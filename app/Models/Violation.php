<?php

namespace App\Models;

use App\Enums\ViolationStatus;
use Database\Factories\ViolationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'establishment_id',
    'pollutant_id',
    'violation_rule_id',
    'detected_value',
    'start_date',
    'current_tier',
    'current_tier_start_date',
    'status',
    'last_sample_id',
    'last_evaluated_at',
])]
class Violation extends Model
{
    /** @use HasFactory<ViolationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ViolationStatus::class,
            'detected_value' => 'decimal:4',
            'start_date' => 'date',
            'current_tier_start_date' => 'date',
        ];
    }

    public function pollutant(): BelongsTo
    {
        return $this->belongsTo(Pollutant::class);
    }

    public function violationRule(): BelongsTo
    {
        return $this->belongsTo(ViolationRule::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', ViolationStatus::Active);
    }

    public function scopeResolved(Builder $query): void
    {
        $query->where('status', ViolationStatus::Resolved);
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function lastSample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'last_sample_id');
    }

    public function tierStateLogs(): HasMany
    {
        return $this->hasMany(ViolationTierStateLog::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
