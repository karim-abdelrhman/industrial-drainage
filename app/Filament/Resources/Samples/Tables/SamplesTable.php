<?php

namespace App\Filament\Resources\Samples\Tables;

use App\Enums\SampleStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\Sample;
use App\Services\SampleEvaluationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make('water_usage')
                    ->label('الاستخدام المائي (م³)')
                    ->numeric(4)
                    ->sortable(),
                TextColumn::make('collected_by')
                    ->label('جُمعت بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('evaluate')
                    ->label('تقييم')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('success')
                    ->visible(fn (Sample $record) => $record->status === SampleStatus::Pending)
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
                                    ->label('عرض الفاتورة')
                                    ->url(InvoiceResource::getUrl('edit', ['record' => $invoice->id])),
                            ])
                            ->send();
                    }),

                Action::make('view_invoice')
                    ->label('الفاتورة')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('info')
                    ->visible(fn (Sample $record) => $record->status === SampleStatus::Evaluated)
                    ->url(function (Sample $record): string {
                        $invoice = Invoice::where('sample_id', $record->id)->first();

                        return $invoice
                            ? InvoiceResource::getUrl('edit', ['record' => $invoice->id])
                            : '#';
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
