<?php

namespace App\Support;

use App\Enums\InvoiceItemType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\SampleViolationSnapshot;

final class DataSheet
{
    public static function sampleResults(Sample $sample): string
    {
        $sample->loadMissing(['violationSnapshots.pollutant', 'readings.pollutant']);

        if ($sample->violationSnapshots->isNotEmpty()) {
            $rows = $sample->violationSnapshots->map(function (SampleViolationSnapshot $snapshot) use ($sample): string {
                $result = match ($snapshot->evaluation_result) {
                    'compliant' => ['مطابق', 'success'],
                    'violation' => ['مخالف', 'danger'],
                    default => ['غير مصنف', 'gray'],
                };

                $amount = (float) $sample->water_usage * 0.80 * (float) $snapshot->price_per_unit_at_time;
                $rowClass = $snapshot->evaluation_result === 'violation' ? ' is-violation' : '';

                return self::row([
                    e(($snapshot->pollutant?->code ? $snapshot->pollutant->code.' — ' : '').($snapshot->pollutant?->name ?? '—')),
                    e(number_format((float) $snapshot->detected_value, 4)),
                    e($snapshot->pollutant?->unit ?? '—'),
                    self::badge($result[0], $result[1]),
                    e($snapshot->tier_order_at_time ? 'المستوى '.$snapshot->tier_order_at_time : '—'),
                    e(Money::format($snapshot->price_per_unit_at_time)),
                    e(Money::format($amount)),
                ], $rowClass, numeric: [1, 5, 6]);
            })->implode('');

            return self::table(
                ['الملوث', 'التركيز', 'الوحدة', 'الحالة', 'المستوى', 'سعر الوحدة', 'قيمة المطالبة'],
                $rows,
            );
        }

        if ($sample->readings->isEmpty()) {
            return '<p class="data-sheet__empty">لا توجد نتائج تحاليل لهذه العينة بعد.</p>';
        }

        $rows = $sample->readings->map(function (SampleReading $reading): string {
            return self::row([
                e($reading->pollutant?->name ?? '—'),
                e(number_format((float) $reading->detected_value, 4)),
                e($reading->pollutant?->unit ?? '—'),
            ], numeric: [1]);
        })->implode('');

        return self::table(['الملوث', 'التركيز', 'الوحدة'], $rows);
    }

    public static function invoiceItems(Invoice $invoice): string
    {
        $invoice->loadMissing(['items.pollutant']);

        if ($invoice->items->isEmpty()) {
            return '<p class="data-sheet__empty">لا توجد بنود في هذه المطالبة.</p>';
        }

        $order = [
            InvoiceItemType::PollutantCharge->value,
            InvoiceItemType::CollectionFee->value,
            InvoiceItemType::AdminFee->value,
            InvoiceItemType::AnalysisFee->value,
            InvoiceItemType::IssuanceFee->value,
            InvoiceItemType::Vat->value,
            InvoiceItemType::Rounding->value,
        ];

        $items = $invoice->items->sortBy(function (InvoiceItem $item) use ($order): int {
            $type = $item->item_type instanceof InvoiceItemType
                ? $item->item_type->value
                : (string) $item->item_type;

            $index = array_search($type, $order, true);

            return $index === false ? 99 : $index;
        });

        $rows = $items->map(function (InvoiceItem $item): string {
            $type = $item->item_type instanceof InvoiceItemType
                ? $item->item_type
                : InvoiceItemType::from((string) $item->item_type);

            $isPollutant = $type === InvoiceItemType::PollutantCharge;
            $rowClass = $isPollutant && $item->tier_order !== null ? ' is-violation' : '';

            $label = $isPollutant
                ? (($item->pollutant?->code ? $item->pollutant->code.' — ' : '').($item->pollutant?->name ?? $type->getLabel()))
                : $type->getLabel();

            $tier = match (true) {
                ! $isPollutant => '—',
                $item->tier_order !== null => 'المستوى '.$item->tier_order,
                default => 'مطابق',
            };

            return self::row([
                e($label),
                e($item->notes ?: '—'),
                $isPollutant ? e(number_format((float) $item->detected_value, 4)) : '—',
                e($tier),
                $isPollutant ? e(Money::format($item->price_per_unit)) : '—',
                e(Money::format($item->amount)),
            ], $rowClass, numeric: [2, 4, 5]);
        })->implode('');

        $footer = '<tr class="data-sheet__total"><td colspan="5">إجمالي المطالبة</td><td class="is-num">'.e(Money::format($invoice->total_amount)).'</td></tr>';

        return self::table(
            ['البند', 'التفاصيل', 'التركيز', 'المستوى', 'سعر الوحدة', 'المبلغ'],
            $rows,
            $footer,
        );
    }

    /**
     * @param  list<string>  $headers
     */
    private static function table(array $headers, string $rows, ?string $footer = null): string
    {
        $head = collect($headers)->map(fn (string $header): string => '<th>'.e($header).'</th>')->implode('');

        return '<div class="data-sheet-wrap"><table class="data-sheet"><thead><tr>'.$head.'</tr></thead><tbody>'.$rows.'</tbody>'
            .($footer ? '<tfoot>'.$footer.'</tfoot>' : '')
            .'</table></div>';
    }

    /**
     * @param  list<string>  $cells
     * @param  list<int>  $numeric
     */
    private static function row(array $cells, string $class = '', array $numeric = []): string
    {
        $html = '';

        foreach ($cells as $index => $cell) {
            $tdClass = in_array($index, $numeric, true) ? ' class="is-num"' : '';
            $html .= "<td{$tdClass}>{$cell}</td>";
        }

        return '<tr class="'.trim($class).'">'.$html.'</tr>';
    }

    private static function badge(string $label, string $tone): string
    {
        return '<span class="data-sheet__badge data-sheet__badge--'.$tone.'">'.e($label).'</span>';
    }
}
