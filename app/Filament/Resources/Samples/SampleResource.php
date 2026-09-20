<?php

namespace App\Filament\Resources\Samples;

use App\Enums\NavigationGroup;
use App\Enums\SampleStatus;
use App\Filament\Resources\Samples\Pages\CreateSample;
use App\Filament\Resources\Samples\Pages\EditSample;
use App\Filament\Resources\Samples\Pages\ListSamples;
use App\Filament\Resources\Samples\Pages\ViewSample;
use App\Filament\Resources\Samples\RelationManagers\ReadingsRelationManager;
use App\Filament\Resources\Samples\Schemas\SampleForm;
use App\Filament\Resources\Samples\Schemas\SampleInfolist;
use App\Filament\Resources\Samples\Tables\SamplesTable;
use App\Models\Sample;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $recordTitleAttribute = 'sample_number';

    protected static ?string $modelLabel = 'عينة';

    protected static ?string $pluralModelLabel = 'العينات';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Laboratory;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SampleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SampleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SamplesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReadingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSamples::route('/'),
            'create' => CreateSample::route('/create'),
            'view' => ViewSample::route('/{record}'),
            'edit' => EditSample::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['establishment', 'readings.pollutant', 'violationSnapshots.pollutant']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Sample::query()->where('status', SampleStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'عينات بانتظار التقييم';
    }
}
