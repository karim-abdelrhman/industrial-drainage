<?php

namespace App\Filament\Resources\Samples\Pages;

use App\Enums\SampleStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Samples\SampleResource;
use App\Models\Invoice;
use App\Services\SampleCalculationService;
use App\Services\SampleEvaluationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class ViewSample extends ViewRecord
{
    protected static string $resource = SampleResource::class;

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview_calculation')
                ->label('معاينة الحساب')
                ->icon(Heroicon::OutlinedCalculator)
                ->color('gray')
                ->modalHeading('معاينة تفاصيل الحساب')
                ->modalContent(function (): HtmlString {
                    $record = $this->getRecord();

                    if (! $record->water_usage || $record->readings()->count() === 0) {
                        return new HtmlString(
                            '<p class="text-center text-gray-500 py-6">يجب إدخال الاستخدام المائي وإضافة قراءات الملوثات أولًا.</p>'
                        );
                    }

                    $result = app(SampleCalculationService::class)->calculateSample($record);

                    return new HtmlString(EditSample::buildBreakdownHtml($result));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('إغلاق'),
            Action::make('evaluate')
                ->label('تقييم العينة')
                ->icon(Heroicon::OutlinedPlay)
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status === SampleStatus::Pending)
                ->requiresConfirmation()
                ->modalHeading('تقييم العينة')
                ->modalDescription('سيتم تقييم جميع قراءات العينة وإنشاء الفاتورة تلقائيًا. لا يمكن التراجع عن هذا الإجراء.')
                ->modalSubmitActionLabel('تأكيد التقييم')
                ->action(function (): void {
                    $invoice = app(SampleEvaluationService::class)->evaluate($this->getRecord());

                    Notification::make()
                        ->title('تم تقييم العينة وإنشاء الفاتورة')
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('view', ['record' => $invoice->id]));
                }),
            Action::make('view_invoice')
                ->label('عرض المطالبة')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary')
                ->visible(fn (): bool => $this->getRecord()->status === SampleStatus::Evaluated)
                ->url(function (): ?string {
                    $invoice = Invoice::query()->where('sample_id', $this->getRecord()->id)->first();

                    return $invoice ? InvoiceResource::getUrl('view', ['record' => $invoice->id]) : null;
                }),
            EditAction::make(),
        ];
    }
}
