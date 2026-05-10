<?php

namespace Database\Factories;

use App\Models\Pollutant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pollutant>
 */
class PollutantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->words(2, true),
            'unit' => $this->faker->randomElement(['mg/L', 'NTU', 'pH', 'CFU/100mL']),
            'is_active' => true,
        ];
    }
}
