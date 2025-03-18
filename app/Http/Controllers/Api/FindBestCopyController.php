<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FindBestCopyController extends Controller
{
    public function findBestCopy($id)
    {
        $book = Book::find($id);

        if (is_null($book)) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        // Retrieve the copies along with their borrow requests and borrowings
        $copies = $book->book_for_borrow_copies()->with([
            'borrow_requests:id,copy_id,status,request_expiry_date,borrow_start_date,borrow_end_date',
            'book_borrowings:id,book_copy_id,returned,borrow_end'
        ])->get();

        // Try simple cases first: available copy, expired requests, or copies returning soon
        $bestCopyWithAvailability = $this->getAvailableCopy($copies)
            ?? $this->getExpiredRequestCopy($copies)
            ?? $this->getReturningSoonCopy($copies);

        if ($bestCopyWithAvailability) {
            return response()->json(['success' => true, 'best_copy' => $bestCopyWithAvailability]);
        }

        // If none of the simple cases apply, use our best-gap approach
        $bestCopyWithAvailability = $this->getBestAvailableCopy($copies);

        if ($bestCopyWithAvailability) {
            return response()->json(['success' => true, 'best_copy' => $bestCopyWithAvailability]);
        }

        return response()->json(['success' => false, 'message' => 'No suitable copy found']);
    }

    // 1. If a copy is fully available, return it immediately.
    private function getAvailableCopy($copies)
    {
        $copy = $copies->firstWhere('status', 'available');

        if ($copy) {
            return [
                'copy'              => $copy,
                'unavailable_from'  => null,
                'unavailable_until' => null,
                'days_available'    => null // Fully available means an infinite gap
            ];
        }

        return null;
    }

    // 2. For a copy with a requested status, if there is exactly one approved request and no active borrowing,
    // and the request has expired, mark it as rejected and make the copy available.
    private function getExpiredRequestCopy($copies)
    {
        foreach ($copies->where('status', 'requested') as $copy) {
            if (
                $copy->borrow_requests->where('status', 'approved')->count() === 1 &&
                $copy->book_borrowings->where('returned', false)->count() === 0
            ) {
                $request = $copy->borrow_requests->first();
                if ($request && Carbon::parse($request->request_expiry_date)->lessThan(now())) {
                    DB::transaction(function () use ($request, $copy) {
                        $request->update(['status' => 'rejected']);
                        $copy->update(['status' => 'available']);
                    });

                    return [
                        'copy'              => $copy,
                        'unavailable_from'  => null,
                        'unavailable_until' => null,
                        'days_available'    => null
                    ];
                }
            }
        }
        return null;
    }

    // 3. For copies that are borrowed, if one is due to be returned within 3 days and it has no future approved requests,
    // return that copy along with its expected return date.
    private function getReturningSoonCopy($copies)
    {
        foreach ($copies->where('status', 'borrowed') as $copy) {
            $borrowing = $copy->book_borrowings->firstWhere('returned', false);

            if ($borrowing && Carbon::parse($borrowing->borrow_end)->diffInDays(now()) <= 4) {
                $hasFutureRequests = $copy->borrow_requests
                    ->where('status', 'approved')
                    ->isNotEmpty();

                if (!$hasFutureRequests) {
                    $returnDate = Carbon::parse($borrowing->borrow_end);
                    $daysAvailable = Carbon::now()->diffInDays($returnDate, true);
                    return [
                        'copy'              => $copy,
                        'unavailable_from'  => now()->toDateString(),
                        'unavailable_until' => $returnDate->toDateString(),
                        'days_available'    => $daysAvailable,
                        'from' => 'returning_soon'
                    ];
                }
            }
        }

        return null;
    }

    // 4. For copies with a requested status, calculate gaps (free periods) between upcoming approved borrow requests.
    // This method returns the copy with the longest available gap along with its unavailable date ranges.
    private function getTemporaryAvailableCopy($copies)
    {
        $bestGap = -1;
        $bestCopy = null;
        $bestUnavailableDates = [];
        $bestDaysAvailable = 0;

        foreach ($copies->where('status', 'requested') as $copy) {
            // Get future approved borrow requests, ordered by start date
            $requests = $copy->borrow_requests()
                ->where('status', 'approved')
                ->where('borrow_start_date', '>=', Carbon::now())
                ->orderBy('borrow_start_date')
                ->get();

            // Build an array of unavailable date ranges (for the frontend calendar)
            $unavailableDates = $requests->map(function ($request) {
                return [
                    'unavailable_from'  => Carbon::parse($request->borrow_start_date)->toDateString(),
                    'unavailable_until' => Carbon::parse($request->borrow_end_date)->toDateString(),
                ];
            })->toArray();

            // Determine the largest gap available for this copy
            if ($requests->isEmpty()) {
                // If there are no future requests, treat the gap as infinite
                $currentGap = INF;
            } else {
                // Gap from now until the first request's start date (if not currently borrowed)
                $firstRequest = $requests->first();
                $gapFromNow = 0;
                if ($copy->book_borrowings->where('returned', false)->isEmpty()) {
                    $gapFromNow = Carbon::now()->diffInDays(Carbon::parse($firstRequest->borrow_start_date), true);
                }
                $maxGap = $gapFromNow > 0 ? $gapFromNow : 0;

                // Gaps between consecutive approved requests
                for ($i = 0; $i < $requests->count() - 1; $i++) {
                    $currentEnd = Carbon::parse($requests[$i]->borrow_end_date);
                    $nextStart  = Carbon::parse($requests[$i + 1]->borrow_start_date);
                    $gap = $currentEnd->diffInDays($nextStart, true);
                    if ($gap > $maxGap) {
                        $maxGap = $gap;
                    }
                }
                $currentGap = $maxGap;
            }

            // If this copy has a larger gap than any before, mark it as best
            if ($currentGap === INF || $currentGap > $bestGap) {
                $bestGap = $currentGap;
                $bestCopy = $copy;
                $bestUnavailableDates = $unavailableDates;
                $bestDaysAvailable = $currentGap;
            }
        }

        if ($bestCopy !== null) {
            return [
                'copy'              => $bestCopy,
                'unavailable_dates' => $bestUnavailableDates,
                'days_available'    => $bestDaysAvailable,
                'from' => 'temporary_available'
            ];
        }

        return null;
    }

    // 5. For copies that are borrowed, determine when each copy will next be available.
    // This method picks the copy that will be free the soonest.
    private function getEarliestReturnDateCopy($copies)
    {
        $bestCopyDetails = null;
        $earliestReturnDate = null;

        foreach ($copies->where('status', 'borrowed') as $copy) {
            $borrowing = $copy->book_borrowings->firstWhere('returned', false);
            if ($borrowing) {
                // Check for future approved requests; if they exist, use the last request's end date as the expected return date.
                $endDateRequest = $copy->borrow_requests
                    ->where('borrow_start_date', '>=', now())
                    ->where('status', 'approved')
                    ->sortByDesc('borrow_start_date')
                    ->first();

                if ($endDateRequest && $endDateRequest->borrow_end_date) {
                    $returnDate = Carbon::parse($endDateRequest->borrow_end_date);
                } else {
                    $returnDate = Carbon::parse($borrowing->borrow_end);
                }

                // Choose the copy with the earliest (i.e. soonest) return date
                if (is_null($earliestReturnDate) || $returnDate->lessThan($earliestReturnDate)) {
                    $earliestReturnDate = $returnDate;
                    $daysAvailable = Carbon::now()->diffInDays($returnDate, true);
                    $bestCopyDetails = [
                        'copy'              => $copy,
                        'unavailable_from'  => now()->toDateString(),
                        'unavailable_until' => $returnDate->toDateString(),
                        'days_available'    => $daysAvailable,
                        'from' => 'earliest_return_date'
                    ];
                }
            }
        }

        return $bestCopyDetails;
    }

    // 6. Compare the best candidate from copies that are borrowed (earliest return)
    // with those that are temporarily available (gaps between requests) and choose the one offering
    // the longest availability.
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
