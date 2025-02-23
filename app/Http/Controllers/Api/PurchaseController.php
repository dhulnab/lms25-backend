<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Transaction;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PurchaseController extends Controller
{




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
