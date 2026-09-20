<?php

namespace App\Filament\Resources\ViolationRules\Tables;

use App\Models\ViolationRule;
use App\Support\InclusiveBoundToggles;
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
                TextColumn::make('pollutant.code')
                    ->label('الملوث')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from')
                    ->label('من')
                    ->formatStateUsing(fn ($state, ViolationRule $record): string => InclusiveBoundToggles::formatLower($state, $record->from_inclusive)),
                TextColumn::make('to')
                    ->label('إلى')
                    ->formatStateUsing(fn ($state, ViolationRule $record): string => InclusiveBoundToggles::formatUpper($state, $record->to_inclusive)),
                TextColumn::make('tiers_count')
                    ->label('عدد المراحل')
                    ->counts('tiers')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
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
            ])
            ->emptyStateHeading('لا توجد قواعد مخالفة')
            ->emptyStateDescription('أضف نطاقات المخالفة ومستوياتها لكل ملوث.')
            ->striped();
    }
}
