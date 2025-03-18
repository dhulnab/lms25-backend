<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book_borrowing;
use App\Models\Book_for_borrow_copy;
use App\Models\Borrow_request;
use App\Models\Penalty;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookBorrowingController extends Controller
{
    public function returnCopy($id)
    {
        $copy = Book_for_borrow_copy::find($id);
        if (!$copy) {
            return response()->json([
                'success' => false,
                'message' => 'Copy not found'
            ], 404);
        }

        $returnRecord = $copy->book_borrowings->where('returned', false)->first();
        if (!$returnRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Copy not currently borrowed'
            ], 404);
        }

        $penaltyPreDay = Setting::latest()->value('penalty_per_day');
        DB::beginTransaction();

        try {
            if ($returnRecord->borrow_end < now()) {
                $days = now()->diffInDays($returnRecord->borrow_end, false);
                $penaltyAmount = $penaltyPreDay * $days;

                Penalty::create([
                    'user_id' => $returnRecord->user_id,
                    'borrow_id' => $returnRecord->id,
                    'penalty_amount' => $penaltyAmount,
                    'penalty_status' => 'unpaid',
                    'assessed_at' => now()
                ]);
            }

            $returnRecord->update(['returned' => true]);

            $requests = $copy->borrow_requests->where('status', 'approved');
            $status = $requests->count() > 0 ? 'requested' : 'available';

            $copy->update(['status' => $status]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error returning book copy: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to return the copy'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'copy' => $copy,
            'borrow_record' => $copy->book_borrowings
        ]);
    }






    public function pickBookCopy(Request $request)
    {
        $validatedData = $request->validate([
            'book_copy_id' => 'required|integer|exists:book_for_borrow_copies,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Fetch the borrow request
        $borrowRequest = Borrow_request::with(['book_for_borrow_copy'])
            ->where('user_id', $validatedData['user_id'])
            ->where('copy_id', $validatedData['book_copy_id'])
            ->where('status', 'approved')
            ->where('borrow_start_date', '>=', now())
            ->first();

        // Check if the borrow request exists
        if (!$borrowRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Borrow request not found!',
            ], 404);
        }
        // Create a new borrowing record
        $borrowingRecord = Book_borrowing::create([
            'user_id' => $validatedData['user_id'],
            'book_copy_id' => $validatedData['book_copy_id'],
            'borrow_start' => $borrowRequest->borrow_start_date,
            'borrow_end' => $borrowRequest->borrow_end_date,
        ]);

        // Update the book copy status
        $borrowRequest->book_for_borrow_copy->update(['status' => 'borrowed']);
        $borrowRequest->status = 'rejected';
        $borrowRequest->save();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Borrowing record added successfully!',
            'data' => $borrowingRecord,
        ], 201);
    }
}
