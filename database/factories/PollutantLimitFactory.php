<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Enums\PollutantStatus;
use App\Models\Pollutant;
use App\Models\PollutantLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PollutantLimit>
 */
class PollutantLimitFactory extends Factory
{
    public function definition(): array
    {
        $min = $this->faker->randomFloat(2, 0, 50);
        $max = $min + $this->faker->randomFloat(2, 10, 100);

        return [
            'pollutant_id' => Pollutant::factory(),
            'activity_type' => $this->faker->randomElement(ActivityType::cases())->value,
            'min_value' => $min,
            'max_value' => $max,
            'price_per_unit' => $this->faker->randomFloat(2, 5, 50),
            'status' => PollutantStatus::Compliant->value,
            'notes' => null,
        ];
    }
}
