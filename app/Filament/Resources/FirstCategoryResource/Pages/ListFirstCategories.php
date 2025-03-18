<?php

namespace App\Filament\Resources\FirstCategoryResource\Pages;

use App\Filament\Resources\FirstCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFirstCategories extends ListRecords
{
    protected static string $resource = FirstCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
