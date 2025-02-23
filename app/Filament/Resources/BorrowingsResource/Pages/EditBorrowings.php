<?php

namespace App\Filament\Resources\BorrowingsResource\Pages;

use App\Filament\Resources\BorrowingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBorrowings extends EditRecord
{
    protected static string $resource = BorrowingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
