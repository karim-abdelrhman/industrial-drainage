<?php

namespace App\Models;

use Database\Factories\SampleViolationSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sample_id',
    'sample_reading_id',
    'establishment_id',
    'pollutant_id',
    'violation_id',
    'violation_rule_id',
    'detected_value',
    'tier_order_at_time',
    'price_per_unit_at_time',
    'evaluation_result',
])]
class SampleViolationSnapshot extends Model
{
    /** @use HasFactory<SampleViolationSnapshotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'detected_value' => 'decimal:4',
            'price_per_unit_at_time' => 'decimal:4',
        ];
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function sampleReading(): BelongsTo
    {
        return $this->belongsTo(SampleReading::class);
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function pollutant(): BelongsTo
    {
        return $this->belongsTo(Pollutant::class);
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function violationRule(): BelongsTo
    {
        return $this->belongsTo(ViolationRule::class);
    }
}
