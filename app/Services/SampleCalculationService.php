<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\ViolationStatus;
use App\Models\Sample;
use App\Models\SampleReading;
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
        $pollutantSubtotal = 0.0;

        foreach ($sample->readings as $reading) {
            $line = $this->calculateReading($reading, $sample->sample_date, (float) $sample->water_usage, $sample->establishment->activity_type, $sample->establishment_id);
            $pollutantSubtotal += $line['amount'];
            $lines[] = $line;
        }

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
     * @return array{pollutant_id: int, pollutant_name: string, unit: string, detected_value: float, evaluation_result: string, tier_order: int|null, price_per_unit: float, amount: float}
     */
    public function calculateReading(
        SampleReading $reading,
        Carbon $evaluationDate,
        float $waterUsage,
        ActivityType $activityType,
        int $establishmentId,
    ): array {
        $reading->loadMissing('pollutant');
        $value = (float) $reading->detected_value;

        $limit = $this->violationService->findLimit($reading->pollutant_id, $value, $activityType);

        if ($limit !== null) {
            $pricePerUnit = (float) $limit->price_per_unit;

            return [
                'pollutant_id' => $reading->pollutant_id,
                'pollutant_name' => $reading->pollutant->name,
                'unit' => $reading->pollutant->unit,
                'detected_value' => $value,
                'evaluation_result' => 'compliant',
                'tier_order' => null,
                'price_per_unit' => $pricePerUnit,
                'amount' => $waterUsage * self::WASTEWATER_COEFFICIENT * $pricePerUnit,
            ];
        }

        $rule = $this->violationService->findRule($reading->pollutant_id, $value, $activityType);

        if ($rule === null) {
            return [
                'pollutant_id' => $reading->pollutant_id,
                'pollutant_name' => $reading->pollutant->name,
                'unit' => $reading->pollutant->unit,
                'detected_value' => $value,
                'evaluation_result' => 'unclassified',
                'tier_order' => null,
                'price_per_unit' => 0.0,
                'amount' => 0.0,
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
            'pollutant_name' => $reading->pollutant->name,
            'unit' => $reading->pollutant->unit,
            'detected_value' => $value,
            'evaluation_result' => 'violation',
            'tier_order' => $tierOrder,
            'price_per_unit' => $pricePerUnit,
            'amount' => $waterUsage * self::WASTEWATER_COEFFICIENT * $pricePerUnit,
        ];
    }
}
