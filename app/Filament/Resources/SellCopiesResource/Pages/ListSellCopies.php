<?php

namespace App\Filament\Resources\SellCopiesResource\Pages;

use App\Filament\Resources\SellCopiesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSellCopies extends ListRecords
{
    protected static string $resource = SellCopiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
