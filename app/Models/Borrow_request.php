<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrow_request extends Model
{
    protected $fillable = [
        'user_id',
        'copy_id',
        'borrow_start_date',
        'borrow_end_date',
        'request_date',
        'request_expiry_date',
        'status'
    ];

    protected $casts = [
        'status' => RequestStatus::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function book_for_borrow_copy(): BelongsTo
    {
        return $this->belongsTo(Book_for_borrow_copy::class, 'copy_id');
    }
}
