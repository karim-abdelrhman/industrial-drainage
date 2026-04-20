<?php

namespace App\Filament\Resources\Pollutants\Pages;

use App\Filament\Resources\Pollutants\PollutantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPollutant extends EditRecord
{
    protected static string $resource = PollutantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
