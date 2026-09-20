<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Enums\SampleStatus;
use App\Enums\ViolationStatus;
use App\Models\Invoice;
use App\Models\Sample;
use App\Models\Violation;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $previousStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $previousEnd = now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $financial = Invoice::query()
            ->selectRaw('
                SUM(CASE WHEN status IN (?, ?) AND billing_month BETWEEN ? AND ? THEN total_amount ELSE 0 END) as current_claims,
                SUM(CASE WHEN status IN (?, ?) AND billing_month BETWEEN ? AND ? THEN total_amount ELSE 0 END) as previous_claims,
                SUM(CASE WHEN status = ? AND billing_month BETWEEN ? AND ? THEN total_amount ELSE 0 END) as paid_amount
            ', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::Paid->value,
                $monthStart,
                $monthEnd,
                InvoiceStatus::Issued->value,
                InvoiceStatus::Paid->value,
                $previousStart,
                $previousEnd,
                InvoiceStatus::Paid->value,
                $monthStart,
                $monthEnd,
            ])
            ->first();

        $outstanding = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Overdue])
            ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(total_amount), 0) as aggregate_sum')
            ->first();

        $overdue = Invoice::query()
            ->where('status', InvoiceStatus::Overdue)
            ->selectRaw('COUNT(*) as aggregate_count, COALESCE(SUM(total_amount), 0) as aggregate_sum')
            ->first();

        $activeViolations = Violation::query()
            ->where('status', ViolationStatus::Active)
            ->count();

        $samplesThisMonth = Sample::query()
            ->where('status', SampleStatus::Evaluated)
            ->whereBetween('sample_date', [$monthStart, $monthEnd])
            ->count();

        $currentClaims = (float) ($financial->current_claims ?? 0);
        $previousClaims = (float) ($financial->previous_claims ?? 0);
        $paidAmount = (float) ($financial->paid_amount ?? 0);
        $outstandingCount = (int) ($outstanding->aggregate_count ?? 0);
        $outstandingSum = (float) ($outstanding->aggregate_sum ?? 0);
        $overdueCount = (int) ($overdue->aggregate_count ?? 0);
        $overdueSum = (float) ($overdue->aggregate_sum ?? 0);

        $claimsDelta = $previousClaims > 0
            ? round((($currentClaims - $previousClaims) / $previousClaims) * 100)
            : null;

        $claimsDescription = $claimsDelta === null
            ? 'الفواتير الصادرة والمدفوعة للشهر الحالي'
            : ($claimsDelta >= 0
                ? "ارتفاع {$claimsDelta}% عن الشهر السابق"
                : 'انخفاض '.abs($claimsDelta).'% عن الشهر السابق');

        return [
            Stat::make('إجمالي المطالبات', Money::format($currentClaims))
                ->description($claimsDescription)
                ->color('primary'),

            Stat::make('المطالبات المحصلة', Money::format($paidAmount))
                ->description('المدفوع خلال الشهر الحالي')
                ->color('success'),

            Stat::make('المطالبات غير المحصلة', Money::format($outstandingSum))
                ->description($outstandingCount.' مطالبة صادرة أو متأخرة')
                ->color($outstandingCount > 0 ? 'warning' : 'success'),

            Stat::make('المطالبات المتأخرة', (string) $overdueCount)
                ->description(Money::format($overdueSum))
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('المخالفات النشطة', number_format($activeViolations))
                ->description('مخالفات غير مسوّاة')
                ->color($activeViolations > 0 ? 'danger' : 'success'),

            Stat::make('العينات هذا الشهر', number_format($samplesThisMonth))
                ->description('العينات التي تم تقييمها')
                ->color('info'),
        ];
    }
}
