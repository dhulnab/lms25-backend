<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case DONE = 'done';
    case FAIL = 'fail';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::DONE => 'Done',
            self::FAIL => 'Fail',
        };
    }
    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::DONE => 'primary',
            self::FAIL => 'red',
        };
    }
}
