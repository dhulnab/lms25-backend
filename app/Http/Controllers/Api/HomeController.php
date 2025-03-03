<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get the 10 most recently added books
        $newRelease = Book::with('first_category', 'second_category', 'third_category')->orderBy('created_at', 'desc')->take(10)->get();

        // Get the best-selling book copies
        $bestSeller = Transaction::where('status', 'success')
            ->whereNotNull('sold_copy_id')
            ->groupBy('sold_copy_id')
            ->selectRaw('count(*) as total, sold_copy_id')
            ->orderBy('total', 'desc')
            ->get();

        $bestSellerBooks = [];
        $i = 0;

        // Extract books for the top 10 best-seller copies
        while (count($bestSellerBooks) < 10 && $i < count($bestSeller)) {
            $bestSellerBooks[] = Book::with('first_category', 'second_category', 'third_category')
                ->where('id', $bestSeller[$i]->sold_copy_id)
                ->first();
            $i++;
        }

        if (!$bestSellerBooks) {
            $bestSellerBooks = Book::with('first_category', 'second_category', 'third_category')->orderBy('created_at', 'desc')->take(10)->get();
        }
        return response()->json([
            'newRelease' => $newRelease,
            'bestSellerBooks' => $bestSellerBooks
        ]);
    }
}
