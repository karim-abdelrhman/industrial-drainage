<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('establishment.name')
                    ->label('المنشأة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('billing_month')
                    ->label('شهر الفوترة')
                    ->date('Y-m')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('issued_at')
                    ->label('تاريخ الإصدار')
                    ->dateTime('Y-m-d')
                    ->placeholder('—'),
                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('billing_month', 'desc')
            ->filters([
                SelectFilter::make('establishment_id')
                    ->label('المنشأة')
                    ->relationship('establishment', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn (InvoiceStatus $c) => [$c->value => $c->getLabel()])),
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
