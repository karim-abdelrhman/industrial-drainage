<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\ViolationStatus;
use App\Models\Violation;
use App\Models\ViolationRule;
use App\Models\ViolationRuleTier;
use App\Models\ViolationTierStateLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ViolationService
{
    /**
     * Find the matching violation rule for a pollutant, value, and activity type.
     */
    public function findRule(int $pollutantId, float $value, ActivityType $activityType): ?ViolationRule
    {
        return ViolationRule::query()
            ->where('pollutant_id', $pollutantId)
            ->where('activity_type', $activityType)
            ->where('min_value', '<=', $value)
            ->where(function ($query) use ($value) {
                $query->whereNull('max_value')
                    ->orWhere('max_value', '>=', $value);
            })
            ->first();
    }

    /**
     * Create a new violation starting at tier 1.
     *
     * @param  array{establishment_id: int, pollutant_id: int, detected_value: float, violation_rule_id: int, start_date: Carbon, last_sample_id?: int}  $data
     */
    public function createViolation(array $data): Violation
    {
        return DB::transaction(function () use ($data) {
            return Violation::create([
                'establishment_id' => $data['establishment_id'],
                'pollutant_id' => $data['pollutant_id'],
                'violation_rule_id' => $data['violation_rule_id'],
                'detected_value' => $data['detected_value'],
                'start_date' => $data['start_date'],
                'current_tier' => 1,
                'current_tier_start_date' => $data['start_date'],
                'status' => ViolationStatus::Active,
                'last_sample_id' => $data['last_sample_id'] ?? null,
                'last_evaluated_at' => now(),
            ]);
        });
    }

    /**
     * Advance a violation to the next tier if the current tier's duration has elapsed.
     * Returns true if the tier was advanced, logs the transition.
     */
    public function advanceTier(Violation $violation): bool
    {
        if ($violation->status !== ViolationStatus::Active) {
            return false;
        }

        $currentTierModel = $this->getTierModel($violation);

        if ($currentTierModel === null) {
            return false;
        }

        $tierEndDate = $violation->current_tier_start_date->copy()->addMonths($currentTierModel->duration_months);

        if (now()->lt($tierEndDate)) {
            return false;
        }

        $nextTier = $violation->violationRule->tiers
            ->firstWhere('tier_order', $violation->current_tier + 1);

        if ($nextTier === null) {
            return false;
        }

        DB::transaction(function () use ($violation, $nextTier, $tierEndDate) {
            ViolationTierStateLog::create([
                'violation_id' => $violation->id,
                'previous_tier' => $violation->current_tier,
                'new_tier' => $nextTier->tier_order,
                'changed_at' => $tierEndDate,
            ]);

            $violation->update([
                'current_tier' => $nextTier->tier_order,
                'current_tier_start_date' => $tierEndDate,
            ]);
        });

        return true;
    }

    /**
     * Get the price_per_unit for the violation's current tier.
     */
    public function getCurrentTierPrice(Violation $violation): float
    {
        return (float) ($this->getTierModel($violation)?->price_per_unit ?? 0);
    }

    /**
     * Resolve an active violation.
     */
    public function resolve(Violation $violation): void
    {
        $violation->update(['status' => ViolationStatus::Resolved]);
    }

    private function getTierModel(Violation $violation): ?ViolationRuleTier
    {
        return $violation->violationRule->tiers
            ->firstWhere('tier_order', $violation->current_tier);
    }
}
