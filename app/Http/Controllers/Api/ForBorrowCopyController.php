<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book_for_borrow_copy;
use Illuminate\Http\Request;

class ForBorrowCopyController extends Controller
{
    public function deleteForBorrowCopy($id)
    {
        $copy = Book_for_borrow_copy::find($id);
        if (!$copy) {
            return response()->json([
                'success' => false,
                'message' => 'copy not found'
            ], 404);
        }

        $copy->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted'
        ]);
    }


    public function getForBorrowCopy($bookId, $id = null)
    {
        if ($id) {
            $copy = Book_for_borrow_copy::find($id);
            if (!$copy || $copy->book_id != $bookId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy not found for this book ID',
                ], 404);
            }

            $data = $copy;
        } else {
            $data = Book_for_borrow_copy::where('book_id', $bookId)->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No copies found for this book ID',
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    public function addForBorrowCopy(Request $request)
    {
        $validatedData = $request->validate([
            'book_id' => 'required|integer|exists:books,id',
            'serial_number' => 'required|string|size:13',
        ]);


        $validatedData['status'] = 'available';


        $copy = Book_for_borrow_copy::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'copy for borrow added successfully!',
            'data' => $copy,
        ], 201);
    }

    public function updateForBorrowCopy(Request $request, $id)
    {
        $validatedData = $request->validate([
            'book_id' => 'required|integer|exists:books,id',
            'serial_number' => 'required|string|size:13',
            'status' => 'in:requested,borrowed,available,deleted',
        ]);


        $copy = Book_for_borrow_copy::find($id);
        if (!$copy) {
            return response()->json([
                'success' => false,
                'message' => 'copy not found',
            ], 404);
        }

        $copy->update($validatedData);
        $copy->save();
        return response()->json([
            'success' => true,
            'message' => 'copy updated successfully',
            'data' => $copy,
        ]);
    }
}
