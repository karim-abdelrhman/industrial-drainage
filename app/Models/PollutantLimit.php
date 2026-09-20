<?php

namespace App\Models;

use App\Enums\CustomerZone;
use App\Enums\PollutantStatus;
use App\Support\NumericInterval;
use Database\Factories\PollutantLimitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pollutant_id', 'customer_zone', 'min_value', 'max_value', 'min_inclusive', 'max_inclusive', 'price_per_unit', 'status', 'sort_order', 'effective_from', 'effective_to', 'notes'])]
class PollutantLimit extends Model
{
    /** @use HasFactory<PollutantLimitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'customer_zone' => CustomerZone::class,
            'status' => PollutantStatus::class,
            'min_value' => 'decimal:4',
            'max_value' => 'decimal:4',
            'min_inclusive' => 'boolean',
            'max_inclusive' => 'boolean',
            'price_per_unit' => 'decimal:4',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function pollutant(): BelongsTo
    {
        return $this->belongsTo(Pollutant::class);
    }

    public function interval(): NumericInterval
    {
        return new NumericInterval(
            (float) $this->min_value,
            (bool) $this->min_inclusive,
            $this->max_value === null ? null : (float) $this->max_value,
            (bool) $this->max_inclusive,
        );
    }
}
