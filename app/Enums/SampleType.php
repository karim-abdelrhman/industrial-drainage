<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SampleType: string implements HasLabel
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
}
