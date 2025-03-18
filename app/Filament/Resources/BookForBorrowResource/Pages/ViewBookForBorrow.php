<?php

namespace App\Filament\Resources\BookForBorrowResource\Pages;

use App\Filament\Resources\BookForBorrowResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookForBorrow extends ViewRecord
{
    protected static string $resource = BookForBorrowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
