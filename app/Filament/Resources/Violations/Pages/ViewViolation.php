<?php

namespace App\Filament\Resources\Violations\Pages;

use App\Filament\Resources\Violations\ViolationResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewViolation extends ViewRecord
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('establishment_violations')
                ->label('كل مخالفات المنشأة')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('gray')
                ->url(fn (): string => ViolationResource::getUrl('establishment', [
                    'record' => $this->getRecord()->establishment_id,
                ])),
            EditAction::make(),
        ];
    }
}
