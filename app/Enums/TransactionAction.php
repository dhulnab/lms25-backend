<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionAction: string implements HasLabel, HasColor
{
    case BORROW = 'borrow';
    case PURCHASE = 'purchase';
    case PENALTY_PAYMENT = 'penalty_payment';
    case BALANCE_UPDATE = 'balance_update';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BORROW => 'Borrow',
            self::PURCHASE => 'Purchase',
            self::PENALTY_PAYMENT => 'Penalty Payment',
            self::BALANCE_UPDATE => 'Balance Update',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::BORROW => 'customPurple',
            self::PURCHASE => 'primary',
            self::PENALTY_PAYMENT => 'warning',
            self::BALANCE_UPDATE => 'yellow',
        };
    }
}
