<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book_borrowing;
use App\Models\Borrow_request;
use App\Models\Book_for_borrow_copy;
use App\Models\Book;
use App\Models\Penalty;
use App\Models\Setting;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserInventoryController extends Controller
{
    public function userInventory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        if (!$user || !$user_id) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        $userInventory = Book_borrowing::with([
            'Book_for_borrow_copy.Book',
            'Book_for_borrow_copy.Book.first_category',
            'Book_for_borrow_copy.Book.second_category',
            'Book_for_borrow_copy.Book.third_category'
        ])->where('user_id', $user_id)->get();
        return response()->json(['success' => true, 'data' => $userInventory]);
    }
    public function UserRequest(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        if (!$user || !$user_id) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        $userRequests = Borrow_request::with([
            'Book_for_borrow_copy.Book',
            'Book_for_borrow_copy.Book.first_category',
            'Book_for_borrow_copy.Book.second_category',
            'Book_for_borrow_copy.Book.third_category'
        ])->where('user_id', $user_id)->get();
        return response()->json(['success' => true, 'data' => $userRequests]);
    }
    public function UserPenalties(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        if (!$user || !$user_id) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        $penalty_per_day = Setting::first()->penalty_per_day;
        $userPenalties = Penalty::with([
            'book_borrowing.book_for_borrow_copy.Book',
            'book_borrowing.book_for_borrow_copy.Book.first_category',
            'book_borrowing.book_for_borrow_copy.Book.second_category',
            'book_borrowing.book_for_borrow_copy.Book.third_category'
        ])->where('user_id', $user_id)
        ->get()
        ->map(function($penalty) use ($penalty_per_day) {
            $penalty->penalty_per_day = $penalty_per_day;
            return $penalty;
        });
        return response()->json(['success' => true, 'data' => $userPenalties]);
    }
}
