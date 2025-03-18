<?php

namespace App\Filament\Resources\FirstCategoryResource\Pages;

use App\Filament\Resources\FirstCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFirstCategory extends EditRecord
{
    protected static string $resource = FirstCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
