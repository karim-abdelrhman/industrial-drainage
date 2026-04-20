<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\ViolationStatus;
use App\Models\Establishment;
use App\Models\Invoice;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate draft invoices for all establishments with active violations for the given month.
     * Already-issued invoices for the same period are skipped.
     *
     * @return Collection<int, Invoice>
     */
    public function generateMonthlyInvoices(Carbon $month): Collection
    {
        $billingMonth = $month->copy()->startOfMonth()->toDateString();

        $establishments = Establishment::query()
            ->whereHas('violations', fn ($q) => $q->where('status', ViolationStatus::Active))
            ->get();

        return $establishments->map(
            fn (Establishment $e) => $this->generateForEstablishment($e, $billingMonth)
        )->filter();
    }

    /**
     * Generate or regenerate a draft invoice for one establishment for a given billing month.
     * Returns null if there are no active violations.
     */
    public function generateForEstablishment(Establishment $establishment, string $billingMonth): ?Invoice
    {
        $violations = Violation::query()
            ->where('establishment_id', $establishment->id)
            ->where('status', ViolationStatus::Active)
            ->with(['violationRule.tiers', 'pollutant'])
            ->get();

        if ($violations->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($establishment, $billingMonth, $violations) {
            $invoice = Invoice::firstOrCreate(
                ['establishment_id' => $establishment->id, 'billing_month' => $billingMonth],
                ['status' => InvoiceStatus::Draft, 'total_amount' => 0]
            );

            if ($invoice->status !== InvoiceStatus::Draft) {
                return $invoice;
            }

            $invoice->items()->delete();

            $total = 0;

            foreach ($violations as $violation) {
                $tier = $violation->violationRule->tiers
                    ->firstWhere('tier_order', $violation->current_tier);

                if ($tier === null) {
                    continue;
                }

                $amount = (float) $tier->price_per_unit * (float) $violation->detected_value;
                $total += $amount;

                $invoice->items()->create([
                    'violation_id' => $violation->id,
                    'pollutant_id' => $violation->pollutant_id,
                    'violation_rule_id' => $violation->violation_rule_id,
                    'tier_order' => $violation->current_tier,
                    'price_per_unit' => $tier->price_per_unit,
                    'detected_value' => $violation->detected_value,
                    'amount' => $amount,
                ]);
            }

            $invoice->update(['total_amount' => $total]);

            return $invoice;
        });
    }

    /**
     * Issue a draft invoice (marks it as issued and sets issued_at + due_date).
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
     * Flag all overdue issued invoices (past due_date and still issued).
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
