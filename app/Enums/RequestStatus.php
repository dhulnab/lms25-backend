<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RequestStatus: string implements HasLabel, HasColor
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::APPROVED => 'Pending',
            self::REJECTED => 'Expired',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
