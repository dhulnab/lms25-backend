<?php

namespace App\Livewire;

use App\Models\Book_for_borrow_copy;
use Livewire\Component;

class BorrowRequestForm extends Component
{
    public $selectedBook = null;
    public $copyInfo = null;
    public $unavailableDates = [];
    public function render()
    {
        return view('livewire.borrow-request-form');
    }
    protected function afterSave(): void
    {
        // Retrieve the related copy using the copy_id from the form data
        $copy = Book_for_borrow_copy::find($this->record->copy_id);

        // Ensure the copy exists before attempting to update
        if ($copy) {
            $copy->update(['status' => 'requested']);
        }
    }
}
