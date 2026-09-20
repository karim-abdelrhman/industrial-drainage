<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum NavigationGroup: string implements HasIcon, HasLabel
{
    case Facilities = 'facilities';
    case Laboratory = 'laboratory';
    case Compliance = 'compliance';
    case Billing = 'billing';
    case Settings = 'settings';

    public function getLabel(): string
    {
        return match ($this) {
            self::Facilities => 'المنشآت',
            self::Laboratory => 'المعامل والعينات',
            self::Compliance => 'المخالفات والرقابة',
            self::Billing => 'المطالبات المالية',
            self::Settings => 'الإعدادات',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Facilities => Heroicon::OutlinedBuildingOffice2,
            self::Laboratory => Heroicon::OutlinedBeaker,
            self::Compliance => Heroicon::OutlinedShieldExclamation,
            self::Billing => Heroicon::OutlinedBanknotes,
            self::Settings => Heroicon::OutlinedCog6Tooth,
        };
    }
}
