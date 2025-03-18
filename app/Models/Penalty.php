<?php

namespace App\Models;

use App\Enums\PenaltyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penalty extends Model
{
    protected $fillable = [
        'user_id',
        'borrow_id',
        'penalty_amount',
        'penalty_status',
        'assessed_at',
        'paid_at',
    ];
    protected $casts = [
        'penalty_status' => PenaltyStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function book_borrowing(): BelongsTo
    {
        return $this->belongsTo(book_borrowing::class, 'borrow_id');
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
