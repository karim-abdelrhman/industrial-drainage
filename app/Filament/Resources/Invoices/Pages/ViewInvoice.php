<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Services\BillingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function infolist(Schema $schema): Schema
    {
        $this->getRecord()->loadMissing(['establishment', 'sample', 'items.pollutant']);

        return parent::infolist($schema);
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('issue')
                ->label('إصدار الفاتورة')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('primary')
                ->visible(fn (): bool => $this->getRecord()->status === InvoiceStatus::Draft)
                ->requiresConfirmation()
                ->modalHeading('إصدار الفاتورة')
                ->modalDescription('سيتم إصدار الفاتورة وتحديد تاريخ الاستحقاق. هل تريد المتابعة؟')
                ->modalSubmitActionLabel('إصدار')
                ->action(function (): void {
                    app(BillingService::class)->issue($this->getRecord());
                    Notification::make()->title('تم إصدار الفاتورة بنجاح')->success()->send();
                    $this->redirect(InvoiceResource::getUrl('view', ['record' => $this->getRecord()]));
                }),
            Action::make('mark_paid')
                ->label('تحديد كمدفوعة')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->visible(fn (): bool => in_array(
                    $this->getRecord()->status,
                    [InvoiceStatus::Issued, InvoiceStatus::Overdue],
                    true
                ))
                ->requiresConfirmation()
                ->modalHeading('تأكيد الدفع')
                ->modalDescription('هل تريد تحديد هذه الفاتورة كمدفوعة؟')
                ->modalSubmitActionLabel('تأكيد')
                ->action(function (): void {
                    app(BillingService::class)->markPaid($this->getRecord());
                    Notification::make()->title('تم تسجيل الدفع بنجاح')->success()->send();
                    $this->redirect(InvoiceResource::getUrl('view', ['record' => $this->getRecord()]));
                }),
            Action::make('print_invoice')
                ->label('طباعة المطالبة')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->url(fn (): string => route('invoices.print', $this->getRecord()))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
