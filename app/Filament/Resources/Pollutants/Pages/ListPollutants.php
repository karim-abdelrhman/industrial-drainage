<?php

namespace App\Filament\Resources\Pollutants\Pages;

use App\Filament\Resources\Pollutants\PollutantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPollutants extends ListRecords
{
    protected static string $resource = PollutantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
