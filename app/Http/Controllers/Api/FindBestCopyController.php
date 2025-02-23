<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Carbon\Carbon;

class FindBestCopyController extends Controller
{
    public function findBestCopy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        $copies = $book->book_for_borrow_copies()->with(['borrow_requests', 'book_borrowings'])->get();

        $bestCopyWithAvailability = $this->getAvailableCopy($copies)
            ?? $this->getExpiredRequestCopy($copies)
            ?? $this->getReturningSoonCopy($copies);

        if ($bestCopyWithAvailability) {
            return response()->json(['success' => true, 'best_copy' => $bestCopyWithAvailability]);
        }

        $bestCopyWithAvailability = $this->getBestAvailableCopy($copies);

        if ($bestCopyWithAvailability) {
            return response()->json(['success' => true, 'best_copy' => $bestCopyWithAvailability]);
        }

        return response()->json(['success' => false, 'message' => 'No suitable copy found']);
    }

    private function getAvailableCopy($copies)
    {
        $copy = $copies->firstWhere('status', 'available');

        if ($copy) {
            return [
                'copy' => $copy,
                'unavailable_from' => null,
                'unavailable_until' => null
            ];
        }

        return null;
    }

    private function getExpiredRequestCopy($copies)
    {
        foreach ($copies->where('status', 'requested') as $copy) {
            if ($copy->borrow_requests->where('status', 'approved')->count() === 1 && $copy->book_borrowings->where('returned', false)->count() === 0) {
                $request = $copy->borrow_requests->first();

                if ($request && $request->request_expiry_date < now()) {
                    $request->update(['status' => 'rejected']);
                    $copy->update(['status' => 'available']);

                    return [
                        'copy' => $copy,
                        'unavailable_from' => null,
                        'unavailable_until' => null
                    ];
                }
            }
        }
        return null;
    }

    private function getReturningSoonCopy($copies)
    {
        foreach ($copies->where('status', 'borrowed') as $copy) {
            $borrowing = $copy->book_borrowings->firstWhere('returned', false);

            if ($borrowing && round(abs(Carbon::parse($borrowing->borrow_end)->diffInDays(now()))) <= 3) {
                $hasFutureRequests = $copy->borrow_requests
                    ->where('status', 'approved')
                    ->isNotEmpty();

                if (!$hasFutureRequests) {
                    $returnDate = Carbon::parse($borrowing->borrow_end);

                    return [
                        'copy' => $copy,
                        'unavailable_from' => now()->toDateString(),
                        'unavailable_until' => $returnDate->toDateString()
                    ];
                }
            }
        }

        return null;
    }

    private function getTemporaryAvailableCopy($copies)
    {
        $unavailableDates = [];

        foreach ($copies->where('status', 'requested') as $copy) {
            $futureRequest = $copy->borrow_requests
                ->where('borrow_start_date', '>=', now())
                ->where('status', 'approved')
                ->sortBy('borrow_start_date')
                ->first();
            $futureRequest2 = $copy->borrow_requests
                ->where('borrow_start_date', '>=', now())
                ->where('status', 'approved')
                ->sortByDesc('borrow_start_date')
                ->first();

            if ($futureRequest && $futureRequest2) {
                $borrowStartDate = Carbon::parse($futureRequest->borrow_start_date);
                $borrowEndDate = Carbon::parse($futureRequest2->borrow_end_date);

                // Add the unavailable period (from borrow start date to borrow end date)
                $unavailableDates[] = [
                    'unavailable_from' => $borrowStartDate->toDateString(),
                    'unavailable_until' => $borrowEndDate->toDateString(),
                ];
            }
        }

        if (count($unavailableDates) > 0) {
            return $unavailableDates;
        }

        return null;
    }

    private function getEarliestReturnDateCopy($copies)
    {
        $bestCopyDetails = null;
        $earliestReturnDate = null;

        foreach ($copies->where('status', 'borrowed') as $copy) {
            $borrowing = $copy->book_borrowings->firstWhere('returned', false);
            if ($borrowing) {
                $endDate = $copy->borrow_requests
                    ->where('borrow_start_date', '>=', now())
                    ->where('status', 'approved')
                    ->sortByDesc('borrow_start_date')
                    ->first();

                if ($endDate && $endDate->borrow_end_date) {
                    $returnDate = Carbon::parse($endDate->borrow_end_date);
                } else {
                    $returnDate = Carbon::parse($borrowing->borrow_end);
                }


                if (is_null($earliestReturnDate) || $returnDate->lessThan($earliestReturnDate)) {
                    $earliestReturnDate = $returnDate;
                    $bestCopyDetails = [
                        'copy' => $copy,
                        'unavailable_from' => now()->toDateString(),
                        'unavailable_until' => $returnDate->toDateString()
                    ];
                }
            }
        }

        return $bestCopyDetails;
    }

    private function getBestAvailableCopy($copies)
    {
        $earliestReturnCopy = $this->getEarliestReturnDateCopy($copies);
        $temporaryAvailableCopy = $this->getTemporaryAvailableCopy($copies);

        if ($earliestReturnCopy && $temporaryAvailableCopy) {
            $earliestReturnDaysAvailable = $earliestReturnCopy['days_available'] ?? 0;
            $temporaryAvailableDaysAvailable = $temporaryAvailableCopy['days_available'] ?? 0;

            return $earliestReturnDaysAvailable >= $temporaryAvailableDaysAvailable
                ? $earliestReturnCopy
                : $temporaryAvailableCopy;
        }

        return $earliestReturnCopy ?? $temporaryAvailableCopy;
    }
}
