<?php

namespace App\Filament\Resources\ThirdCategoryResource\Pages;

use App\Filament\Resources\ThirdCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListThirdCategories extends ListRecords
{
    protected static string $resource = ThirdCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
