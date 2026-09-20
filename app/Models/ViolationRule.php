<?php

namespace App\Models;

use App\Support\NumericInterval;
use Database\Factories\ViolationRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pollutant_id', 'from', 'to', 'from_inclusive', 'to_inclusive', 'duration_days'])]
class ViolationRule extends Model
{
    /** @use HasFactory<ViolationRuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'from' => 'decimal:4',
            'to' => 'decimal:4',
            'from_inclusive' => 'boolean',
            'to_inclusive' => 'boolean',
            'duration_days' => 'integer',
        ];
    }

    public function pollutant(): BelongsTo
    {
        return $this->belongsTo(Pollutant::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(ViolationRuleTier::class)->orderBy('tier_order');
    }

    public function interval(): NumericInterval
    {
        return new NumericInterval(
            (float) $this->from,
            (bool) $this->from_inclusive,
            $this->to === null ? null : (float) $this->to,
            (bool) $this->to_inclusive,
        );
    }
}
