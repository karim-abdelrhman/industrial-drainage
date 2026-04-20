<?php

namespace App\Filament\Resources\ViolationRules;

use App\Filament\Resources\ViolationRules\Pages\CreateViolationRule;
use App\Filament\Resources\ViolationRules\Pages\EditViolationRule;
use App\Filament\Resources\ViolationRules\Pages\ListViolationRules;
use App\Filament\Resources\ViolationRules\RelationManagers\TiersRelationManager;
use App\Filament\Resources\ViolationRules\Schemas\ViolationRuleForm;
use App\Filament\Resources\ViolationRules\Tables\ViolationRulesTable;
use App\Models\ViolationRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ViolationRuleResource extends Resource
{
    protected static ?string $model = ViolationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $modelLabel = 'قاعدة مخالفة';

    protected static ?string $pluralModelLabel = 'قواعد المخالفات';

    protected static string|\UnitEnum|null $navigationGroup = 'الصرف الصناعي';

    public static function form(Schema $schema): Schema
    {
        return ViolationRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ViolationRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TiersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListViolationRules::route('/'),
            'create' => CreateViolationRule::route('/create'),
            'edit' => EditViolationRule::route('/{record}/edit'),
        ];
    }
}
