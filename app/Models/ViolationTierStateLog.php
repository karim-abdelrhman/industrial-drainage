<?php

namespace App\Models;

use Database\Factories\ViolationTierStateLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['violation_id', 'previous_tier', 'new_tier', 'changed_at', 'reason'])]
class ViolationTierStateLog extends Model
{
    /** @use HasFactory<ViolationTierStateLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }
}
