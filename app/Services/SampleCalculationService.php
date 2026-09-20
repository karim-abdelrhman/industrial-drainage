<?php

namespace App\Services;

use App\Enums\CustomerZone;
use App\Enums\ViolationStatus;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\SystemSetting;
use App\Models\Violation;
use Carbon\Carbon;

class SampleCalculationService
{
    private const WASTEWATER_COEFFICIENT = 0.80;

    public function __construct(
        private readonly ViolationService $violationService,
        private readonly FeeCalculationService $feeCalculationService,
    ) {}

    /**
     * Preview the full cost breakdown for a sample without persisting anything.
     *
     * @return array{lines: list<array<string, mixed>>, pollutant_subtotal: float, fees: list<array<string, mixed>>, total_fees: float, grand_total: float}
     */
    public function calculateSample(Sample $sample): array
    {
        $sample->loadMissing(['readings.pollutant', 'establishment']);

        $lines = [];

        foreach ($sample->readings as $reading) {
            $line = $this->calculateReading($reading, $sample->sample_date, (float) $sample->water_usage, $sample->establishment->customer_zone, $sample->establishment_id);
            $lines[] = $line;
        }

        $lines = $this->applyCodDiscountWhenBodViolates($lines);
        $pollutantSubtotal = array_sum(array_column($lines, 'amount'));

        $feePreview = $this->feeCalculationService->preview($sample, $pollutantSubtotal);

        return [
            'lines' => $lines,
            'pollutant_subtotal' => $pollutantSubtotal,
            'fees' => $feePreview['lines'],
            'total_fees' => $feePreview['total_fees'],
            'grand_total' => $feePreview['grand_total'],
        ];
    }

    /**
     * Preview the cost for a single reading.
     *
     * @return array{pollutant_id: int, pollutant_code: string, pollutant_name: string, unit: string, detected_value: float, evaluation_result: string, tier_order: int|null, price_per_unit: float, amount: float, notes: string|null}
     */
    public function calculateReading(
        SampleReading $reading,
        Carbon $evaluationDate,
        float $waterUsage,
        CustomerZone $customerZone,
        int $establishmentId,
    ): array {
        $reading->loadMissing('pollutant');
        $value = (float) $reading->detected_value;

        $limit = $this->violationService->findLimit($reading->pollutant_id, $value, $customerZone);

        if ($limit !== null) {
            $pricePerUnit = (float) $limit->price_per_unit;

            return [
                'pollutant_id' => $reading->pollutant_id,
                'pollutant_code' => $reading->pollutant->code,
                'pollutant_name' => $reading->pollutant->name,
                'unit' => $reading->pollutant->unit,
                'detected_value' => $value,
                'evaluation_result' => 'compliant',
                'tier_order' => null,
                'price_per_unit' => $pricePerUnit,
                'amount' => $waterUsage * self::WASTEWATER_COEFFICIENT * $pricePerUnit,
                'notes' => null,
            ];
        }

        $rule = $this->violationService->findRule($reading->pollutant_id, $value);

        if ($rule === null) {
            return [
                'pollutant_id' => $reading->pollutant_id,
                'pollutant_code' => $reading->pollutant->code,
                'pollutant_name' => $reading->pollutant->name,
                'unit' => $reading->pollutant->unit,
                'detected_value' => $value,
                'evaluation_result' => 'unclassified',
                'tier_order' => null,
                'price_per_unit' => 0.0,
                'amount' => 0.0,
                'notes' => null,
            ];
        }

        $existingViolation = Violation::query()
            ->where('establishment_id', $establishmentId)
            ->where('pollutant_id', $reading->pollutant_id)
            ->where('status', ViolationStatus::Active)
            ->with(['violationRule.tiers'])
            ->first();

        if ($existingViolation !== null) {
            $tierOrder = $this->violationService->computeTier($existingViolation, $evaluationDate);
        } else {
            $tierOrder = 1;
        }

        $tierModel = $rule->tiers->firstWhere('tier_order', $tierOrder);
        $pricePerUnit = (float) ($tierModel?->price_per_unit ?? 0);

        return [
            'pollutant_id' => $reading->pollutant_id,
            'pollutant_code' => $reading->pollutant->code,
            'pollutant_name' => $reading->pollutant->name,
            'unit' => $reading->pollutant->unit,
            'detected_value' => $value,
            'evaluation_result' => 'violation',
            'tier_order' => $tierOrder,
            'price_per_unit' => $pricePerUnit,
            'amount' => $waterUsage * self::WASTEWATER_COEFFICIENT * $pricePerUnit,
            'notes' => null,
        ];
    }

    /**
     * Apply a COD unit-price discount when the same sample also has a BOD violation.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyCodDiscountWhenBodViolates(array $lines): array
    {
        $percent = SystemSetting::get('cod_discount_when_bod_violation_percent', 40);
        $multiplier = 1 - ($percent / 100);

        $bodViolates = false;
        $codIndexes = [];

        foreach ($lines as $index => $line) {
            $code = $line['pollutant_code'] ?? null;
            $result = $line['evaluation_result'] ?? null;

            if ($code === 'BOD' && $result === 'violation') {
                $bodViolates = true;
            }

            if ($code === 'COD' && $result === 'violation') {
                $codIndexes[] = $index;
            }
        }

        if (! $bodViolates || $codIndexes === [] || $multiplier >= 1) {
            return $lines;
        }

        $note = "خصم {$percent}% لاجتماع مخالفة BOD";

        foreach ($codIndexes as $index) {
            $lines[$index]['price_per_unit'] = round((float) $lines[$index]['price_per_unit'] * $multiplier, 4);
            $lines[$index]['amount'] = round((float) $lines[$index]['amount'] * $multiplier, 4);
            $lines[$index]['notes'] = $note;
        }

        return $lines;
    }
}
