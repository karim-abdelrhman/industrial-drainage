<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasLabel
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
}
