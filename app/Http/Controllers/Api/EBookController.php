<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class EBookController extends Controller
{
    public function preview($id, Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;

        $book = Book::where('id', $id)
            ->whereNotNull('link')
            ->where('status', 'active')
            ->first();

        if (!$book) {
            abort(404, 'Book or file not found');
        }

        $purchase = Transaction::where('user_id', $userId)
            ->where('book_id', $book->id)
            ->where('status', 'done') // Ensure the purchase is completed
            ->first();
            
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'You need to purchase the ebook to preview it.'
            ], 403);
        }
        $filePath = "private/pdfs/{$book->link}";

        if (!Storage::exists($filePath)) {
            abort(404, 'File not found');
        }

        // Stream the PDF file
        return response()->file(storage_path("app/{$filePath}"), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $book->link . '"',
        ]);
    }

    public function upload(Request $request, $id)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:24576',  // 24MB max
        ]);

        $book = Book::where('id', $id)
            ->where('status', 'active')
            ->first();


        if (!$book) {
            abort(404, 'Book not found');
        }

        $fileName = time() . '_' . $request->file('pdf')->getClientOriginalName();
        $path = $request->file('pdf')->storeAs('private/pdfs', $fileName);

        // Store the path in the database
        $book->link = $fileName;
        $book->save();

        return response()->json([
            'message' => 'PDF uploaded successfully',
            'path' => $path,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:24576',
        ]);

        $book = Book::where('id', $id)
            ->whereNotNull('link')
            ->where('status', 'active')
            ->first();


        if (!$book) {
            abort(404, 'Book not found');
        }

        DB::transaction(function () use ($book, $request) {
            // Delete old file if exists
            if ($book->link) {
                $oldFilePath = "private/pdfs/{$book->link}";
                if (Storage::exists($oldFilePath)) {
                    Storage::delete($oldFilePath);
                }
            }

            // Upload new file
            $newLink = time() . '_' . $request->file('pdf')->getClientOriginalName();
            $request->file('pdf')->storeAs('private/pdfs', $newLink);

            // Update database
            $book->link = $newLink;
            $book->save();
        });

        return response()->json([
            'message' => 'PDF updated successfully',
        ]);
    }

    public function delete($id)
    {
        $book = Book::where('id', $id)
            ->whereNotNull('link')
            ->where('status', 'active')
            ->first();

        if (!$book) {
            abort(404, 'Book or file not found');
        }

        $filePath = "private/pdfs/{$book->link}";

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        $book->link = null;
        $book->save();

        return response()->json([
            'message' => 'PDF deleted successfully',
        ]);
    }
}

// Handle Caching in PWA:
// On the PWA side, you'll need to implement Service Workers to cache the files for offline access. 
//When the PDF is requested for preview, it should be cached for offline use.
// This requires adding a service worker that will intercept requests and cache the PDFs locally. 
//You can use caches.open() and caches.addAll() to cache the files in your PWA.