<?php

namespace App\Filament\Resources\Violations;

use App\Filament\Resources\Violations\Pages\CreateViolation;
use App\Filament\Resources\Violations\Pages\EditViolation;
use App\Filament\Resources\Violations\Pages\ListViolations;
use App\Filament\Resources\Violations\Schemas\ViolationForm;
use App\Filament\Resources\Violations\Tables\ViolationsTable;
use App\Models\Violation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ViolationResource extends Resource
{
    protected static ?string $model = Violation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $modelLabel = 'مخالفة';

    protected static ?string $pluralModelLabel = 'المخالفات';

    protected static string|\UnitEnum|null $navigationGroup = 'الصرف الصناعي';

    public static function form(Schema $schema): Schema
    {
        return ViolationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ViolationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListViolations::route('/'),
            'create' => CreateViolation::route('/create'),
            'edit' => EditViolation::route('/{record}/edit'),
        ];
    }
}
