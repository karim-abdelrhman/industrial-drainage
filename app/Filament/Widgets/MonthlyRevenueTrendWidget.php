<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueTrendWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'المطالبات المالية';

    protected ?string $description = 'إجمالي المطالبات والمحصّل وغير المحصّل خلال آخر 6 أشهر';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $issued = InvoiceStatus::Issued->value;
        $paid = InvoiceStatus::Paid->value;
        $overdue = InvoiceStatus::Overdue->value;

        $rows = Invoice::query()
            ->where('billing_month', '>=', $months->first())
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Paid, InvoiceStatus::Overdue])
            ->selectRaw('DATE_FORMAT(billing_month, "%Y-%m") as month')
            ->selectRaw("SUM(CASE WHEN status IN ('{$issued}', '{$paid}') THEN total_amount ELSE 0 END) as billed")
            ->selectRaw("SUM(CASE WHEN status = '{$paid}' THEN total_amount ELSE 0 END) as collected")
            ->selectRaw("SUM(CASE WHEN status IN ('{$issued}', '{$overdue}') THEN total_amount ELSE 0 END) as outstanding")
            ->groupByRaw('DATE_FORMAT(billing_month, "%Y-%m")')
            ->get()
            ->keyBy('month');

        $labels = $months->map(fn (Carbon $month) => $month->translatedFormat('M Y'))->all();

        return [
            'datasets' => [
                [
                    'label' => 'إجمالي المطالبات',
                    'data' => $months->map(fn (Carbon $month) => (float) ($rows[$month->format('Y-m')]->billed ?? 0))->all(),
                    'borderColor' => '#155E75',
                    'backgroundColor' => 'rgba(21, 94, 117, 0.08)',
                    'fill' => false,
                    'tension' => 0.25,
                ],
                [
                    'label' => 'المحصل',
                    'data' => $months->map(fn (Carbon $month) => (float) ($rows[$month->format('Y-m')]->collected ?? 0))->all(),
                    'borderColor' => '#15803D',
                    'backgroundColor' => 'rgba(21, 128, 61, 0.08)',
                    'fill' => false,
                    'tension' => 0.25,
                ],
                [
                    'label' => 'غير المحصل',
                    'data' => $months->map(fn (Carbon $month) => (float) ($rows[$month->format('Y-m')]->outstanding ?? 0))->all(),
                    'borderColor' => '#D97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.08)',
                    'fill' => false,
                    'tension' => 0.25,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'rtl' => true,
                    'labels' => [
                        'boxWidth' => 12,
                        'usePointStyle' => true,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'maxTicksLimit' => 6,
                    ],
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.18)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
