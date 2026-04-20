<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PollutantStatus: string implements HasColor, HasLabel
{
    case Compliant = 'compliant';
    case Violation = 'violation';

    public function getLabel(): string
    {
        return match ($this) {
            PollutantStatus::Compliant => 'مطابق',
            PollutantStatus::Violation => 'مخالفة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            PollutantStatus::Compliant => 'success',
            PollutantStatus::Violation => 'danger',
        };
    }
}
