<?php

namespace Tests\Feature; 

use App\Models\Establishment;
use App\Models\Sample;
use App\Support\SampleNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleNumberGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_numbers_are_generated_sequentially(): void
    {
        $establishment = Establishment::factory()->create();

        $first = Sample::factory()->pending()->create([
            'establishment_id' => $establishment->id,
        ]);
        $second = Sample::factory()->pending()->create([
            'establishment_id' => $establishment->id,
        ]);

        $this->assertSame('SMP-00001', $first->sample_number);
        $this->assertSame('SMP-00002', $second->sample_number);
    }

    public function test_explicit_sample_number_is_preserved(): void
    {
        $sample = Sample::factory()->create([
            'sample_number' => 'SMP-00420',
        ]);

        $this->assertSame('SMP-00420', $sample->sample_number);
        $this->assertSame('SMP-00421', SampleNumber::next());
    }
}
