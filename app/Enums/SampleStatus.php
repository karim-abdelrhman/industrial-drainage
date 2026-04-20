<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SampleStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Evaluated = 'evaluated';

    public function getLabel(): string
    {
        return match ($this) {
            SampleStatus::Pending => 'قيد الانتظار',
            SampleStatus::Evaluated => 'تم التقييم',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            SampleStatus::Pending => 'warning',
            SampleStatus::Evaluated => 'success',
        };
    }
}
