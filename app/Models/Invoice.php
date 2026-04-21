<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[
    Fillable([
        'establishment_id',
        'sample_id',
        'billing_month',
        'status',
        'total_amount',
        'issued_at',
        'due_date',
        'notes'
    ])
]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'billing_month' => 'date',
            'issued_at' => 'datetime',
            'due_date' => 'date',
            'total_amount' => 'decimal:4',
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function scopeDraft(Builder $query): void
    {
        $query->where('status', InvoiceStatus::Draft);
    }

    public function scopeIssued(Builder $query): void
    {
        $query->where('status', InvoiceStatus::Issued);
    }
}
