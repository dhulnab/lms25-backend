<?php

namespace App\Filament\Resources\BorrowingsResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\BorrowingsResource;
use App\Models\Book;
use App\Models\Book_for_borrow_copy;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form; // Ensure this import is present

class ViewBorrowings extends ViewRecord
{
    protected static string $resource = BorrowingsResource::class;

    public function form(Form $form): Form
    {
        $record = $this->getRecord();

        $bookCopy = Book_for_borrow_copy::where('id', $record->book_copy_id)->first();
        $bookId = $bookCopy ? $bookCopy->book_id : null;

        return $form
            ->schema([
                Select::make('book_copy_id')
                    ->label('Book Title')
                    ->options(Book::where('id', $bookId)->pluck('title', 'id'))
                    ->disabled(),
                Select::make('book_copy_id')
                    ->label('Copy Serial Number')
                    ->options(
                        Book_for_borrow_copy::where('id', $record->book_copy_id)->pluck('serial_number', 'id')
                    )
                    ->disabled(),
                Select::make('user_id')
                    ->label('User Name')
                    ->options(User::where('id', $record->user_id)->pluck('name', 'id'))
                    ->disabled(),
                Select::make('user_id')
                    ->label('User Email')
                    ->options(User::where('id', $record->user_id)->pluck('email', 'id'))
                    ->disabled(),
                DateTimePicker::make('borrow_start')
                    ->label('Borrow Start')
                    ->disabled(),
                DateTimePicker::make('borrow_end')
                    ->label('Borrow End')
                    ->disabled(),
                DateTimePicker::make('return_date')
                    ->label('Return Date')
                    ->disabled(),
                Checkbox::make('returned')
                    ->label('Returned')
                    ->disabled(),
            ]);
    }
}
