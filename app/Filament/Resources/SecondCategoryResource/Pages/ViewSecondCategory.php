<?php

namespace App\Filament\Resources\SecondCategoryResource\Pages;

use App\Filament\Resources\SecondCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSecondCategory extends ViewRecord
{
    protected static string $resource = SecondCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
