<?php

namespace App\Filament\Resources\Violations\Pages;

use App\Filament\Resources\Violations\ViolationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewViolation extends ViewRecord
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
