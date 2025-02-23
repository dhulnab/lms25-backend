<?php

namespace App\Filament\Resources\FirstCategoryResource\Pages;

use App\Filament\Resources\FirstCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFirstCategory extends ViewRecord
{
    protected static string $resource = FirstCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
