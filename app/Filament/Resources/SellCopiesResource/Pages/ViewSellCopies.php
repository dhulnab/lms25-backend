<?php

namespace App\Filament\Resources\SellCopiesResource\Pages;

use App\Filament\Resources\SellCopiesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSellCopies extends ViewRecord
{
    protected static string $resource = SellCopiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
