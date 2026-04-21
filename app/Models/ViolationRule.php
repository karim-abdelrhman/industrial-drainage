<?php

namespace App\Models;

use App\Enums\ActivityType;
use Database\Factories\ViolationRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pollutant_id', 'activity_type', 'from', 'to'])]
class ViolationRule extends Model
{
    /** @use HasFactory<ViolationRuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'activity_type' => ActivityType::class,
            'from' => 'decimal:4',
            'to' => 'decimal:4',
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
}
