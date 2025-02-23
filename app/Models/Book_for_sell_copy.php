<?php

namespace App\Models;

use App\Enums\BookForSellStatus;
use App\Enums\Condition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book_for_sell_copy extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'condition' => Condition::class,
        'status' => BookForSellStatus::class,
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sold_copy_id');
    }
}
