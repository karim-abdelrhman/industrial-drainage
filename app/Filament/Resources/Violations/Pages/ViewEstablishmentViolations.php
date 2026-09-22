<?php

namespace App\Filament\Resources\Violations\Pages;

use App\Filament\Resources\Violations\Schemas\EstablishmentViolationsInfolist;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Establishment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewEstablishmentViolations extends ViewRecord
{
    protected static string $resource = ViolationResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'مخالفات '.$this->getRecord()->name;
    }

    public function getRecordTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return EstablishmentViolationsInfolist::configure($schema);
    }

    protected function resolveRecord(int|string $key): Model
    {
        return Establishment::query()
            ->with([
                'violations' => function ($query): void {
                    $query
                        ->with(['pollutant', 'violationRule.tiers'])
                        ->orderByRaw("case when status = 'active' then 0 else 1 end")
                        ->orderByDesc('start_date');
                },
            ])
            ->findOrFail($key);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canViewAny(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(ViolationResource::getUrl('create')),
        ];
    }
}
