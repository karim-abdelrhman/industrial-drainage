<?php

namespace App\Models;

use Database\Factories\PollutantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'unit', 'is_active'])]
class Pollutant extends Model
{
    /** @use HasFactory<PollutantFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PollutantLimit::class);
    }

    public function violationRules(): HasMany
    {
        return $this->hasMany(ViolationRule::class);
    }
}
