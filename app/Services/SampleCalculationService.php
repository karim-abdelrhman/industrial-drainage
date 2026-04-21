<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\Violation;
use App\Models\ViolationRule;
use App\Models\ViolationRuleTier;

class SampleCalculationService
{
    /**
     * Calculate the full cost breakdown for a sample.
     *
     * @return array{lines: array<int, array{pollutant_id: int, pollutant_name: string, unit: string, detected_value: float, rule_id: int|null, tier_order: int|null, price_per_unit: float|null, subtotal: float}>, total: float}
     */
    public function calculateSample(Sample $sample): array
    {
        $sample->loadMissing('readings.pollutant', 'establishment');

        $lines = [];
        $total = 0.0;

        foreach ($sample->readings as $reading) {
            $line = $this->calculateReading($reading, $sample->establishment->activity_type);
            $total += $line['subtotal'];
            $lines[] = $line;
        }

        return ['lines' => $lines, 'total' => $total];
    }

    /**
     * Calculate cost for a single reading.
     *
     * @return array{pollutant_id: int, pollutant_name: string, unit: string, detected_value: float, rule_id: int|null, tier_order: int|null, price_per_unit: float|null, subtotal: float}
     */
    public function calculateReading(SampleReading $reading, ActivityType $activityType): array
    {
        $reading->loadMissing('pollutant');

        $rule = $this->getRuleForValue($reading->pollutant_id, (float) $reading->detected_value, $activityType);

        if ($rule === null) {
            return [
                'pollutant_id' => $reading->pollutant_id,
                'pollutant_name' => $reading->pollutant->name,
                'unit' => $reading->pollutant->unit,
                'detected_value' => (float) $reading->detected_value,
                'rule_id' => null,
                'tier_order' => null,
                'price_per_unit' => null,
                'subtotal' => 0.0,
            ];
        }

        $tier = $this->getCurrentTierForEstablishment($rule, $reading->sample->establishment_id, $reading->pollutant_id);

        $pricePerUnit = $tier !== null ? (float) $tier->price_per_unit : 0.0;
        $subtotal = $pricePerUnit * (float) $reading->detected_value;

        return [
            'pollutant_id' => $reading->pollutant_id,
            'pollutant_name' => $reading->pollutant->name,
            'unit' => $reading->pollutant->unit,
            'detected_value' => (float) $reading->detected_value,
            'rule_id' => $rule->id,
            'tier_order' => $tier?->tier_order,
            'price_per_unit' => $pricePerUnit,
            'subtotal' => $subtotal,
        ];
    }

    public function getRuleForValue(int $pollutantId, float $value, ActivityType $activityType): ?ViolationRule
    {
        return ViolationRule::query()
            ->where('pollutant_id', $pollutantId)
            ->where('activity_type', $activityType)
            ->where('from', '<=', $value)
            ->where(function ($q) use ($value) {
                $q->whereNull('to')->orWhere('to', '>=', $value);
            })
            ->with('tiers')
            ->first();
    }

    public function getCurrentTierForEstablishment(ViolationRule $rule, int $establishmentId, int $pollutantId): ?ViolationRuleTier
    {
        $violation = Violation::query()
            ->where('establishment_id', $establishmentId)
            ->where('pollutant_id', $pollutantId)
            ->where('violation_rule_id', $rule->id)
            ->whereIn('status', ['active'])
            ->first();

        $tierOrder = $violation?->current_tier ?? 1;

        return $rule->tiers->firstWhere('tier_order', $tierOrder)
            ?? $rule->tiers->sortBy('tier_order')->first();
    }
}
