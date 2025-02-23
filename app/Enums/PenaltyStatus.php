<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PenaltyStatus: string implements HasLabel, HasColor
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case WAIVED = 'waived';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::UNPAID => 'Unpaid',
            self::WAIVED => 'Waived',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::PAID => 'success',
            self::UNPAID => 'warning',
            self::WAIVED => 'purple',
        };
    }
}
