<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ViolationStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Resolved = 'resolved';

    public function getLabel(): string
    {
        return match ($this) {
            ViolationStatus::Active => 'نشط',
            ViolationStatus::Resolved => 'تم الحل',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            ViolationStatus::Active => 'danger',
            ViolationStatus::Resolved => 'success',
        };
    }
}
