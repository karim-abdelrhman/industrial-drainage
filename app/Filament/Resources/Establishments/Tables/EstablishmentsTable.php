<?php

namespace App\Filament\Resources\Establishments\Tables;

use App\Enums\ActivityType;
use App\Enums\CustomerZone;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('customer_zone')
                    ->label('منطقة العميل')
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
                SelectFilter::make('customer_zone')
                    ->label('منطقة العميل')
                    ->options(collect(CustomerZone::cases())->mapWithKeys(fn (CustomerZone $c) => [$c->value => $c->getLabel()])),
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
