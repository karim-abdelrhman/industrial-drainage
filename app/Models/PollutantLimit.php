<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\PollutantStatus;
use Database\Factories\PollutantLimitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pollutant_id', 'activity_type', 'min_value', 'max_value', 'price_per_unit', 'status', 'sort_order', 'effective_from', 'effective_to', 'notes'])]
class PollutantLimit extends Model
{
    /** @use HasFactory<PollutantLimitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'activity_type' => ActivityType::class,
            'status' => PollutantStatus::class,
            'min_value' => 'decimal:4',
            'max_value' => 'decimal:4',
            'price_per_unit' => 'decimal:4',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function pollutant(): BelongsTo
    {
        return $this->belongsTo(Pollutant::class);
    }
}
