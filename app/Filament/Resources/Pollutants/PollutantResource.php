<?php

namespace App\Filament\Resources\Pollutants;

use App\Filament\Resources\Pollutants\Pages\CreatePollutant;
use App\Filament\Resources\Pollutants\Pages\EditPollutant;
use App\Filament\Resources\Pollutants\Pages\ListPollutants;
use App\Filament\Resources\Pollutants\Schemas\PollutantForm;
use App\Filament\Resources\Pollutants\Tables\PollutantsTable;
use App\Models\Pollutant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PollutantResource extends Resource
{
    protected static ?string $model = Pollutant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $modelLabel = 'ملوث';

    protected static ?string $pluralModelLabel = 'الملوثات';

    protected static string|\UnitEnum|null $navigationGroup = 'الصرف الصناعي';

    public static function form(Schema $schema): Schema
    {
        return PollutantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PollutantsTable::configure($table);
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
            'index' => ListPollutants::route('/'),
            'create' => CreatePollutant::route('/create'),
            'edit' => EditPollutant::route('/{record}/edit'),
        ];
    }
}
