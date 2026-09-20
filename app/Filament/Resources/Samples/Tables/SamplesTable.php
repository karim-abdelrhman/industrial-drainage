<?php

namespace App\Filament\Resources\Samples\Tables;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Samples\SampleResource;
use App\Models\Invoice;
use App\Models\Sample;
use App\Services\SampleEvaluationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SamplesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sample_number')
                    ->label('رقم العينة')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('establishment.name')
                    ->label('المنشأة')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('sample_date')
                    ->label('تاريخ العينة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('water_usage')
                    ->label('الاستهلاك (م³)')
                    ->numeric(4)
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('sample_type')
                    ->label('النوع')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('collected_by')
                    ->label('جُمعت بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('readings_count')
                    ->label('القراءات')
                    ->counts('readings')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->defaultSort('sample_date', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('establishment_id')
                    ->label('المنشأة')
                    ->relationship('establishment', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(SampleStatus::cases())->mapWithKeys(fn (SampleStatus $case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('sample_type')
                    ->label('نوع العينة')
                    ->options(collect(SampleType::cases())->mapWithKeys(fn (SampleType $case) => [$case->value => $case->getLabel()])),
                Filter::make('sample_date')
                    ->label('التاريخ')
                    ->form([
                        DatePicker::make('from')->label('من'),
                        DatePicker::make('until')->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('sample_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('sample_date', '<=', $date));
                    }),
            ])
            ->recordUrl(fn (Sample $record): string => SampleResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
                Action::make('evaluate')
                    ->label('تقييم')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('success')
                    ->visible(fn (Sample $record): bool => $record->status === SampleStatus::Pending)
                    ->requiresConfirmation()
                    ->modalHeading('تقييم العينة')
                    ->modalDescription('سيتم تقييم جميع قراءات العينة وإنشاء الفاتورة تلقائيًا. لا يمكن التراجع عن هذا الإجراء.')
                    ->modalSubmitActionLabel('تأكيد التقييم')
                    ->action(function (Sample $record): void {
                        $invoice = app(SampleEvaluationService::class)->evaluate($record);

                        Notification::make()
                            ->title('تم تقييم العينة بنجاح')
                            ->body('تم إنشاء الفاتورة بحالة مسودة.')
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view_invoice')
                                    ->label('عرض المطالبة')
                                    ->url(InvoiceResource::getUrl('view', ['record' => $invoice->id])),
                            ])
                            ->send();
                    }),
                Action::make('view_invoice')
                    ->label('المطالبة')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('gray')
                    ->visible(fn (Sample $record): bool => $record->status === SampleStatus::Evaluated)
                    ->url(function (Sample $record): string {
                        $invoice = Invoice::query()->where('sample_id', $record->id)->first();

                        return $invoice
                            ? InvoiceResource::getUrl('view', ['record' => $invoice->id])
                            : SampleResource::getUrl('view', ['record' => $record]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد عينات حتى الآن')
            ->emptyStateDescription('سجّل عينة معملية لبدء الرصد والتقييم.');
    }
}
