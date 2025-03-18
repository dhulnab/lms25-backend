<?php

namespace App\Filament\Resources\BookForBorrowResource\Pages;

use App\Filament\Resources\BookForBorrowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookForBorrows extends ListRecords
{
    protected static string $resource = BookForBorrowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
