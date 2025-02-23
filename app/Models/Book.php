<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Book extends Model
{
    protected $guarded = ['id'];

    public function scopeSearch($query, $term)
    {
        // Full-text search using tsvector
        return $query->whereRaw("tsv @@ plainto_tsquery('english', ?)", [$term])
            // Fuzzy search using pg_trgm's similarity operator for title, author, and description
            ->orWhereRaw("title % ? OR author % ? OR description % ?", [$term, $term, $term])
            // Fuzzy matching using pg_trgm with similarity
            ->orWhereRaw("title ILIKE ? OR author ILIKE ? OR description ILIKE ?", ['%' . $term . '%', '%' . $term . '%', '%' . $term . '%'])
            // Add pg_trgm similarity matching for fuzzy terms
            ->orWhereRaw("similarity(title, ?) > 0.3 OR similarity(author, ?) > 0.3 OR similarity(description, ?) > 0.3", [$term, $term, $term]);
    }


    // Filter by price range
    public function scopeFilterByPrice($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    // Filter by category
    public function scopeFilterByCategory($query, $category)
    {
        return $query->where('category_id', $category);
    }


    public function first_category(): BelongsTo
    {
        return $this->belongsTo(First_category::class);
    }
    public function second_category(): BelongsTo
    {
        return $this->belongsTo(Second_category::class);
    }
    public function third_category(): BelongsTo
    {
        return $this->belongsTo(Third_category::class);
    }
    public function book_for_sell_copies(): HasMany
    {
        return $this->hasMany(Book_for_sell_copy::class);
    }
    public function book_for_borrow_copies(): HasMany
    {
        return $this->hasMany(Book_for_borrow_copy::class);
    }

    public function book_borrowings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Book_borrowing::class,
            Book_for_borrow_copy::class,
            'book_id',
            'book_copy_id'
        );
    }
    public function borrow_requests(): HasManyThrough
    {
        return $this->hasManyThrough(
            Borrow_request::class,
            Book_for_borrow_copy::class,
            'book_id',
            'copy_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
