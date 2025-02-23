<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    public function chargeBalance($userId, $amount, $action, $details = null, $penaltyId = null, $bookId = null, $soldCopyId = null)
    {
        $user = User::findOrFail($userId);

        if ($user->balance < $amount) {
            return false;
        }

        DB::transaction(function () use ($user, $amount, $action, $details, $penaltyId, $bookId, $soldCopyId) {
            $user->decrement('balance', $amount);

            Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'action' => $action,
                'penalty_id' => $penaltyId,
                'book_id' => $bookId,
                'sold_copy_id' => $soldCopyId,
                'details' => $details,
                'status' => 'done',
            ]);
        });

        return true;
    }


    public function addBalance($userId, $amount, $details = 'Balance top-up')
    {
        $user = User::findOrFail($userId);

        DB::transaction(function () use ($user, $amount, $details) {
            // Increment the balance
            $user->increment('balance', $amount);

            // Create a balance update transaction
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'action' => 'balance_update',
                'details' => $details,
                'status' => 'done',
            ]);
        });
    }
}
