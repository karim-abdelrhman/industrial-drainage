<?php

namespace App\Filament\Resources\Establishments\Tables;

use App\Enums\ActivityType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EstablishmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المنشأة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('activity_type')
                    ->label('نوع النشاط')
                    ->badge(),
                TextColumn::make('contact_person')
                    ->label('المسؤول')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('الهاتف'),
                ToggleColumn::make('is_active')
                    ->label('نشط'),
            ])
            ->filters([
                SelectFilter::make('activity_type')
                    ->label('نوع النشاط')
                    ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()])),
                TernaryFilter::make('is_active')
                    ->label('نشط'),
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
