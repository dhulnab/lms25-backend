<?php

namespace App\Filament\Resources\ThirdCategoryResource\Pages;

use App\Filament\Resources\ThirdCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditThirdCategory extends EditRecord
{
    protected static string $resource = ThirdCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
