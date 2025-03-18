<?php

namespace App\Models;

use App\Enums\TransactionAction;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $casts = [
        'action' => TransactionAction::class,
        'status' => TransactionStatus::class
    ];
    protected $fillable = [
        'user_id',
        'action',
        'book_id',
        'penalty_id',
        'sold_copy_id',
        'amount',
        'details',
        'status'
    ];
    public function book_for_sell_copy(): BelongsTo
    {
        return $this->belongsTo(Book_for_sell_copy::class, 'sold_copy_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
    public function penalties(): BelongsTo
    {
        return $this->belongsTo(Penalty::class);
    }
}
