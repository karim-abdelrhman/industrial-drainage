<?php

namespace Tests\Feature;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use App\Filament\Resources\Samples\Pages\CreateSample;
use App\Filament\Resources\Samples\Pages\EditSample;
use App\Models\Admin;
use App\Models\Establishment;
use App\Models\Pollutant;
use App\Models\Sample;
use App\Models\SampleReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SampleFormReadingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_sample_saves_pollutant_readings(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $establishment = Establishment::factory()->create();
        $pollutant = Pollutant::factory()->create([
            'code' => 'BOD',
            'name' => 'الأكسجين الحيوي',
            'unit' => 'mg/L',
        ]);

        Livewire::test(CreateSample::class)
            ->fillForm([
                'establishment_id' => $establishment->id,
                'sample_date' => now()->toDateString(),
                'water_usage' => 120,
                'sample_type' => SampleType::Regular->value,
                'status' => SampleStatus::Pending->value,
                'readings' => [
                    [
                        'pollutant_id' => $pollutant->id,
                        'detected_value' => 250.5,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sample = Sample::query()->where('establishment_id', $establishment->id)->first();

        $this->assertNotNull($sample);
        $this->assertDatabaseHas('sample_readings', [
            'sample_id' => $sample->id,
            'pollutant_id' => $pollutant->id,
            'detected_value' => 250.5,
        ]);
    }

    public function test_editing_a_sample_updates_pollutant_readings(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $sample = Sample::factory()->pending()->create();
        $pollutant = Pollutant::factory()->create();
        $reading = SampleReading::factory()->create([
            'sample_id' => $sample->id,
            'pollutant_id' => $pollutant->id,
            'detected_value' => 10,
        ]);

        $livewire = Livewire::test(EditSample::class, ['record' => $sample->getKey()]);
        $readings = $livewire->get('data.readings');
        $itemKey = array_key_first($readings);
        $readings[$itemKey]['detected_value'] = 88.25;

        $livewire
            ->fillForm(['readings' => $readings])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sample_readings', [
            'id' => $reading->id,
            'detected_value' => 88.25,
        ]);
    }
}
