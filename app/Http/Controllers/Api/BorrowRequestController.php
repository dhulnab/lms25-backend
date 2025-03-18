<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow_request;
use App\Models\User;
use App\Models\Book_for_borrow_copy;
use App\Models\Book;
use App\Notifications\NotifyByAll;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class BorrowRequestController extends Controller
{


    public function deactivateRequest($id)
    {
        $req = Borrow_request::with(['book_for_borrow_copy'])->find($id);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Borrow request not found'
            ], 404);
        }

        DB::beginTransaction();

        try {

            $req->update(['status' => 'rejected']);
            if (!$req->book_for_borrow_copy) {
                throw new \Exception('Related copy not found for this borrow request.');
            }

            // Check if there are other active requests for the same copy
            $activeRequests = Borrow_request::where('copy_id', $req->copy_id)
                ->where('id', '!=', $id)
                ->where('status', 'approved')
                ->exists();

            // Update copy status if no other active requests exist and the current status is not 'borrowed'
            if (!$activeRequests && $req->book_for_borrow_copy->status !== 'borrowed') {
                $req->book_for_borrow_copy->update(['status' => 'available']);
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request successfully deactivated'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deactivating the request',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getBorrowRequest($id = null)
    {
        if ($id) {
            $borrowRequest = Borrow_request::with([
                'user',
                'book_for_borrow_copy.book'
            ])->find($id);

            if (!$borrowRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrow Request not found'
                ], 404);
            }

            $data = $borrowRequest;
        } else {
            $data = Borrow_request::all();
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getUserBorrowRequests($id)
    {
        if ($id) {
            $borrowRequests = Borrow_request::with([
                'user',
                'book_for_borrow_copy.book'
            ])
                ->where('user_id', $id)
                ->get();

            if ($borrowRequests->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No borrow requests found for the specified user'
                ], 404);
            }

            $data = $borrowRequests;
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function createRequest(Request $request)
    {

        $validatedData = $request->validate([
            'copy_id' => [
                'required',
                'integer',
                Rule::exists('book_for_borrow_copies', 'id'),
            ],
            'borrow_start_date' => 'required|date',
            'borrow_end_date' => 'required|date|after:borrow_start_date',
        ]);
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;
        $validatedData['user_id'] = $userId;
        // Current timestamp
        $validatedData['request_date'] = Carbon::now()->toDateTimeString();
        $validatedData['borrow_start_date'] = Carbon::parse($validatedData['borrow_start_date'])->toDateTimeString();
        $validatedData['borrow_end_date'] = Carbon::parse($validatedData['borrow_end_date'])->toDateTimeString();
        $validatedData['request_expiry_date'] = Carbon::parse($validatedData['borrow_start_date'])->addDay()->toDateTimeString();
        Log::info($validatedData);
        DB::beginTransaction();
        try {
            // Fetch the copy
            $copy = Book_for_borrow_copy::findOrFail($validatedData['copy_id']);

            // Check for date overlap
            $overlappingRequest = Borrow_request::where('copy_id', $validatedData['copy_id'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('borrow_start_date', [$validatedData['borrow_start_date'], $validatedData['borrow_end_date']])
                        ->orWhereBetween('borrow_end_date', [$validatedData['borrow_start_date'], $validatedData['borrow_end_date']])
                        ->orWhere(function ($query) use ($validatedData) {
                            $query->where('borrow_start_date', '<=', $validatedData['borrow_start_date'])
                                ->where('borrow_end_date', '>=', $validatedData['borrow_end_date']);
                        });
                })
                ->exists();

            if ($overlappingRequest) {
                return response()->json(['error' => 'The requested dates overlap with an existing borrow request.'], 400);
            }

            // Update copy status to 'requested'
            $copy->status = 'requested';
            $copy->save();


            // Create the borrow request
            $borrowCopyRequest = Borrow_request::create($validatedData);

            $user->notify(new NotifyByAll('Book Requested', 'The book you requested \'' . $copy->book->title . '\' is now requested come to the library in the first day of your borrow preiod.', $copy->book));
            (new FirebaseNotificationService())->sendNotification($user->fcm_token, 'Book Requested', 'The book you requested \'' . $copy->book->title . '\' is now requested come to the library in the first day of your borrow preiod.');
            DB::commit();

            return response()->json($borrowCopyRequest, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function updateRequest(Request $request, $id)
    {

        $validatedData = $request->validate([
            'status' => 'in:approved,rejected',
        ]);


        $borrowCopyRequest = Borrow_request::find($id);
        if (!$borrowCopyRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        $borrowCopyRequest->update($validatedData);
        $borrowCopyRequest->save();


        return response()->json([
            $borrowCopyRequest
        ]);
    }
}
