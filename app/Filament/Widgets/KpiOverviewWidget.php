<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Enums\ViolationStatus;
use App\Models\Invoice;
use App\Models\Sample;
use App\Models\Violation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class KpiOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $financialClaims = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Paid])
            ->whereBetween('billing_month', [$monthStart, $monthEnd])
            ->sum('total_amount');

        $activeViolations = Violation::query()
            ->where('status', ViolationStatus::Active)
            ->count();

        $overdueInvoices = Invoice::query()
            ->where('status', InvoiceStatus::Overdue)
            ->get(['total_amount']);

        $samplesThisMonth = Sample::query()
            ->whereBetween('sample_date', [$monthStart, $monthEnd])
            ->count();

        return [
            Stat::make('إجمالي المطالبات المالية (الشهر الحالي)', Number::currency($financialClaims, 'EGP', 'ar'))
                ->description('الفواتير الصادرة والمدفوعة فقط')
                ->color('primary'),

            Stat::make('المخالفات النشطة', number_format($activeViolations))
                ->description('مخالفات غير مسوّاة')
                ->color($activeViolations > 0 ? 'danger' : 'success'),

            Stat::make('الفواتير المتأخرة', number_format($overdueInvoices->count()))
                ->description('إجمالي: '.Number::currency($overdueInvoices->sum('total_amount'), 'EGP', 'ar'))
                ->color($overdueInvoices->count() > 0 ? 'danger' : 'success'),

            Stat::make('العينات هذا الشهر', number_format($samplesThisMonth))
                ->description('العينات المُجمَّعة في الشهر الحالي')
                ->color('info'),
        ];
    }
}
