<?php

namespace Database\Factories;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use App\Models\Establishment;
use App\Models\Sample;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sample>
 */
class SampleFactory extends Factory
{
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'establishment_id' => Establishment::factory(),
            'sample_number' => 'SMP-'.strtoupper($this->faker->unique()->bothify('####??')),
            'sample_date' => $date,
            'water_usage' => $this->faker->randomFloat(2, 10, 500),
            'lab_report_image' => null,
            'collected_by' => $this->faker->name(),
            'status' => SampleStatus::Evaluated,
            'sample_type' => $this->faker->randomElement(SampleType::cases()),
            'notes' => null,
            'evaluated_at' => $date,
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => SampleStatus::Pending,
            'evaluated_at' => null,
        ]);
    }
}
