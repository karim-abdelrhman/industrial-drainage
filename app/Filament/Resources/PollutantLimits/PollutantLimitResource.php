<?php

namespace App\Filament\Resources\PollutantLimits;

use App\Filament\Resources\PollutantLimits\Pages\CreatePollutantLimit;
use App\Filament\Resources\PollutantLimits\Pages\EditPollutantLimit;
use App\Filament\Resources\PollutantLimits\Pages\ListPollutantLimits;
use App\Filament\Resources\PollutantLimits\Schemas\PollutantLimitForm;
use App\Filament\Resources\PollutantLimits\Tables\PollutantLimitsTable;
use App\Models\PollutantLimit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PollutantLimitResource extends Resource
{
    protected static ?string $model = PollutantLimit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $modelLabel = 'حد تلوث';

    protected static ?string $pluralModelLabel = 'حدود التلوث';

    protected static string|\UnitEnum|null $navigationGroup = 'الصرف الصناعي';

    public static function form(Schema $schema): Schema
    {
        return PollutantLimitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PollutantLimitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPollutantLimits::route('/'),
            'create' => CreatePollutantLimit::route('/create'),
            'edit' => EditPollutantLimit::route('/{record}/edit'),
        ];
    }
}
