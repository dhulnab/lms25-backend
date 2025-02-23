<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookForSellStatus: string implements HasLabel, HasColor
{
    case SOLD = 'sold';
    case UNSOLD = 'unsold';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SOLD => 'Sold',
            self::UNSOLD => 'Unsold',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::UNSOLD => 'success',
            self::SOLD => 'warning',
        };
    }
}
