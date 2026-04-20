<?php

namespace App\Filament\Resources\PollutantLimits\Tables;

use App\Enums\ActivityType;
use App\Enums\PollutantStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PollutantLimitsTable
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
                TextColumn::make('min_value')
                    ->label('الحد الأدنى')
                    ->numeric(),
                TextColumn::make('max_value')
                    ->label('الحد الأقصى')
                    ->numeric()
                    ->placeholder('مفتوح'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('effective_from')
                    ->label('من')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('effective_to')
                    ->label('إلى')
                    ->date('Y-m-d')
                    ->placeholder('ساري')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('pollutant_id')
                    ->label('الملوث')
                    ->relationship('pollutant', 'name'),
                SelectFilter::make('activity_type')
                    ->label('نوع النشاط')
                    ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(PollutantStatus::cases())->mapWithKeys(fn (PollutantStatus $case) => [$case->value => $case->getLabel()])),
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
