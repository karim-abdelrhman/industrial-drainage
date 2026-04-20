<?php

namespace App\Filament\Resources\ViolationRules\Pages;

use App\Filament\Resources\ViolationRules\ViolationRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditViolationRule extends EditRecord
{
    protected static string $resource = ViolationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
