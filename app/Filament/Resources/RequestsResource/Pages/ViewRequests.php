<?php

namespace App\Filament\Resources\RequestsResource\Pages;

use App\Filament\Resources\RequestsResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewRequests extends ViewRecord
{
    public $unavailableDates = [];
    public $selectedBook = null;
    public $copyInfo = null;
    protected static string $resource = RequestsResource::class;

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->options(
                        \App\Models\User::all()->pluck('name', 'id')
                    )
                    ->disabled(),
                Select::make('user_id')
                    ->label('Email')
                    ->options(
                        \App\Models\User::all()->pluck('email', 'id')
                    )
                    ->disabled(),
                Select::make('copy_id')
                    ->label('Book Title')
                    ->options(
                        \App\Models\Book::where('id', $this->getRecord()->copy_id)->pluck('title', 'id')
                    ),
                Select::make('copy_id')
                    ->label('Copy Serial Number')
                    ->options(
                        \App\Models\Book_for_borrow_copy::where('id', $this->getRecord()->copy_id)->pluck('serial_number', 'id')
                    ),
                TextInput::make('borrow_start_date')
                    ->label('Borrow Start Date')
                    ->disabled(),
                TextInput::make('borrow_end_date')
                    ->label('Borrow End Date')
                    ->disabled(),
                TextInput::make('request_date')
                    ->label('Request Date')
                    ->disabled(),
                TextInput::make('request_expiry_date')
                    ->label('Request Expiry Date')
                    ->disabled(),
                TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
            ]);
    }
}
