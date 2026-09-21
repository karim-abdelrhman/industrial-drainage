<?php

namespace Tests\Feature;

use App\Enums\InvoiceItemType;
use App\Models\Establishment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pollutant;
use App\Models\PollutantLimit;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\SampleViolationSnapshot;
use App\Models\Violation;
use App\Models\ViolationRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablishmentDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_an_establishment_removes_operational_records_and_keeps_pollutant_catalog(): void
    {
        $pollutant = Pollutant::factory()->create();
        $limit = PollutantLimit::factory()->create(['pollutant_id' => $pollutant->id]);
        $rule = ViolationRule::factory()->create(['pollutant_id' => $pollutant->id]);

        $establishment = Establishment::factory()->create();
        $otherEstablishment = Establishment::factory()->create();

        $sample = Sample::factory()->create(['establishment_id' => $establishment->id]);
        $reading = SampleReading::factory()->create([
            'sample_id' => $sample->id,
            'pollutant_id' => $pollutant->id,
        ]);

        $violation = Violation::factory()->create([
            'establishment_id' => $establishment->id,
            'pollutant_id' => $pollutant->id,
            'violation_rule_id' => $rule->id,
            'last_sample_id' => $sample->id,
        ]);

        SampleViolationSnapshot::query()->create([
            'sample_id' => $sample->id,
            'sample_reading_id' => $reading->id,
            'establishment_id' => $establishment->id,
            'pollutant_id' => $pollutant->id,
            'violation_id' => $violation->id,
            'violation_rule_id' => $rule->id,
            'detected_value' => 100,
            'tier_order_at_time' => 1,
            'price_per_unit_at_time' => 10,
            'evaluation_result' => 'violation',
        ]);

        $invoice = Invoice::factory()->create([
            'establishment_id' => $establishment->id,
            'sample_id' => $sample->id,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'item_type' => InvoiceItemType::PollutantCharge,
            'violation_id' => $violation->id,
            'pollutant_id' => $pollutant->id,
            'violation_rule_id' => $rule->id,
            'tier_order' => 1,
        ]);

        $otherSample = Sample::factory()->create(['establishment_id' => $otherEstablishment->id]);

        $establishment->delete();

        $this->assertDatabaseMissing('establishments', ['id' => $establishment->id]);
        $this->assertDatabaseMissing('samples', ['id' => $sample->id]);
        $this->assertDatabaseMissing('sample_readings', ['id' => $reading->id]);
        $this->assertDatabaseMissing('violations', ['id' => $violation->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseCount('invoice_items', 0);
        $this->assertDatabaseCount('sample_violation_snapshots', 0);

        $this->assertDatabaseHas('establishments', ['id' => $otherEstablishment->id]);
        $this->assertDatabaseHas('samples', ['id' => $otherSample->id]);
        $this->assertDatabaseHas('pollutants', ['id' => $pollutant->id]);
        $this->assertDatabaseHas('pollutant_limits', ['id' => $limit->id]);
        $this->assertDatabaseHas('violation_rules', ['id' => $rule->id]);
    }
}
