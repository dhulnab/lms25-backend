<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookForBorrowStatus: string implements HasLabel, HasColor
{
    case AVAILABLE = 'available';
    case BORROWED = 'borrowed';
    case REQUESTED = 'requested';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::BORROWED => 'Borrowed',
            self::REQUESTED => 'Requested',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::BORROWED => 'warning',
            self::REQUESTED => 'customPurple',
        };
    }
}
