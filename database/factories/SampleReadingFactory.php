<?php

namespace Database\Factories;

use App\Models\Pollutant;
use App\Models\Sample;
use App\Models\SampleReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SampleReading>
 */
class SampleReadingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sample_id' => Sample::factory(),
            'pollutant_id' => Pollutant::factory(),
            'detected_value' => $this->faker->randomFloat(4, 0, 300),
        ];
    }
}
