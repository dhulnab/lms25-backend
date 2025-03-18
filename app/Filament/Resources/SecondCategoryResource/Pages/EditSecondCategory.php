<?php

namespace App\Filament\Resources\SecondCategoryResource\Pages;

use App\Filament\Resources\SecondCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSecondCategory extends EditRecord
{
    protected static string $resource = SecondCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
