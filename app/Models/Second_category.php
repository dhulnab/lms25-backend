<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Second_category extends Model
{
    protected $guarded = ['id'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
    public function third_categories(): HasMany
    {
        return $this->hasMany(Third_category::class, 'parent_id');
    }
    public function first_category(): BelongsTo
    {
        return $this->belongsTo(First_category::class, 'parent_id');
    }
}
