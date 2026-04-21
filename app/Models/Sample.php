<?php

namespace App\Models;

use App\Enums\SampleStatus;
use Database\Factories\SampleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['establishment_id', 'sample_number', 'sample_date', 'lab_report_image', 'collected_by', 'status', 'notes', 'evaluated_at'])]
class Sample extends Model
{
    /** @use HasFactory<SampleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SampleStatus::class,
            'sample_date' => 'date',
            'evaluated_at' => 'datetime',
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(SampleReading::class);
    }

    public function violationSnapshots(): HasMany
    {
        return $this->hasMany(SampleViolationSnapshot::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', SampleStatus::Pending);
    }

    public function scopeEvaluated(Builder $query): void
    {
        $query->where('status', SampleStatus::Evaluated);
    }
}
