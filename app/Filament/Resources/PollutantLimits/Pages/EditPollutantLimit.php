<?php

namespace App\Filament\Resources\PollutantLimits\Pages;

use App\Filament\Resources\PollutantLimits\PollutantLimitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPollutantLimit extends EditRecord
{
    protected static string $resource = PollutantLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
