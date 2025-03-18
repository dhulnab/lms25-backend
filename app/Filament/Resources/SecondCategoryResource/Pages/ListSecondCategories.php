<?php

namespace App\Filament\Resources\SecondCategoryResource\Pages;

use App\Filament\Resources\SecondCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecondCategories extends ListRecords
{
    protected static string $resource = SecondCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
