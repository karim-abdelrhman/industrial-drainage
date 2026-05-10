<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Establishment;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $billingMonth = $this->faker->dateTimeBetween('-6 months', 'now');
        $billingMonthStart = Carbon::instance($billingMonth)->startOfMonth();
        $issuedAt = $billingMonthStart->copy()->addDays($this->faker->numberBetween(1, 5));
        $dueDate = $issuedAt->copy()->addDays(30);

        return [
            'establishment_id' => Establishment::factory(),
            'sample_id' => null,
            'billing_month' => $billingMonthStart,
            'status' => InvoiceStatus::Issued,
            'total_amount' => $this->faker->randomFloat(2, 500, 15000),
            'issued_at' => $issuedAt,
            'due_date' => $dueDate,
            'notes' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(['status' => InvoiceStatus::Paid]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status' => InvoiceStatus::Overdue,
            'due_date' => now()->subDays($this->faker->numberBetween(5, 60)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(['status' => InvoiceStatus::Draft]);
    }

    public function forMonth(Carbon $month): static
    {
        return $this->state(['billing_month' => $month->copy()->startOfMonth()]);
    }
}
