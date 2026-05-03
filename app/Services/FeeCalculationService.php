<?php

namespace App\Services;

use App\Enums\InvoiceItemType;
use App\Enums\LocationType;
use App\Enums\SampleType;
use App\Models\Sample;
use App\Models\SystemSetting;

class FeeCalculationService
{
    /**
     * Build the fee line items for a sample invoice.
     *
     * Fee order:
     *  1. Collection fee  (composite = fixed 1400, else inside/outside rate)
     *  2. Admin fee       (20% of collection fee)
     *  3. Analysis fee    (fixed per sample)
     *  4. Issuance fee    (fixed)
     *  5. VAT             (14% of everything above + pollutant charges)
     *  6. Rounding        (ceil adjustment so total is a whole number)
     *
     * @param  float  $pollutantSubtotal  Sum of all pollutant charge amounts already computed
     * @return array{items: list<array<string, mixed>>, total_fees: float, grand_total: float}
     */
    public function calculate(Sample $sample, float $pollutantSubtotal): array
    {
        $sample->loadMissing('establishment');

        $collectionFee = $this->resolveCollectionFee($sample);
        $adminFee = round($collectionFee * SystemSetting::get('admin_fee_percentage') / 100, 4);
        $analysisFee = SystemSetting::get('analysis_fee');
        $issuanceFee = SystemSetting::get('issuance_fee');

        $preVatTotal = $pollutantSubtotal + $collectionFee + $adminFee + $analysisFee + $issuanceFee;
        $vatAmount = round($preVatTotal * SystemSetting::get('vat_percentage') / 100, 4);

        $preRoundTotal = $preVatTotal + $vatAmount;
        $roundedTotal = (float) ceil($preRoundTotal);
        $roundingAmount = round($roundedTotal - $preRoundTotal, 4);

        $vatPct = SystemSetting::get('vat_percentage');
        $adminPct = SystemSetting::get('admin_fee_percentage');

        $items = [
            $this->feeItem(InvoiceItemType::CollectionFee, $collectionFee, $this->collectionFeeNote($sample)),
            $this->feeItem(InvoiceItemType::AdminFee, $adminFee, "نسبة {$adminPct}% من رسوم الجمع"),
            $this->feeItem(InvoiceItemType::AnalysisFee, $analysisFee, 'رسوم تحليل العينة'),
            $this->feeItem(InvoiceItemType::IssuanceFee, $issuanceFee, 'رسوم إصدار المطالبة'),
            $this->feeItem(InvoiceItemType::Vat, $vatAmount, "ضريبة القيمة المضافة {$vatPct}%"),
        ];

        if ($roundingAmount > 0) {
            $items[] = $this->feeItem(InvoiceItemType::Rounding, $roundingAmount, 'تسوية للأعلى لأقرب جنيه');
        }

        return [
            'items' => $items,
            'total_fees' => $collectionFee + $adminFee + $analysisFee + $issuanceFee + $vatAmount + $roundingAmount,
            'grand_total' => $roundedTotal,
        ];
    }

    /**
     * Build a preview-only fee breakdown (same logic, no DB writes).
     *
     * @return array{lines: list<array<string, mixed>>, total_fees: float, grand_total: float}
     */
    public function preview(Sample $sample, float $pollutantSubtotal): array
    {
        $result = $this->calculate($sample, $pollutantSubtotal);

        $lines = array_map(fn (array $item) => [
            'label' => InvoiceItemType::from($item['item_type'])->getLabel(),
            'notes' => $item['notes'],
            'amount' => $item['amount'],
        ], $result['items']);

        return [
            'lines' => $lines,
            'total_fees' => $result['total_fees'],
            'grand_total' => $result['grand_total'],
        ];
    }

    private function resolveCollectionFee(Sample $sample): float
    {
        if ($sample->sample_type === SampleType::Composite) {
            return SystemSetting::get('collection_fee_composite');
        }

        return $sample->establishment->location_type === LocationType::OutsideCity
            ? SystemSetting::get('collection_fee_outside_city')
            : SystemSetting::get('collection_fee_inside_city');
    }

    private function collectionFeeNote(Sample $sample): string
    {
        if ($sample->sample_type === SampleType::Composite) {
            return 'عينة مركبة';
        }

        return $sample->establishment->location_type === LocationType::OutsideCity
            ? 'خارج النطاق العمراني'
            : 'داخل النطاق العمراني';
    }

    /** @return array<string, mixed> */
    private function feeItem(InvoiceItemType $type, float $amount, string $notes): array
    {
        return [
            'item_type' => $type->value,
            'violation_id' => null,
            'pollutant_id' => null,
            'violation_rule_id' => null,
            'tier_order' => null,
            'price_per_unit' => 0,
            'detected_value' => 0,
            'amount' => $amount,
            'notes' => $notes,
        ];
    }
}
