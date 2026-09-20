<?php

namespace Tests\Feature;

use App\Enums\CustomerZone;
use App\Enums\SampleStatus;
use App\Enums\ViolationStatus;
use App\Models\Establishment;
use App\Models\Pollutant;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\Violation;
use App\Services\SampleCalculationService;
use App\Services\ViolationService;
use Carbon\Carbon;
use Database\Seeders\PollutantTariffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollutantTariffCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PollutantTariffSeeder::class);
    }

    public function test_city_bod_boundaries_and_pricing(): void
    {
        $this->assertPreview('BOD', CustomerZone::City, 244, 'unclassified', 0, 0);
        $this->assertPreview('BOD', CustomerZone::City, 244.01, 'compliant', 1, 80);
        $this->assertPreview('BOD', CustomerZone::City, 600, 'compliant', 1, 80);
        $this->assertPreview('BOD', CustomerZone::City, 600.01, 'violation', 3, 240);
        $this->assertPreview('BOD', CustomerZone::City, 659.99, 'violation', 3, 240);
        $this->assertPreview('BOD', CustomerZone::City, 660, 'violation', 9, 720);
        $this->assertPreview('BOD', CustomerZone::City, 2000, 'violation', 18, 1440);
    }

    public function test_industrial_bod_uses_third_band_after_threshold(): void
    {
        $this->assertPreview('BOD', CustomerZone::IndustrialZone, 1000, 'compliant', 1, 80);
        $this->assertPreview('BOD', CustomerZone::IndustrialZone, 2675.99, 'compliant', 1, 80);
        $this->assertPreview('BOD', CustomerZone::IndustrialZone, 2676, 'violation', 18, 1440);
    }

    public function test_city_and_industrial_tss_bands(): void
    {
        $this->assertPreview('TSS', CustomerZone::City, 500, 'unclassified', 0, 0);
        $this->assertPreview('TSS', CustomerZone::City, 800, 'compliant', 0.5, 40);
        $this->assertPreview('TSS', CustomerZone::City, 800.01, 'violation', 2, 160);
        $this->assertPreview('TSS', CustomerZone::IndustrialZone, 1500, 'compliant', 0.5, 40);
        $this->assertPreview('TSS', CustomerZone::IndustrialZone, 1964, 'compliant', 0.5, 40);
        $this->assertPreview('TSS', CustomerZone::IndustrialZone, 1965, 'violation', 5, 400);
        $this->assertPreview('TSS', CustomerZone::IndustrialZone, 3000, 'violation', 15, 1200);
    }

    public function test_cod_industrial_zero_charge_until_threshold(): void
    {
        $this->assertPreview('COD', CustomerZone::City, 1100, 'unclassified', 0, 0);
        $this->assertPreview('COD', CustomerZone::City, 1100.01, 'violation', 6, 480);
        $this->assertPreview('COD', CustomerZone::City, 2000, 'violation', 15, 1200);
        $this->assertPreview('COD', CustomerZone::IndustrialZone, 5000, 'compliant', 0, 0);
        $this->assertPreview('COD', CustomerZone::IndustrialZone, 6400, 'compliant', 0, 0);
        $this->assertPreview('COD', CustomerZone::IndustrialZone, 6400.01, 'violation', 30, 2400);
    }

    public function test_ph_is_the_same_for_both_zones(): void
    {
        foreach ([CustomerZone::City, CustomerZone::IndustrialZone] as $zone) {
            $this->assertPreview('PH', $zone, 7, 'compliant', 0, 0);
            $this->assertPreview('PH', $zone, 6, 'compliant', 0, 0);
            $this->assertPreview('PH', $zone, 9.5, 'compliant', 0, 0);
            $this->assertPreview('PH', $zone, 5, 'violation', 30, 2400);
            $this->assertPreview('PH', $zone, 11, 'violation', 30, 2400);
            $this->assertPreview('PH', $zone, 12, 'violation', 30, 2400);
            $this->assertPreview('PH', $zone, 1, 'violation', 60, 4800);
            $this->assertPreview('PH', $zone, 12.01, 'violation', 60, 4800);
        }
    }

    public function test_oil_and_grease_industrial_mapping(): void
    {
        $this->assertPreview('G&O', CustomerZone::City, 100, 'unclassified', 0, 0);
        $this->assertPreview('G&O', CustomerZone::City, 100.01, 'violation', 10, 800);
        $this->assertPreview('G&O', CustomerZone::IndustrialZone, 120, 'compliant', 0, 0);
        $this->assertPreview('G&O', CustomerZone::IndustrialZone, 121, 'violation', 10, 800);
        $this->assertPreview('G&O', CustomerZone::IndustrialZone, 1000, 'violation', 25, 2000);
    }

    public function test_bod_severe_band_escalates_using_fourteen_day_windows(): void
    {
        $pollutant = Pollutant::query()->where('code', 'BOD')->firstOrFail();
        $establishment = Establishment::factory()->cityZone()->create();
        $rule = app(ViolationService::class)->findRule($pollutant->id, 2500);
        $this->assertNotNull($rule);

        $startDate = Carbon::parse('2026-01-01');
        Violation::factory()->create([
            'establishment_id' => $establishment->id,
            'pollutant_id' => $pollutant->id,
            'violation_rule_id' => $rule->id,
            'detected_value' => 2500,
            'start_date' => $startDate,
            'current_tier' => 1,
            'current_tier_start_date' => $startDate,
            'status' => ViolationStatus::Active,
        ]);

        $day13 = $this->preview('BOD', CustomerZone::City, 2500, $establishment, $startDate->copy()->addDays(13));
        $day14 = $this->preview('BOD', CustomerZone::City, 2500, $establishment, $startDate->copy()->addDays(14));
        $day28 = $this->preview('BOD', CustomerZone::City, 2500, $establishment, $startDate->copy()->addDays(28));

        $this->assertSame(1, $day13['tier_order']);
        $this->assertEquals(18.0, $day13['price_per_unit']);
        $this->assertSame(2, $day14['tier_order']);
        $this->assertEquals(36.0, $day14['price_per_unit']);
        $this->assertSame(3, $day28['tier_order']);
        $this->assertEquals(90.0, $day28['price_per_unit']);
    }

    public function test_cod_is_discounted_when_the_same_sample_also_has_a_bod_violation(): void
    {
        $result = $this->previewSample([
            'BOD' => 601,
            'COD' => 1100.01,
        ]);

        $bod = $this->lineByCode($result, 'BOD');
        $cod = $this->lineByCode($result, 'COD');

        $this->assertSame('violation', $bod['evaluation_result']);
        $this->assertEquals(3.0, $bod['price_per_unit']);
        $this->assertEquals(240.0, $bod['amount']);

        $this->assertSame('violation', $cod['evaluation_result']);
        $this->assertEquals(3.6, $cod['price_per_unit']);
        $this->assertEquals(288.0, $cod['amount']);
        $this->assertSame('خصم 40% لاجتماع مخالفة BOD', $cod['notes']);
    }

    public function test_cod_is_not_discounted_without_a_bod_violation_on_the_sample(): void
    {
        $codOnly = $this->lineByCode($this->previewSample(['COD' => 1100.01]), 'COD');
        $this->assertEquals(6.0, $codOnly['price_per_unit']);
        $this->assertEquals(480.0, $codOnly['amount']);
        $this->assertNull($codOnly['notes']);

        $compliantBod = $this->lineByCode($this->previewSample([
            'BOD' => 500,
            'COD' => 1100.01,
        ]), 'COD');
        $this->assertEquals(6.0, $compliantBod['price_per_unit']);
        $this->assertEquals(480.0, $compliantBod['amount']);
        $this->assertNull($compliantBod['notes']);
    }

    /**
     * @param  array<string, float>  $readings
     * @return array{lines: list<array<string, mixed>>, pollutant_subtotal: float, fees: list<array<string, mixed>>, total_fees: float, grand_total: float}
     */
    private function previewSample(array $readings): array
    {
        $establishment = Establishment::factory()->create([
            'customer_zone' => CustomerZone::City,
            'name' => 'منشأة اختبار-'.uniqid('sample', true),
        ]);

        $sample = Sample::factory()->pending()->create([
            'establishment_id' => $establishment->id,
            'sample_date' => Carbon::parse('2026-03-01'),
            'water_usage' => 100,
            'status' => SampleStatus::Pending,
        ]);

        foreach ($readings as $code => $value) {
            SampleReading::factory()->create([
                'sample_id' => $sample->id,
                'pollutant_id' => Pollutant::query()->where('code', $code)->firstOrFail()->id,
                'detected_value' => $value,
            ]);
        }

        $sample->load(['readings.pollutant', 'establishment']);

        return app(SampleCalculationService::class)->calculateSample($sample);
    }

    /**
     * @param  array{lines: list<array<string, mixed>>}  $result
     * @return array<string, mixed>
     */
    private function lineByCode(array $result, string $code): array
    {
        foreach ($result['lines'] as $line) {
            if (($line['pollutant_code'] ?? null) === $code) {
                return $line;
            }
        }

        $this->fail("Missing {$code} line");
    }

    private function assertPreview(
        string $code,
        CustomerZone $zone,
        float $value,
        string $result,
        float $price,
        float $amount,
    ): void {
        $line = $this->preview($code, $zone, $value);

        $this->assertSame($result, $line['evaluation_result'], "{$code} {$zone->value} {$value}");
        $this->assertEquals($price, $line['price_per_unit'], "{$code} {$zone->value} {$value} price");
        $this->assertEquals($amount, $line['amount'], "{$code} {$zone->value} {$value} amount");
    }

    /**
     * @return array<string, mixed>
     */
    private function preview(
        string $code,
        CustomerZone $zone,
        float $value,
        ?Establishment $establishment = null,
        ?Carbon $sampleDate = null,
    ): array {
        $pollutant = Pollutant::query()->where('code', $code)->firstOrFail();
        $establishment ??= Establishment::factory()->create([
            'customer_zone' => $zone,
            'name' => 'منشأة اختبار-'.uniqid($code, true),
        ]);
        $establishment->update(['customer_zone' => $zone]);

        $sample = Sample::factory()->pending()->create([
            'establishment_id' => $establishment->id,
            'sample_date' => $sampleDate ?? Carbon::parse('2026-03-01'),
            'water_usage' => 100,
            'status' => SampleStatus::Pending,
        ]);

        $reading = SampleReading::factory()->create([
            'sample_id' => $sample->id,
            'pollutant_id' => $pollutant->id,
            'detected_value' => $value,
        ]);

        $sample->load(['readings.pollutant', 'establishment']);

        return app(SampleCalculationService::class)->calculateReading(
            $reading,
            $sample->sample_date,
            (float) $sample->water_usage,
            $zone,
            $establishment->id,
        );
    }
}
