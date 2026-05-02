<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

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
                TextColumn::make('pollutant.name')
                    ->label('الملوث')
                    ->weight('bold'),
                TextColumn::make('pollutant.unit')
                    ->label('الوحدة'),
                TextColumn::make('detected_value')
                    ->label('القيمة المرصودة')
                    ->numeric(4),
                TextColumn::make('tier_order')
                    ->label('المرحلة')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'المرحلة '.$state : 'مطابق')
                    ->badge()
                    ->color(fn ($state) => $state !== null ? 'danger' : 'success'),
                TextColumn::make('price_per_unit')
                    ->label('سعر الوحدة (ج.م)')
                    ->money('EGP'),
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
