<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Third_category extends Model
{
    protected $guarded = ['id'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
    public function second_category(): BelongsTo
    {
        return $this->belongsTo(Second_category::class, 'parent_id');
    }
}
