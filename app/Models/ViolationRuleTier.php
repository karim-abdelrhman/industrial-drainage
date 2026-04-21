<?php

namespace App\Models;

use Database\Factories\ViolationRuleTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['violation_rule_id', 'tier_order', 'duration_days', 'price_per_unit'])]
class ViolationRuleTier extends Model
{
    /** @use HasFactory<ViolationRuleTierFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:4',
            'duration_days' => 'integer',
        ];
    }

    public function violationRule(): BelongsTo
    {
        return $this->belongsTo(ViolationRule::class);
    }
}
