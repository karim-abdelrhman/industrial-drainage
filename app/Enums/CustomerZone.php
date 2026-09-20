<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CustomerZone: string implements HasLabel
{
    case City = 'city';
    case IndustrialZone = 'industrial_zone';

    public function getLabel(): string
    {
        return match ($this) {
            CustomerZone::City => 'داخل المدن',
            CustomerZone::IndustrialZone => 'المنطقة الصناعية',
        };
    }
}
