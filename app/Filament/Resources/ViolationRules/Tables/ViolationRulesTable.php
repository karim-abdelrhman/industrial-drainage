<?php

namespace App\Filament\Resources\ViolationRules\Tables;

use App\Enums\ActivityType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ViolationRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pollutant.name')
                    ->label('الملوث')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity_type')
                    ->label('نوع النشاط')
                    ->badge(),
                TextColumn::make('from')
                    ->label('من')
                    ->numeric(),
                TextColumn::make('to')
                    ->label('إلى')
                    ->numeric()
                    ->placeholder('مفتوح'),
                TextColumn::make('tiers_count')
                    ->label('عدد المراحل')
                    ->counts('tiers')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                SelectFilter::make('activity_type')
                    ->label('نوع النشاط')
                    ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()])),
                SelectFilter::make('pollutant_id')
                    ->label('الملوث')
                    ->relationship('pollutant', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
