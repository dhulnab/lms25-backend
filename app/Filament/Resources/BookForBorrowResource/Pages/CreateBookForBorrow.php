<?php

namespace App\Filament\Resources\BookForBorrowResource\Pages;

use App\Filament\Resources\BookForBorrowResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookForBorrow extends CreateRecord
{
    protected static string $resource = BookForBorrowResource::class;
}
