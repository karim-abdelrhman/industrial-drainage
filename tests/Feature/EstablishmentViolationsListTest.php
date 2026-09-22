<?php

namespace Tests\Feature;

use App\Filament\Resources\Violations\Pages\ListViolations;
use App\Filament\Resources\Violations\Pages\ViewEstablishmentViolations;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Admin;
use App\Models\Establishment;
use App\Models\Pollutant;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EstablishmentViolationsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_violations_index_lists_one_row_per_establishment(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $firstEstablishment = Establishment::factory()->create(['name' => 'مصنع النيل']);
        $secondEstablishment = Establishment::factory()->create(['name' => 'مصنع الشرق']);

        Violation::factory()->count(2)->create(['establishment_id' => $firstEstablishment->id]);
        Violation::factory()->create(['establishment_id' => $secondEstablishment->id]);

        Livewire::test(ListViolations::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$firstEstablishment, $secondEstablishment])
            ->assertCountTableRecords(2);
    }

    public function test_establishment_violations_page_shows_all_violations_together(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $establishment = Establishment::factory()->create(['name' => 'مصنع النيل']);
        $bod = Pollutant::factory()->create(['code' => 'BOD', 'name' => 'الأكسجين الحيوي']);
        $cod = Pollutant::factory()->create(['code' => 'COD', 'name' => 'الأكسجين الكيميائي']);

        Violation::factory()->create([
            'establishment_id' => $establishment->id,
            'pollutant_id' => $bod->id,
        ]);
        Violation::factory()->create([
            'establishment_id' => $establishment->id,
            'pollutant_id' => $cod->id,
        ]);

        $this->get(ViolationResource::getUrl('establishment', ['record' => $establishment]))
            ->assertOk();

        Livewire::test(ViewEstablishmentViolations::class, ['record' => $establishment->getKey()])
            ->assertOk()
            ->assertSee('مصنع النيل')
            ->assertSee('الأكسجين الحيوي')
            ->assertSee('الأكسجين الكيميائي');
    }
}
