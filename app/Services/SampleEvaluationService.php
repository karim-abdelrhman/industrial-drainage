<?php

namespace App\Services;

use App\Enums\SampleStatus;
use App\Enums\ViolationStatus;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\SampleViolationSnapshot;
use App\Models\Violation;
use App\Models\ViolationRule;
use Illuminate\Support\Facades\DB;

class SampleEvaluationService
{
    public function __construct(
        private readonly ViolationService $violationService
    ) {}

    /**
     * Evaluate all readings in a sample, update violations, and snapshot state.
     * Idempotent: re-evaluating the same sample will overwrite previous snapshots.
     */
    public function evaluate(Sample $sample): void
    {
        if ($sample->status === SampleStatus::Evaluated) {
            return;
        }

        $sample->load('readings.pollutant', 'establishment');

        DB::transaction(function () use ($sample) {
            $sample->violationSnapshots()->delete();

            foreach ($sample->readings as $reading) {
                $this->evaluateReading($sample, $reading);
            }

            $sample->update([
                'status' => SampleStatus::Evaluated,
                'evaluated_at' => now(),
            ]);
        });
    }

    private function evaluateReading(Sample $sample, SampleReading $reading): void
    {
        $rule = $this->violationService->findRule(
            $reading->pollutant_id,
            (float) $reading->detected_value,
            $sample->establishment->activity_type
        );

        $existingViolation = Violation::query()
            ->where('establishment_id', $sample->establishment_id)
            ->where('pollutant_id', $reading->pollutant_id)
            ->where('status', ViolationStatus::Active)
            ->with(['violationRule.tiers'])
            ->first();

        if ($rule === null) {
            if ($existingViolation !== null) {
                $this->violationService->resolve($existingViolation);
            }

            $this->snapshot($sample, $reading, null, null, 'compliant');

            return;
        }

        if ($existingViolation !== null) {
            $existingViolation->update([
                'violation_rule_id' => $rule->id,
                'detected_value' => $reading->detected_value,
                'last_sample_id' => $sample->id,
                'last_evaluated_at' => now(),
            ]);

            $existingViolation->refresh()->load('violationRule.tiers');
            $this->violationService->advanceTier($existingViolation);

            $this->snapshot($sample, $reading, $existingViolation, $rule, 'violation');
        } else {
            $violation = $this->violationService->createViolation([
                'establishment_id' => $sample->establishment_id,
                'pollutant_id' => $reading->pollutant_id,
                'violation_rule_id' => $rule->id,
                'detected_value' => $reading->detected_value,
                'start_date' => $sample->sample_date,
                'last_sample_id' => $sample->id,
            ]);

            $violation->load('violationRule.tiers');
            $this->snapshot($sample, $reading, $violation, $rule, 'violation');
        }
    }

    private function snapshot(
        Sample $sample,
        SampleReading $reading,
        ?Violation $violation,
        ?ViolationRule $rule,
        string $result
    ): void {
        $tierPrice = $violation !== null
            ? $this->violationService->getCurrentTierPrice($violation)
            : null;

        SampleViolationSnapshot::create([
            'sample_id' => $sample->id,
            'sample_reading_id' => $reading->id,
            'establishment_id' => $sample->establishment_id,
            'pollutant_id' => $reading->pollutant_id,
            'violation_id' => $violation?->id,
            'violation_rule_id' => $rule?->id,
            'detected_value' => $reading->detected_value,
            'tier_order_at_time' => $violation?->current_tier,
            'price_per_unit_at_time' => $tierPrice,
            'evaluation_result' => $result,
        ]);
    }
}
