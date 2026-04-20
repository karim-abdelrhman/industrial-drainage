<?php

namespace App\Models;

use App\Enums\ActivityType;
use Database\Factories\EstablishmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'activity_type', 'address', 'contact_person', 'phone', 'email', 'is_active'])]
class Establishment extends Model
{
    /** @use HasFactory<EstablishmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'activity_type' => ActivityType::class,
            'is_active' => 'boolean',
        ];
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
