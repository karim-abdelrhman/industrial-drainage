<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

class BillingService
{
    /**
     * Issue a draft invoice (sets status, issued_at, and due_date).
     */
    public function issue(Invoice $invoice, int $dueDays = 30): void
    {
        abort_unless($invoice->status === InvoiceStatus::Draft, 422, 'يمكن إصدار الفواتير المسودة فقط.');

        $invoice->update([
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
            'due_date' => now()->addDays($dueDays)->toDateString(),
        ]);
    }

    /**
     * Mark an issued invoice as paid.
     */
    public function markPaid(Invoice $invoice): void
    {
        abort_unless($invoice->status === InvoiceStatus::Issued, 422, 'يمكن تحديد الفواتير الصادرة فقط كمدفوعة.');

        $invoice->update(['status' => InvoiceStatus::Paid]);
    }

    /**
     * Flag all issued invoices that have passed their due date as overdue.
     * Returns the number of invoices updated.
     */
    public function flagOverdue(): int
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Issued)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => InvoiceStatus::Overdue]);
    }
}
