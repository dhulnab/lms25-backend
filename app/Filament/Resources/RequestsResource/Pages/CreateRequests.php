<?php

namespace App\Filament\Resources\RequestsResource\Pages;

use App\Filament\Resources\RequestsResource;
use App\Models\Book;
use App\Models\Book_for_borrow_copy;
use App\Notifications\NotifyByAll;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRequests extends CreateRecord
{
    protected static string $resource = RequestsResource::class;

    public $unavailableDates = [];
    public $selectedBook = null;
    public $copyInfo = null;
    protected function afterCreate(): void
    {
        // Access the newly created record
        $record = $this->record;
        $copy = Book_for_borrow_copy::find($record->copy_id);
        if ($copy) {
            $copy->status = 'requested';
            $copy->save();
            $notifiableUser = $record->user; // This gets the user associated with the request
            $notifiableUser->notify(new NotifyByAll('Book Requested', 'The book you requested \'' . $copy->book->title . '\' is now requested come to the library in the first day of your borrow preiod.', $copy->book));
            (new FirebaseNotificationService())->sendNotification($notifiableUser->fcm_token, 'Book Requested', 'The book you requested \'' . $copy->book->title . '\' is now requested come to the library in the first day of your borrow preiod.');
        }
    }

    // Move the handleBookSelection method here
    public function handleBookSelection($bookId, callable $set)
    {
        $set('copy_id', null);
        $set('borrow_start_date', null);
        $set('borrow_end_date', null);
        $this->copyInfo = null;
        $this->unavailableDates = [];

        $book = Book::find($bookId);
        if ($book) {
            $this->selectedBook = $book;

            $copies = $book->book_for_borrow_copies()->with(['borrow_requests', 'book_borrowings'])->get();
            // Determine the best copy with availability
            $bestCopyWithAvailability = $this->getAvailableCopy($copies)
                ?? $this->getExpiredRequestCopy($copies)
                ?? $this->getReturningSoonCopy($copies)
                ?? $this->getBestAvailableCopy($copies);
            if ($bestCopyWithAvailability) {
                $this->copyInfo = $bestCopyWithAvailability;
                $set('copy_id', $bestCopyWithAvailability['copy']->id);

                // Store unavailable dates for disabling in date pickers
                $this->unavailableDates = [
                    'from' => $bestCopyWithAvailability['unavailable_from'],
                    'until' => $bestCopyWithAvailability['unavailable_until'],
                ];
            }
        } else {
            $this->selectedBook = null;
        }
    }
    private static function getAvailableCopy($copies)
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
    private static function getExpiredRequestCopy($copies)
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
    private static function getReturningSoonCopy($copies)
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
    private static function getTemporaryAvailableCopy($copies)
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

    private static function getEarliestReturnDateCopy($copies)
    {

        $bestCopyDetails = null;
        $earliestReturnDate = null;
        $bestCopyDetails2 = null;
        $earliestReturnDate2 = null;

        foreach ($copies->where('status', 'borrowed') as $copy) {
            $borrowing = $copy->book_borrowings->where('returned', false)->first();
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
                        'unavailable_until' => $returnDate->addDays(1)->toDateString()
                    ];
                }
            }
        }


        foreach ($copies->where('status', 'requested') as $copy) {
            $futureRequest = $copy->borrow_requests
                ->where('status', 'approved')
                ->where('borrow_start_date', '>=', now())
                ->sortByDesc('borrow_start_date')
                ->first();
            if ($futureRequest && $futureRequest->borrow_end_date) {
                $returnDate = Carbon::parse($futureRequest->borrow_end_date);
                if (is_null($earliestReturnDate) || $returnDate->lessThan($earliestReturnDate)) {
                    $earliestReturnDate2 = $returnDate;
                    $bestCopyDetails2 = [
                        'copy' => $copy,
                        'unavailable_from' => now()->toDateString(),
                        'unavailable_until' => $returnDate->addDays(1)->toDateString()
                    ];
                }
            }
        }
        if ($earliestReturnDate2 === null) {
            return $bestCopyDetails;
        }
        if ($earliestReturnDate === null) {
            return $bestCopyDetails2;
        }
        if ($earliestReturnDate2->lessThan($earliestReturnDate)) {
            return $bestCopyDetails2;
        } else if ($earliestReturnDate->lessThan($earliestReturnDate2)) {
            return $bestCopyDetails;
        } else return null;
    }

    private static function getBestAvailableCopy($copies)
    {

        $earliestReturnCopy = self::getEarliestReturnDateCopy($copies);
        // $temporaryAvailableCopy = self::getTemporaryAvailableCopy($copies);

        if ($earliestReturnCopy) {
            $earliestReturnDaysAvailable = $earliestReturnCopy['days_available'] ?? 0;
            $temporaryAvailableDaysAvailable = $temporaryAvailableCopy['days_available'] ?? 0;

            return $earliestReturnCopy;
            // return $earliestReturnDaysAvailable >= $temporaryAvailableDaysAvailable
            //     ? $earliestReturnCopy
            //     : $temporaryAvailableCopy;
        }

        return $earliestReturnCopy;
    }
    public function getUnavailableDates()
    {
        $unavailableDates = $this->unavailableDates;
        if (!empty($unavailableDates)) {
            $from = Carbon::parse($unavailableDates['from']);
            $until = Carbon::parse($unavailableDates['until']);

            // Generate an array of disabled dates between 'from' and 'until'
            $disabledDates = [];
            while ($from->lte($until)) {
                $disabledDates[] = $from->toDateString();
                $from->addDay();
            }
            return $disabledDates;
        }

        return [];
    }
}
