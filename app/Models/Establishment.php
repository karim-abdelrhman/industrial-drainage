<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\CustomerZone;
use App\Enums\LocationType;
use Database\Factories\EstablishmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'activity_type', 'location_type', 'customer_zone', 'address', 'contact_person', 'phone', 'email', 'is_active'])]
class Establishment extends Model
{
    /** @use HasFactory<EstablishmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'activity_type' => ActivityType::class,
            'location_type' => LocationType::class,
            'customer_zone' => CustomerZone::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Establishment $establishment): void {
            $establishment->purgeOperationalRecords();
        });
    }

    /**
     * Remove samples, violations, and invoices so the establishment can be deleted
     * despite restrictOnDelete foreign keys.
     */
    public function purgeOperationalRecords(): void
    {
        DB::transaction(function (): void {
            Invoice::query()->where('establishment_id', $this->id)->delete();

            SampleViolationSnapshot::query()
                ->where('establishment_id', $this->id)
                ->delete();

            Violation::query()
                ->where('establishment_id', $this->id)
                ->update(['last_sample_id' => null]);

            Sample::query()->where('establishment_id', $this->id)->delete();
            Violation::query()->where('establishment_id', $this->id)->delete();
        });
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
