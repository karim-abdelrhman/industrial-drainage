<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم المطالبة')
                    ->formatStateUsing(fn ($state): string => str_pad((string) $state, 6, '0', STR_PAD_LEFT))
                    ->sortable(),
                TextColumn::make('establishment.name')
                    ->label('المنشأة')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('sample.sample_number')
                    ->label('رقم العينة')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('billing_month')
                    ->label('شهر الفوترة')
                    ->date('Y-m')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('إجمالي المطالبة')
                    ->formatStateUsing(fn ($state): string => Money::format($state))
                    ->alignEnd()
                    ->weight('semibold')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('issued_at')
                    ->label('تاريخ الإصدار')
                    ->date('Y-m-d')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('billing_month', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('establishment_id')
                    ->label('المنشأة')
                    ->relationship('establishment', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('حالة المطالبة')
                    ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn (InvoiceStatus $case) => [$case->value => $case->getLabel()])),
                Filter::make('billing_month')
                    ->label('التاريخ')
                    ->form([
                        DatePicker::make('from')->label('من'),
                        DatePicker::make('until')->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('billing_month', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('billing_month', '<=', $date));
                    }),
            ])
            ->recordUrl(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد مطالبات')
            ->emptyStateDescription('تظهر المطالبات هنا بعد تقييم العينات أو إنشائها يدويًا.');
    }
}
