<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EscalationAlertsWidget;
use App\Filament\Widgets\KpiOverviewWidget;
use App\Filament\Widgets\MonthlyRevenueTrendWidget;
use App\Filament\Widgets\TopPollutantsWidget;
use App\Filament\Widgets\TopViolatingEstablishmentsWidget;
use Filament\Widgets\Widget;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'لوحة المعلومات';

    protected static ?string $navigationLabel = 'لوحة المعلومات';

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            KpiOverviewWidget::class,
            EscalationAlertsWidget::class,
            TopViolatingEstablishmentsWidget::class,
            TopPollutantsWidget::class,
            MonthlyRevenueTrendWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
