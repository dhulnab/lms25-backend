<?php

namespace App\Filament\Resources\BookForBorrowResource\Pages;

use App\Filament\Resources\BookForBorrowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookForBorrow extends EditRecord
{
    protected static string $resource = BookForBorrowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
