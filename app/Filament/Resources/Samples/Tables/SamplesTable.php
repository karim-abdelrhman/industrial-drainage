<?php

namespace App\Filament\Resources\Samples\Tables;

use App\Enums\SampleStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SamplesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sample_number')
                    ->label('رقم العينة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('establishment.name')
                    ->label('المنشأة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sample_date')
                    ->label('تاريخ العينة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('collected_by')
                    ->label('جُمعت بواسطة'),
                TextColumn::make('readings_count')
                    ->label('القراءات')
                    ->counts('readings')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->defaultSort('sample_date', 'desc')
            ->filters([
                SelectFilter::make('establishment_id')
                    ->label('المنشأة')
                    ->relationship('establishment', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(SampleStatus::cases())->mapWithKeys(fn (SampleStatus $c) => [$c->value => $c->getLabel()])),
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
