<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SampleType: string implements HasColor, HasLabel
{
    case Regular = 'regular';
    case Composite = 'composite';

    public function getLabel(): string
    {
        return match ($this) {
            SampleType::Regular => 'عادية',
            SampleType::Composite => 'مركبة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            SampleType::Regular => 'gray',
            SampleType::Composite => 'primary',
        };
    }
}
