<?php

namespace App\Models;

use Database\Factories\SampleReadingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sample_id', 'pollutant_id', 'detected_value'])]
class SampleReading extends Model
{
    /** @use HasFactory<SampleReadingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'detected_value' => 'decimal:4',
        ];
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function pollutant(): BelongsTo
    {
        return $this->belongsTo(Pollutant::class);
    }
}
