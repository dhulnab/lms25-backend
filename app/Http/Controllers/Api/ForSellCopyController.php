<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book_for_sell_copy;
use Illuminate\Http\Request;

class ForSellCopyController extends Controller
{
    public function getForSellCopies($id)
    {
        $copies = Book_for_sell_copy::with('book')
            ->where('book_id', $id)
            ->where('status', '!=', 'inactive')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $copies->items(),
            'meta' => [
                'total' => $copies->total(),
                'current_page' => $copies->currentPage(),
                'last_page' => $copies->lastPage(),
            ],
        ]);
    }

    public function getForSellCopy($id)
    {
        $copy = Book_for_sell_copy::with('book')
            ->where('status', '!=', 'inactive')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $copy,
        ]);
    }

    public function addForSellCopy(Request $request)
    {
        $validatedData = $request->validate([
            'book_id' => 'required|integer|exists:books,id',
            'serial_number' => 'required|string|size:13',
        ]);

        $copy = Book_for_sell_copy::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Copy for sell added successfully!',
            'data' => $copy,
        ], 201);
    }

    public function deleteForSellCopy($id)
    {
        $copy = Book_for_sell_copy::findOrFail($id);
        $copy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted',
        ]);
    }

    public function updateForSellCopy(Request $request, $id)
    {
        $validatedData = $request->validate([
            'book_id' => 'required|integer|exists:books,id',
            'serial_number' => 'required|string|size:13',
            'status' => 'in:unsold,sold,inactive',
        ]);

        $copy = Book_for_sell_copy::findOrFail($id);
        $copy->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Copy updated successfully',
            'data' => $copy,
        ]);
    }
}
