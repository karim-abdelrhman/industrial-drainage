<?php

namespace App\Filament\Resources\ViolationRules\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';

    protected static ?string $title = 'مراحل المخالفة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('tier_order')
                            ->label('رقم المرحلة')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->unique(
                                table: 'violation_rule_tiers',
                                column: 'tier_order',
                                ignorable: fn ($record) => $record,
                                modifyRuleUsing: fn ($rule) => $rule->where('violation_rule_id', $this->getOwnerRecord()->id),
                            ),
                        TextInput::make('duration_months')
                            ->label('المدة (بالأشهر)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('price_per_unit')
                            ->label('السعر لكل وحدة (EGP)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tier_order')
            ->defaultSort('tier_order')
            ->columns([
                TextColumn::make('tier_order')
                    ->label('المرحلة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('duration_months')
                    ->label('المدة (أشهر)')
                    ->numeric(),
                TextColumn::make('price_per_unit')
                    ->label('السعر / وحدة')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
