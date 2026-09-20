<?php

namespace App\Filament\Resources\Establishments\Tables;

use App\Enums\ActivityType;
use App\Enums\CustomerZone;
use App\Enums\LocationType;
use App\Filament\Resources\Establishments\EstablishmentResource;
use App\Models\Establishment;
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
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                TextColumn::make('activity_type')
                    ->label('نوع النشاط')
                    ->badge(),
                TextColumn::make('customer_zone')
                    ->label('منطقة التعريفة')
                    ->badge(),
                TextColumn::make('location_type')
                    ->label('الموقع')
                    ->toggleable(),
                TextColumn::make('contact_person')
                    ->label('المسؤول')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->placeholder('—'),
                ToggleColumn::make('is_active')
                    ->label('نشط'),
            ])
            ->defaultSort('name')
            ->striped()
            ->filters([
                SelectFilter::make('activity_type')
                    ->label('نوع النشاط')
                    ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('customer_zone')
                    ->label('منطقة العميل')
                    ->options(collect(CustomerZone::cases())->mapWithKeys(fn (CustomerZone $case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('location_type')
                    ->label('الموقع الجغرافي')
                    ->options(collect(LocationType::cases())->mapWithKeys(fn (LocationType $case) => [$case->value => $case->getLabel()])),
                TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->recordUrl(fn (Establishment $record): string => EstablishmentResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد منشآت حتى الآن')
            ->emptyStateDescription('أضف منشأة أو استورد ملف Excel للبدء.');
    }
}
