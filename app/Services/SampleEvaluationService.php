<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\SampleStatus;
use App\Enums\ViolationStatus;
use App\Models\Invoice;
use App\Models\PollutantLimit;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\SampleViolationSnapshot;
use App\Models\Violation;
use App\Models\ViolationRule;
use Illuminate\Support\Facades\DB;

class SampleEvaluationService
{
    private const WASTEWATER_COEFFICIENT = 0.80;

    public function __construct(
        private readonly ViolationService $violationService
    ) {}

    /**
     * Evaluate all readings in a sample, create/resolve violations, and generate the invoice.
     * Idempotent: no-op if the sample is already evaluated.
     */
    public function evaluate(Sample $sample): Invoice
    {
        if ($sample->status === SampleStatus::Evaluated) {
            return $sample->invoice ?? Invoice::where('sample_id', $sample->id)->firstOrFail();
        }

        $sample->load(['readings.pollutant', 'establishment']);

        return DB::transaction(function () use ($sample) {
            $invoice = Invoice::firstOrCreate(
                ['establishment_id' => $sample->establishment_id, 'sample_id' => $sample->id],
                [
                    'billing_month' => $sample->sample_date->startOfMonth()->toDateString(),
                    'status' => InvoiceStatus::Draft,
                    'total_amount' => 0,
                ]
            );

            $invoice->items()->delete();
            $sample->violationSnapshots()->delete();

            $totalAmount = 0;

            foreach ($sample->readings as $reading) {
                [$amount, $snapshotData, $itemData] = $this->processReading($sample, $reading);

                SampleViolationSnapshot::create($snapshotData);
                $invoice->items()->create($itemData);

                $totalAmount += $amount;
            }

            $invoice->update(['total_amount' => $totalAmount]);

            $sample->update([
                'status' => SampleStatus::Evaluated,
                'evaluated_at' => now(),
            ]);

            return $invoice->fresh();
        });
    }

    /**
     * Process one reading: classify, create/resolve violation, compute amount.
     *
     * @return array{0: float, 1: array<string, mixed>, 2: array<string, mixed>}
     */
    private function processReading(Sample $sample, SampleReading $reading): array
    {
        $value = (float) $reading->detected_value;
        $activityType = $sample->establishment->activity_type;

        $limit = $this->violationService->findLimit($reading->pollutant_id, $value, $activityType);

        if ($limit !== null) {
            return $this->handleCompliant($sample, $reading, $limit);
        }

        $rule = $this->violationService->findRule($reading->pollutant_id, $value, $activityType);

        if ($rule !== null) {
            return $this->handleViolation($sample, $reading, $rule);
        }

        return $this->handleUnclassified($sample, $reading);
    }

    private function handleCompliant(Sample $sample, SampleReading $reading, PollutantLimit $limit): array
    {
        $existingViolation = $this->findActiveViolation($sample->establishment_id, $reading->pollutant_id);

        if ($existingViolation !== null) {
            $this->violationService->resolve($existingViolation);
        }

        $pricePerUnit = (float) $limit->price_per_unit;
        $amount = (float) $sample->water_usage * self::WASTEWATER_COEFFICIENT * $pricePerUnit;

        $snapshot = [
            'sample_id' => $sample->id,
            'sample_reading_id' => $reading->id,
            'establishment_id' => $sample->establishment_id,
            'pollutant_id' => $reading->pollutant_id,
            'violation_id' => null,
            'violation_rule_id' => null,
            'detected_value' => $reading->detected_value,
            'tier_order_at_time' => null,
            'price_per_unit_at_time' => $pricePerUnit,
            'evaluation_result' => 'compliant',
        ];

        $item = [
            'violation_id' => null,
            'pollutant_id' => $reading->pollutant_id,
            'violation_rule_id' => null,
            'tier_order' => null,
            'price_per_unit' => $pricePerUnit,
            'detected_value' => $reading->detected_value,
            'amount' => $amount,
        ];

        return [$amount, $snapshot, $item];
    }

    private function handleViolation(Sample $sample, SampleReading $reading, ViolationRule $rule): array
    {
        $existingViolation = $this->findActiveViolation($sample->establishment_id, $reading->pollutant_id);

        if ($existingViolation !== null) {
            $existingViolation->update([
                'violation_rule_id' => $rule->id,
                'detected_value' => $reading->detected_value,
                'last_sample_id' => $sample->id,
                'last_evaluated_at' => now(),
            ]);
            $existingViolation->refresh()->load('violationRule.tiers');
            $violation = $existingViolation;
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
        }

        $tierOrder = $this->violationService->computeTier($violation, $sample->sample_date);
        $this->violationService->syncTier($violation, $tierOrder, $sample->sample_date);

        $tierModel = $rule->tiers->firstWhere('tier_order', $tierOrder);
        $pricePerUnit = (float) ($tierModel?->price_per_unit ?? 0);
        $amount = (float) $sample->water_usage * self::WASTEWATER_COEFFICIENT * $pricePerUnit;

        $snapshot = [
            'sample_id' => $sample->id,
            'sample_reading_id' => $reading->id,
            'establishment_id' => $sample->establishment_id,
            'pollutant_id' => $reading->pollutant_id,
            'violation_id' => $violation->id,
            'violation_rule_id' => $rule->id,
            'detected_value' => $reading->detected_value,
            'tier_order_at_time' => $tierOrder,
            'price_per_unit_at_time' => $pricePerUnit,
            'evaluation_result' => 'violation',
        ];

        $item = [
            'violation_id' => $violation->id,
            'pollutant_id' => $reading->pollutant_id,
            'violation_rule_id' => $rule->id,
            'tier_order' => $tierOrder,
            'price_per_unit' => $pricePerUnit,
            'detected_value' => $reading->detected_value,
            'amount' => $amount,
        ];

        return [$amount, $snapshot, $item];
    }

    private function handleUnclassified(Sample $sample, SampleReading $reading): array
    {
        $snapshot = [
            'sample_id' => $sample->id,
            'sample_reading_id' => $reading->id,
            'establishment_id' => $sample->establishment_id,
            'pollutant_id' => $reading->pollutant_id,
            'violation_id' => null,
            'violation_rule_id' => null,
            'detected_value' => $reading->detected_value,
            'tier_order_at_time' => null,
            'price_per_unit_at_time' => null,
            'evaluation_result' => 'unclassified',
        ];

        $item = [
            'violation_id' => null,
            'pollutant_id' => $reading->pollutant_id,
            'violation_rule_id' => null,
            'tier_order' => null,
            'price_per_unit' => 0,
            'detected_value' => $reading->detected_value,
            'amount' => 0,
            'notes' => 'Reading does not match any configured limit or violation rule.',
        ];

        return [0, $snapshot, $item];
    }

    private function findActiveViolation(int $establishmentId, int $pollutantId): ?Violation
    {
        return Violation::query()
            ->where('establishment_id', $establishmentId)
            ->where('pollutant_id', $pollutantId)
            ->where('status', ViolationStatus::Active)
            ->with(['violationRule.tiers'])
            ->first();
    }
}
