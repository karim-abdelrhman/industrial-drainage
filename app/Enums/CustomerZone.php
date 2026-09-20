<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomerZone: string implements HasColor, HasLabel
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

    public function getColor(): string|array|null
    {
        return match ($this) {
            CustomerZone::City => 'primary',
            CustomerZone::IndustrialZone => 'info',
        };
    }
}
