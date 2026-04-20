<?php

namespace App\Filament\Resources\Violations\Tables;

use App\Enums\ViolationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ViolationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('establishment_id')
                    ->label('رقم المنشأة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pollutant.name')
                    ->label('الملوث')
                    ->searchable(),
                TextColumn::make('violationRule.min_value')
                    ->label('نطاق القاعدة')
                    ->formatStateUsing(fn ($state, $record) => "{$record->violationRule?->min_value} – ".($record->violationRule?->max_value ?? '∞')),
                TextColumn::make('detected_value')
                    ->label('القيمة المرصودة')
                    ->numeric(),
                TextColumn::make('current_tier')
                    ->label('المرحلة')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(ViolationStatus::cases())->mapWithKeys(fn (ViolationStatus $c) => [$c->value => $c->getLabel()])),
                SelectFilter::make('pollutant_id')
                    ->label('الملوث')
                    ->relationship('pollutant', 'name'),
            ])
            ->defaultSort('start_date', 'desc')
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
