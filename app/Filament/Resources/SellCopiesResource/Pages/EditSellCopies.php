<?php

namespace App\Filament\Resources\SellCopiesResource\Pages;

use App\Filament\Resources\SellCopiesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSellCopies extends EditRecord
{
    protected static string $resource = SellCopiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
