<?php

namespace App\Filament\Resources\PollutantLimits\Pages;

use App\Filament\Resources\PollutantLimits\PollutantLimitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPollutantLimits extends ListRecords
{
    protected static string $resource = PollutantLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
