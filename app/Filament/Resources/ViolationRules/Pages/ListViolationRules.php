<?php

namespace App\Filament\Resources\ViolationRules\Pages;

use App\Filament\Resources\ViolationRules\ViolationRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListViolationRules extends ListRecords
{
    protected static string $resource = ViolationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
