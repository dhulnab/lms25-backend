<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->input('term');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $category = $request->input('category');

        // Apply search with fuzzy matching and filters
        $query = Book::with('first_category', 'second_category', 'third_category')->search($term);

        // Apply filters if present
        if ($minPrice && $maxPrice) {
            $query = $query->filterByPrice($minPrice, $maxPrice);
        }

        if ($category) {
            $query = $query->filterByCategory($category);
        }

        // Order by relevance using ts_rank
        $books = $query
            ->orderByRaw("ts_rank(tsv, plainto_tsquery('english', ?)) DESC", [$term])
            ->get();

        return response()->json($books);
    }




    public function getBook($id = null)
    {
        if ($id) {
            $book = Book::with('first_category', 'second_category', 'third_category')->find($id);
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found'
                ], 404);
            }
            $data = $book;
        } else {
            $data = Book::all();
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function haveCopies($id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }
        $haveSellCopies = $book->book_for_sell_copies()->where('status', 'unsold')->count();
        $haveBorrowCopies = $book->book_for_borrow_copies()->count();
        return response()->json([
            'success' => true,
            'data' => [
                'have_sell_copies' => $haveSellCopies,
                'have_borrow_copies' => $haveBorrowCopies,
            ]
        ]);
    }

    public function addBook(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'first_category_id' => 'required|integer|exists:first_categories,id',
            'second_category_id' => 'required|integer|exists:second_categories,id',
            'third_category_id' => 'required|integer|exists:third_categories,id',
            'publisher' => 'required|string',
            'published_year' => 'required|integer|digits:4|lte:' . date('Y'),
            'isbn' => 'required|string|size:13',
            'electronic_available' => 'required',
            'hard_copy_price' => 'required|numeric|min:0',
            'electronic_copy_price' => 'required|numeric|min:0',
            'language' => 'required|string',
        ]);

        $validatedData['is_deleted'] = false;
        $validatedData['electronic_available'] = filter_var($validatedData['electronic_available'], FILTER_VALIDATE_BOOLEAN);
        $validatedData['first_category_id'] = intval($validatedData['first_category_id']);
        $validatedData['second_category_id'] = intval($validatedData['second_category_id']);
        $validatedData['third_category_id'] = intval($validatedData['third_category_id']);
        $validatedData['published_year'] = intval($validatedData['published_year']);
        $validatedData['hard_copy_price'] = doubleval($validatedData['hard_copy_price']);
        $validatedData['electronic_copy_price'] = doubleval($validatedData['electronic_copy_price']);


        if ($request->file('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
            $validatedData['cover'] = $coverPath;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cover image is required',
            ], 500);
        }


        $book = Book::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Book added successfully!',
            'data' => $book,
        ], 201);
    }


    public function updateBook(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'first_category_id' => 'required|integer|exists:first_categories,id',
            'second_category_id' => 'required|integer|exists:second_categories,id',
            'third_category_id' => 'required|integer|exists:third_categories,id',
            'publisher' => 'required|string',
            'published_year' => 'required|integer|digits:4|lte:' . date('Y'),
            'isbn' => 'required|string|size:13',
            'electronic_available' => 'required',
            'hard_copy_price' => 'required|numeric|min:0',
            'electronic_copy_price' => 'required|numeric|min:0',
            'language' => 'required|string',
        ]);

        $validatedData['is_deleted'] = false;
        $validatedData['electronic_available'] = filter_var($validatedData['electronic_available'], FILTER_VALIDATE_BOOLEAN);
        $validatedData['first_category_id'] = intval($validatedData['first_category_id']);
        $validatedData['second_category_id'] = intval($validatedData['second_category_id']);
        $validatedData['third_category_id'] = intval($validatedData['third_category_id']);
        $validatedData['published_year'] = intval($validatedData['published_year']);
        $validatedData['hard_copy_price'] = doubleval($validatedData['hard_copy_price']);
        $validatedData['electronic_copy_price'] = doubleval($validatedData['electronic_copy_price']);

        $book = Book::find(intval($id));
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
            ], 404);
        }

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::delete($book->cover);
            }
            $validatedData['cover'] = $request->file('cover')->store('covers', 'public');
        }
        $book->update($validatedData);
        $book->save();
        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book,
        ]);
    }


    public function deleteBook($id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $book->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted'
        ]);
    }
}
