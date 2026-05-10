<?php

namespace Database\Factories;

use App\Enums\ViolationStatus;
use App\Models\Establishment;
use App\Models\Pollutant;
use App\Models\Violation;
use App\Models\ViolationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Violation>
 */
class ViolationFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-6 months', '-7 days');

        return [
            'establishment_id' => Establishment::factory(),
            'pollutant_id' => Pollutant::factory(),
            'violation_rule_id' => ViolationRule::factory(),
            'detected_value' => $this->faker->randomFloat(4, 50, 300),
            'start_date' => $startDate,
            'current_tier' => 1,
            'current_tier_start_date' => $startDate,
            'status' => ViolationStatus::Active,
            'last_sample_id' => null,
            'last_evaluated_at' => now(),
        ];
    }

    public function resolved(): static
    {
        return $this->state(['status' => ViolationStatus::Resolved]);
    }

    public function atTier(int $tier): static
    {
        return $this->state(['current_tier' => $tier]);
    }
}
