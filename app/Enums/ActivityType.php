<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasColor, HasLabel
{
    case Industrial = 'industrial';
    case Commercial = 'commercial';

    public function getLabel(): string
    {
        return match ($this) {
            ActivityType::Industrial => 'صناعي',
            ActivityType::Commercial => 'تجاري',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            ActivityType::Industrial => 'primary',
            ActivityType::Commercial => 'gray',
        };
    }
}
