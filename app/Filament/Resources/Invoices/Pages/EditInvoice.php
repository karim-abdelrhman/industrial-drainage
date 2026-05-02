<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Services\BillingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('issue')
                ->label('إصدار الفاتورة')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('info')
                ->visible(fn (): bool => $this->getRecord()->status === InvoiceStatus::Draft)
                ->requiresConfirmation()
                ->modalHeading('إصدار الفاتورة')
                ->modalDescription('سيتم إصدار الفاتورة وتحديد تاريخ الاستحقاق. هل تريد المتابعة؟')
                ->modalSubmitActionLabel('إصدار')
                ->action(function (): void {
                    app(BillingService::class)->issue($this->getRecord());
                    $this->refreshFormData(['status', 'issued_at', 'due_date']);
                    Notification::make()
                        ->title('تم إصدار الفاتورة بنجاح')
                        ->success()
                        ->send();
                }),

            Action::make('mark_paid')
                ->label('تحديد كمدفوعة')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->visible(fn (): bool => in_array(
                    $this->getRecord()->status,
                    [InvoiceStatus::Issued, InvoiceStatus::Overdue]
                ))
                ->requiresConfirmation()
                ->modalHeading('تأكيد الدفع')
                ->modalDescription('هل تريد تحديد هذه الفاتورة كمدفوعة؟')
                ->modalSubmitActionLabel('تأكيد')
                ->action(function (): void {
                    app(BillingService::class)->markPaid($this->getRecord());
                    $this->refreshFormData(['status']);
                    Notification::make()
                        ->title('تم تسجيل الدفع بنجاح')
                        ->success()
                        ->send();
                }),

            DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->status === InvoiceStatus::Draft),
        ];
    }
}
