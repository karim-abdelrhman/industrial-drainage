<?php

namespace Database\Factories;

use App\Models\Pollutant;
use App\Models\ViolationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViolationRule>
 */
class ViolationRuleFactory extends Factory
{
    public function definition(): array
    {
        $from = $this->faker->randomFloat(2, 50, 200);
        $to = $this->faker->boolean(70) ? $from + $this->faker->randomFloat(2, 50, 200) : null;

        return [
            'pollutant_id' => Pollutant::factory(),
            'from' => $from,
            'to' => $to,
            'from_inclusive' => true,
            'to_inclusive' => false,
            'duration_days' => $this->faker->randomElement([30, 60, 90]),
        ];
    }
}
