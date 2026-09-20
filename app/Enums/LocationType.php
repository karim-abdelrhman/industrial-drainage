<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LocationType: string implements HasColor, HasLabel
{
    case InsideCity = 'inside_city';
    case OutsideCity = 'outside_city';

    public function getLabel(): string
    {
        return match ($this) {
            LocationType::InsideCity => 'داخل النطاق العمراني',
            LocationType::OutsideCity => 'خارج النطاق العمراني',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            LocationType::InsideCity => 'gray',
            LocationType::OutsideCity => 'warning',
        };
    }
}
