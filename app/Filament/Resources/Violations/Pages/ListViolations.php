<?php

namespace App\Filament\Resources\Violations\Pages;

use App\Enums\ViolationStatus;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Establishment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListViolations extends ListRecords
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return Establishment::query()
            ->whereHas('violations')
            ->with([
                'violations.pollutant',
            ])
            ->withCount([
                'violations',
                'violations as active_violations_count' => fn (Builder $query): Builder => $query->where('status', ViolationStatus::Active),
            ]);
    }
}
