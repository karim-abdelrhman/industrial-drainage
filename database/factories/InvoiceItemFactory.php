<?php

namespace Database\Factories;

use App\Enums\InvoiceItemType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $detectedValue = $this->faker->randomFloat(4, 10, 200);
        $pricePerUnit = $this->faker->randomFloat(2, 5, 80);

        return [
            'invoice_id' => Invoice::factory(),
            'item_type' => InvoiceItemType::PollutantCharge,
            'violation_id' => null,
            'pollutant_id' => null,
            'violation_rule_id' => null,
            'tier_order' => null,
            'price_per_unit' => $pricePerUnit,
            'detected_value' => $detectedValue,
            'amount' => round($detectedValue * $pricePerUnit, 2),
            'notes' => null,
        ];
    }
}
