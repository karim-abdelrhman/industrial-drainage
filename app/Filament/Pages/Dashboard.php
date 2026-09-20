<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EscalationAlertsWidget;
use App\Filament\Widgets\KpiOverviewWidget;
use App\Filament\Widgets\MonthlyRevenueTrendWidget;
use App\Filament\Widgets\OperationalAlertsWidget;
use App\Filament\Widgets\TopPollutantsWidget;
use App\Filament\Widgets\TopViolatingEstablishmentsWidget;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected static ?string $navigationLabel = 'الرئيسية';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    public function getHeading(): string|Htmlable|null
    {
        return 'لوحة التحكم';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'نظرة عامة على المطالبات المالية والمخالفات والعينات';
    }

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            KpiOverviewWidget::class,
            OperationalAlertsWidget::class,
            MonthlyRevenueTrendWidget::class,
            TopViolatingEstablishmentsWidget::class,
            TopPollutantsWidget::class,
            EscalationAlertsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
