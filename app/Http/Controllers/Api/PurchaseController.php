<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Book_for_sell_copy;
use App\Models\Transaction;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class PurchaseController extends Controller
{
    public function checkout($id)
    {
        $copy = Book_for_sell_copy::where('status', 'unsold')->where('book_id', $id)->first();
        if (!$copy) {
            return response()->json(['success' => false, 'message' => 'Copy not found'], 404);
        }
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;
        $userBalance = $user->balance;
        $book = $copy->book;
        if ($userBalance < $book->hard_copy_price) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }
        $data = [
            'user_id' => $userId,
            'user_balance' => $userBalance,
            'book_id' => $copy->book_id,
            'book_title' => $book->title,
            'sold_copy_id' => $copy->id,
            'amount' => $book->hard_copy_price,
            'copy_serial_number' => $copy->serial_number,
            'copy_status' => $copy->status,
            'copy_condition' => $copy->condition,
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function purchaseHardBook($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;
        $copy = Book_for_sell_copy::where('status', 'unsold')->find($id);
        if (!$copy) {
            return response()->json(['success' => false, 'message' => 'Copy not found'], 404);
        }
        $book = $copy->book;
        $balanceService = new BalanceService();
        $purchase = $balanceService->chargeBalance($userId, $book->hard_copy_price, 'purchase', null, null, $copy->book_id, $copy->id);


        if (!$purchase) {
            return response()->json(["success" => false, 'message' => 'Insufficient balance'], 400);
        }
        DB::beginTransaction();
        try {
            $copy->status = 'sold';
            $copy->user_id = $userId;
            $copy->purchase_date = now();
            $copy->save();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        DB::commit();

        return response()->json(['success' => true, 'message' => 'Book purchased successfully', 'data' => $purchase], 200);
    }

    public function purchaseEbook(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;
        $book = Book::where('id', $id)
            ->whereNotNull('link')
            ->where('status', 'active')
            ->first();
        $balanceService = new BalanceService();
        $purchase = $balanceService->chargeBalance($userId, $book->electronic_copy_price, 'ebook_purchase', null, null, $book->id);

        if (!$purchase) {
            return response()->json(["success" => false, 'message' => 'Insufficient balance'], 400);
        }

        return response()->json(['success' => true, 'message' => 'Ebook purchased successfully', 'data' => $purchase]);
    }
}
