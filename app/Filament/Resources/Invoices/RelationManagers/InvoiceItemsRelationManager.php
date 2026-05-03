<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Enums\InvoiceItemType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود الفاتورة';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item_type')
                    ->label('نوع البند')
                    ->formatStateUsing(fn ($state) => $state instanceof InvoiceItemType ? $state->getLabel() : InvoiceItemType::from($state)->getLabel())
                    ->badge()
                    ->color(fn ($state): string => match (is_string($state) ? $state : $state->value) {
                        InvoiceItemType::PollutantCharge->value => 'info',
                        InvoiceItemType::CollectionFee->value, InvoiceItemType::AdminFee->value => 'gray',
                        InvoiceItemType::AnalysisFee->value, InvoiceItemType::IssuanceFee->value => 'warning',
                        InvoiceItemType::Vat->value => 'danger',
                        InvoiceItemType::Rounding->value => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('pollutant.name')
                    ->label('الملوث')
                    ->placeholder('—')
                    ->weight('bold'),
                TextColumn::make('pollutant.unit')
                    ->label('الوحدة')
                    ->placeholder('—'),
                TextColumn::make('detected_value')
                    ->label('القيمة المرصودة')
                    ->formatStateUsing(fn ($state, $record) => $record->item_type === InvoiceItemType::PollutantCharge ? number_format((float) $state, 4) : '—')
                    ->placeholder('—'),
                TextColumn::make('tier_order')
                    ->label('المرحلة')
                    ->formatStateUsing(fn ($state, $record) => match (true) {
                        $record->item_type !== InvoiceItemType::PollutantCharge => '—',
                        $state !== null => 'المرحلة '.$state,
                        default => 'مطابق',
                    })
                    ->badge()
                    ->color(fn ($state, $record): string => match (true) {
                        $record->item_type !== InvoiceItemType::PollutantCharge => 'gray',
                        $state !== null => 'danger',
                        default => 'success',
                    }),
                TextColumn::make('price_per_unit')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state, $record) => $record->item_type === InvoiceItemType::PollutantCharge ? number_format((float) $state, 2).' ج.م' : '—')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->label('المبلغ (ج.م)')
                    ->money('EGP')
                    ->weight('bold'),
                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->paginated(false);
    }
}
