<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueTrendWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'اتجاه الإيرادات الشهرية (آخر 6 أشهر)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $revenues = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Paid])
            ->where('billing_month', '>=', $months->first())
            ->selectRaw('DATE_FORMAT(billing_month, "%Y-%m") as month, SUM(total_amount) as total')
            ->groupByRaw('DATE_FORMAT(billing_month, "%Y-%m")')
            ->pluck('total', 'month');

        $labels = $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->toArray();
        $data = $months->map(fn (Carbon $m) => (float) ($revenues[$m->format('Y-m')] ?? 0))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (جنيه)',
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
