<?php

namespace Database\Factories;

use App\Models\ViolationRule;
use App\Models\ViolationRuleTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViolationRuleTier>
 */
class ViolationRuleTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'violation_rule_id' => ViolationRule::factory(),
            'tier_order' => 1,
            'price_per_unit' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
