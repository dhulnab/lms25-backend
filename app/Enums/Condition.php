<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Condition: string implements HasLabel, HasColor
{
    case NEW = 'new';
    case USED_GOOD = 'used_good';
    case USED_FAIR = 'used_fair';
    case DAMAGED = 'damaged';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEW => 'New',
            self::USED_GOOD => 'Used - Good',
            self::USED_FAIR => 'Used - Fair',
            self::DAMAGED => 'Damaged',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::NEW => 'customPurple',
            self::USED_GOOD => 'success',
            self::USED_FAIR => 'warning',
            self::DAMAGED => 'danger',
        };
    }
}
