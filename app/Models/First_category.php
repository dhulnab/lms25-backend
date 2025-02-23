<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class First_category extends Model
{
    protected $guarded = ['id'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
    public function second_categories(): HasMany
    {
        return $this->hasMany(Second_category::class, 'parent_id');
    }
    public function third_categories(): HasManyThrough
    {
        return $this->hasManyThrough(Third_category::class, Second_category::class, 'parent_id', 'parent_id');
    }
}
