<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\ViolationStatus;
use App\Models\PollutantLimit;
use App\Models\Violation;
use App\Models\ViolationRule;
use App\Models\ViolationTierStateLog;
use Carbon\Carbon;

class ViolationService
{
    /**
     * Find the matching compliant limit for a reading.
     * Checked before violation rules — boundary values resolve to compliant.
     */
    public function findLimit(int $pollutantId, float $value, ActivityType $activityType): ?PollutantLimit
    {
        return PollutantLimit::query()
            ->where('pollutant_id', $pollutantId)
            ->where('activity_type', $activityType->value)
            ->where('min_value', '<=', $value)
            ->where(function ($query) use ($value) {
                $query->whereNull('max_value')->orWhere('max_value', '>=', $value);
            })
            ->first();
    }

    /**
     * Find the matching violation rule for a reading (columns: from / to).
     * Only called when findLimit returns null.
     */
    public function findRule(int $pollutantId, float $value, ActivityType $activityType): ?ViolationRule
    {
        return ViolationRule::query()
            ->where('pollutant_id', $pollutantId)
            ->where('activity_type', $activityType->value)
            ->where('from', '<=', $value)
            ->where(function ($query) use ($value) {
                $query->whereNull('to')->orWhere('to', '>', $value);
            })
            ->with(['tiers' => fn ($q) => $q->orderBy('tier_order')])
            ->first();
    }

    /**
     * Compute which tier applies on a given evaluation date using elapsed days.
     * Tier 3 (or the last defined tier) is the ceiling — it never escalates beyond it.
     *
     * @param  Violation  $violation  Must have violationRule.tiers loaded.
     */
    public function computeTier(Violation $violation, Carbon $evaluationDate): int
    {
        $elapsedDays = $violation->start_date->diffInDays($evaluationDate);
        $durationDays = (int) $violation->violationRule->duration_days;
        $tiers = $violation->violationRule->tiers->sortBy('tier_order')->values();

        if ($tiers->isEmpty()) {
            return 1;
        }

        if ($durationDays <= 0) {
            return $tiers->first()->tier_order;
        }

        $tierIndex = min((int) floor($elapsedDays / $durationDays), $tiers->count() - 1);

        return $tiers->get($tierIndex)->tier_order;
    }

    /**
     * Create a new active violation starting at tier 1.
     *
     * @param  array{establishment_id: int, pollutant_id: int, violation_rule_id: int, detected_value: float, start_date: Carbon, last_sample_id?: int}  $data
     */
    public function createViolation(array $data): Violation
    {
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
    }

    /**
     * Resolve an active violation (reading returned to compliant range).
     */
    public function resolve(Violation $violation): void
    {
        $violation->update(['status' => ViolationStatus::Resolved]);
    }

    /**
     * Persist a tier change on the violation and write an audit log entry if the tier changed.
     */
    public function syncTier(Violation $violation, int $newTier, Carbon $evaluationDate): void
    {
        if ($violation->current_tier === $newTier) {
            return;
        }

        ViolationTierStateLog::create([
            'violation_id' => $violation->id,
            'previous_tier' => $violation->current_tier,
            'new_tier' => $newTier,
            'changed_at' => $evaluationDate,
        ]);

        $violation->update(['current_tier' => $newTier]);
    }
}
