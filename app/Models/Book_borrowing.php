<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book_borrowing extends Model
{
    protected $fillable = [
        'book_copy_id',
        'user_id',
        'borrow_start',
        'borrow_end',
        'return_date',
        'returned',
    ];
    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class, 'borrow_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function book_for_borrow_copy(): BelongsTo
    {
        return $this->belongsTo(Book_for_borrow_copy::class, 'book_copy_id');
    }
    public function getBookAttribute()
    {
        return $this->bookCopy ? $this->bookCopy->book : null;
    }
}
