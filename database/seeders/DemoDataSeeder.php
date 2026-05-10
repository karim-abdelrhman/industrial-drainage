<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Enums\LocationType;
use App\Enums\PollutantStatus;
use App\Enums\SampleStatus;
use App\Enums\SampleType;
use App\Enums\ViolationStatus;
use App\Models\Establishment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pollutant;
use App\Models\PollutantLimit;
use App\Models\Sample;
use App\Models\SampleReading;
use App\Models\SystemSetting;
use App\Models\Violation;
use App\Models\ViolationRule;
use App\Models\ViolationRuleTier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSystemSettings();
        $pollutants = $this->seedPollutants();
        $rules = $this->seedViolationRules($pollutants);
        $establishments = $this->seedEstablishments();
        $this->seedSamplesAndReadings($establishments, $pollutants);
        $this->seedViolations($establishments, $pollutants, $rules);
        $this->seedInvoices($establishments);
    }

    private function seedSystemSettings(): void
    {
        $settings = [
            ['key' => 'collection_fee_inside_city', 'label' => 'رسوم جمع العينة داخل النطاق العمراني', 'value' => '250', 'type' => 'decimal'],
            ['key' => 'collection_fee_outside_city', 'label' => 'رسوم جمع العينة خارج النطاق العمراني', 'value' => '450', 'type' => 'decimal'],
            ['key' => 'collection_fee_composite', 'label' => 'رسوم جمع العينة المركبة', 'value' => '1400', 'type' => 'decimal'],
            ['key' => 'admin_fee_percentage', 'label' => 'نسبة الرسوم الإدارية', 'value' => '20', 'type' => 'decimal'],
            ['key' => 'analysis_fee', 'label' => 'رسوم التحليل', 'value' => '355', 'type' => 'decimal'],
            ['key' => 'issuance_fee', 'label' => 'رسوم الإصدار', 'value' => '0.50', 'type' => 'decimal'],
            ['key' => 'vat_percentage', 'label' => 'نسبة ضريبة القيمة المضافة', 'value' => '14', 'type' => 'decimal'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    /** @return array<string, Pollutant> */
    private function seedPollutants(): array
    {
        $data = [
            'bod' => ['code' => 'BOD5', 'name' => 'الطلب البيولوجي للأكسجين', 'unit' => 'mg/L'],
            'cod' => ['code' => 'COD', 'name' => 'الطلب الكيميائي للأكسجين', 'unit' => 'mg/L'],
            'tss' => ['code' => 'TSS', 'name' => 'المواد الصلبة العالقة الكلية', 'unit' => 'mg/L'],
            'oil' => ['code' => 'O&G', 'name' => 'الزيوت والشحوم', 'unit' => 'mg/L'],
            'nh3' => ['code' => 'NH3', 'name' => 'الأمونيا', 'unit' => 'mg/L'],
            'ph' => ['code' => 'pH', 'name' => 'الرقم الهيدروجيني', 'unit' => 'pH'],
        ];

        $pollutants = [];
        foreach ($data as $key => $attrs) {
            $p = Pollutant::firstOrCreate(['code' => $attrs['code']], array_merge($attrs, ['is_active' => true]));
            $pollutants[$key] = $p;

            PollutantLimit::firstOrCreate(
                ['pollutant_id' => $p->id, 'activity_type' => ActivityType::Industrial->value],
                [
                    'min_value' => 0,
                    'max_value' => match ($key) {
                        'bod' => 60, 'cod' => 200, 'tss' => 60, 'oil' => 15, 'nh3' => 35, default => 9,
                    },
                    'price_per_unit' => match ($key) {
                        'bod' => 8.5, 'cod' => 5.0, 'tss' => 6.0, 'oil' => 20.0, 'nh3' => 12.0, default => 3.0,
                    },
                    'status' => PollutantStatus::Compliant->value,
                ]
            );

            PollutantLimit::firstOrCreate(
                ['pollutant_id' => $p->id, 'activity_type' => ActivityType::Commercial->value],
                [
                    'min_value' => 0,
                    'max_value' => match ($key) {
                        'bod' => 50, 'cod' => 150, 'tss' => 50, 'oil' => 10, 'nh3' => 25, default => 8.5,
                    },
                    'price_per_unit' => match ($key) {
                        'bod' => 7.0, 'cod' => 4.0, 'tss' => 5.0, 'oil' => 18.0, 'nh3' => 10.0, default => 2.5,
                    },
                    'status' => PollutantStatus::Compliant->value,
                ]
            );
        }

        return $pollutants;
    }

    /** @return array<string, ViolationRule> */
    private function seedViolationRules(array $pollutants): array
    {
        $rules = [];

        $ruleData = [
            'bod' => [
                ['from' => 60, 'to' => 120, 'duration_days' => 30, 'tiers' => [10, 18, 28]],
                ['from' => 120, 'to' => null, 'duration_days' => 30, 'tiers' => [20, 35, 55]],
            ],
            'cod' => [
                ['from' => 200, 'to' => 400, 'duration_days' => 30, 'tiers' => [8, 14, 22]],
                ['from' => 400, 'to' => null, 'duration_days' => 30, 'tiers' => [16, 28, 44]],
            ],
            'tss' => [
                ['from' => 60, 'to' => 150, 'duration_days' => 30, 'tiers' => [9, 16, 25]],
                ['from' => 150, 'to' => null, 'duration_days' => 30, 'tiers' => [18, 32, 50]],
            ],
            'oil' => [
                ['from' => 15, 'to' => 40, 'duration_days' => 30, 'tiers' => [25, 45, 70]],
                ['from' => 40, 'to' => null, 'duration_days' => 30, 'tiers' => [50, 90, 140]],
            ],
            'nh3' => [
                ['from' => 35, 'to' => 70, 'duration_days' => 30, 'tiers' => [15, 26, 40]],
                ['from' => 70, 'to' => null, 'duration_days' => 30, 'tiers' => [30, 52, 80]],
            ],
        ];

        foreach ($ruleData as $key => $rules_config) {
            if (! isset($pollutants[$key])) {
                continue;
            }

            $pollutant = $pollutants[$key];
            foreach ($rules_config as $i => $config) {
                $rule = ViolationRule::firstOrCreate(
                    ['pollutant_id' => $pollutant->id, 'from' => $config['from']],
                    ['to' => $config['to'], 'duration_days' => $config['duration_days']]
                );

                foreach ($config['tiers'] as $tierOrder => $pricePerUnit) {
                    ViolationRuleTier::firstOrCreate(
                        ['violation_rule_id' => $rule->id, 'tier_order' => $tierOrder + 1],
                        ['price_per_unit' => $pricePerUnit]
                    );
                }

                $rules["{$key}_{$i}"] = $rule;
            }
        }

        return $rules;
    }

    /** @return array<Establishment> */
    private function seedEstablishments(): array
    {
        $establishmentData = [
            ['name' => 'مصنع النيل للورق والكرتون', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::InsideCity],
            ['name' => 'شركة الدلتا للصناعات الكيماوية', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::OutsideCity],
            ['name' => 'مصنع الإسكندرية للزجاج', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::InsideCity],
            ['name' => 'شركة القاهرة للمنسوجات', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::InsideCity],
            ['name' => 'مصنع الجيزة للبلاستيك', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::OutsideCity],
            ['name' => 'شركة سيناء للصناعات المعدنية', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::OutsideCity],
            ['name' => 'مصنع الصعيد للغزل والنسيج', 'activity_type' => ActivityType::Commercial, 'location_type' => LocationType::InsideCity],
            ['name' => 'شركة بورسعيد للبتروكيماويات', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::OutsideCity],
            ['name' => 'مصنع الإسماعيلية للطلاء والدهانات', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::InsideCity],
            ['name' => 'شركة المنيا للصناعات الغذائية', 'activity_type' => ActivityType::Commercial, 'location_type' => LocationType::InsideCity],
            ['name' => 'مصنع أسيوط للأسمدة والكيماويات', 'activity_type' => ActivityType::Industrial, 'location_type' => LocationType::OutsideCity],
            ['name' => 'شركة طنطا للصناعات الجلدية', 'activity_type' => ActivityType::Commercial, 'location_type' => LocationType::InsideCity],
        ];

        $establishments = [];
        foreach ($establishmentData as $attrs) {
            $establishments[] = Establishment::firstOrCreate(
                ['name' => $attrs['name']],
                [
                    'activity_type' => $attrs['activity_type'],
                    'location_type' => $attrs['location_type'],
                    'address' => fake()->address(),
                    'contact_person' => fake()->name(),
                    'phone' => '01'.fake()->numerify('#########'),
                    'email' => fake()->unique()->companyEmail(),
                    'is_active' => true,
                ]
            );
        }

        return $establishments;
    }

    private function seedSamplesAndReadings(array $establishments, array $pollutants): void
    {
        $sampleCounter = Sample::max('id') ?? 0;
        $pollutantList = array_filter($pollutants, fn ($key) => $key !== 'ph', ARRAY_FILTER_USE_KEY);

        foreach ($establishments as $establishment) {
            for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {
                $sampleDate = now()->subMonths($monthOffset)->startOfMonth()->addDays(fake()->numberBetween(2, 20));
                $sampleCounter++;

                $sample = Sample::create([
                    'establishment_id' => $establishment->id,
                    'sample_number' => 'SMP-'.str_pad($sampleCounter, 5, '0', STR_PAD_LEFT),
                    'sample_date' => $sampleDate,
                    'water_usage' => fake()->randomFloat(2, 20, 300),
                    'collected_by' => 'م. أحمد '.fake()->lastName(),
                    'status' => SampleStatus::Evaluated,
                    'sample_type' => SampleType::Regular,
                    'evaluated_at' => Carbon::instance($sampleDate)->addDays(2),
                ]);

                foreach ($pollutantList as $pollutant) {
                    SampleReading::create([
                        'sample_id' => $sample->id,
                        'pollutant_id' => $pollutant->id,
                        'detected_value' => fake()->randomFloat(4, 10, 250),
                    ]);
                }
            }
        }
    }

    private function seedViolations(array $establishments, array $pollutants, array $rules): void
    {
        $topViolators = array_slice($establishments, 0, 5);
        $otherEstablishments = array_slice($establishments, 5);
        $pollutantKeys = ['bod', 'cod', 'tss', 'oil', 'nh3'];

        foreach ($topViolators as $i => $establishment) {
            $violationCount = [8, 7, 6, 5, 4][$i];

            for ($v = 0; $v < $violationCount; $v++) {
                $pollutantKey = $pollutantKeys[$v % count($pollutantKeys)];
                $pollutant = $pollutants[$pollutantKey];
                $ruleKey = "{$pollutantKey}_0";
                if (! isset($rules[$ruleKey])) {
                    continue;
                }
                $rule = $rules[$ruleKey];
                $startDate = now()->subDays(fake()->numberBetween(10, 150));
                $elapsedDays = $startDate->diffInDays(now());
                $tier = min((int) floor($elapsedDays / $rule->duration_days) + 1, 3);
                $status = ($v < 2 && $i < 3) ? ViolationStatus::Resolved : ViolationStatus::Active;
                $toValue = $rule->to ?? (float) $rule->from * 2;

                Violation::create([
                    'establishment_id' => $establishment->id,
                    'pollutant_id' => $pollutant->id,
                    'violation_rule_id' => $rule->id,
                    'detected_value' => fake()->randomFloat(4, (float) $rule->from + 1, $toValue),
                    'start_date' => $startDate,
                    'current_tier' => $tier,
                    'current_tier_start_date' => $startDate->copy()->addDays(($tier - 1) * $rule->duration_days),
                    'status' => $status,
                    'last_evaluated_at' => now(),
                ]);
            }
        }

        foreach ($otherEstablishments as $establishment) {
            $pollutantKey = $pollutantKeys[array_rand($pollutantKeys)];
            $pollutant = $pollutants[$pollutantKey];
            $ruleKey = "{$pollutantKey}_0";
            if (! isset($rules[$ruleKey])) {
                continue;
            }
            $rule = $rules[$ruleKey];
            $startDate = now()->subDays(fake()->numberBetween(5, 90));
            $toValue = $rule->to ?? (float) $rule->from * 2;

            Violation::create([
                'establishment_id' => $establishment->id,
                'pollutant_id' => $pollutant->id,
                'violation_rule_id' => $rule->id,
                'detected_value' => fake()->randomFloat(4, (float) $rule->from + 1, $toValue),
                'start_date' => $startDate,
                'current_tier' => 1,
                'current_tier_start_date' => $startDate,
                'status' => ViolationStatus::Active,
                'last_evaluated_at' => now(),
            ]);
        }

        $this->seedEscalationSoonViolations($establishments, $pollutants, $rules);
    }

    private function seedEscalationSoonViolations(array $establishments, array $pollutants, array $rules): void
    {
        $scenarios = [
            ['establishment' => $establishments[0], 'pollutantKey' => 'nh3', 'ruleKey' => 'nh3_0', 'tier' => 1, 'daysUntilNext' => 3],
            ['establishment' => $establishments[2], 'pollutantKey' => 'cod', 'ruleKey' => 'cod_0', 'tier' => 1, 'daysUntilNext' => 5],
            ['establishment' => $establishments[3], 'pollutantKey' => 'tss', 'ruleKey' => 'tss_0', 'tier' => 2, 'daysUntilNext' => 1],
            ['establishment' => $establishments[1], 'pollutantKey' => 'oil', 'ruleKey' => 'oil_0', 'tier' => 1, 'daysUntilNext' => 6],
        ];

        foreach ($scenarios as $scenario) {
            if (! isset($rules[$scenario['ruleKey']]) || ! isset($pollutants[$scenario['pollutantKey']])) {
                continue;
            }

            $rule = $rules[$scenario['ruleKey']];
            $pollutant = $pollutants[$scenario['pollutantKey']];
            $tier = $scenario['tier'];
            $daysUntilNext = $scenario['daysUntilNext'];
            $elapsedDays = $tier * $rule->duration_days - $daysUntilNext;
            $startDate = now()->subDays($elapsedDays);
            $toValue = $rule->to ?? (float) $rule->from * 2;

            Violation::create([
                'establishment_id' => $scenario['establishment']->id,
                'pollutant_id' => $pollutant->id,
                'violation_rule_id' => $rule->id,
                'detected_value' => fake()->randomFloat(4, (float) $rule->from + 1, $toValue),
                'start_date' => $startDate,
                'current_tier' => $tier,
                'current_tier_start_date' => $startDate->copy()->addDays(($tier - 1) * $rule->duration_days),
                'status' => ViolationStatus::Active,
                'last_evaluated_at' => now(),
            ]);
        }
    }

    private function seedInvoices(array $establishments): void
    {
        $monthlyTotals = [18500, 22000, 27500, 31000, 38000, 42500];

        for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {
            $billingMonth = now()->subMonths($monthOffset)->startOfMonth();
            $targetTotal = $monthlyTotals[5 - $monthOffset];
            $isCurrentMonth = $monthOffset === 0;

            foreach ($establishments as $i => $establishment) {
                $baseAmount = $targetTotal / count($establishments);
                $variance = $baseAmount * fake()->randomFloat(2, -0.3, 0.3);
                $invoiceAmount = max(500, round($baseAmount + $variance, 2));

                if ($isCurrentMonth) {
                    $status = $i < 8 ? InvoiceStatus::Issued : InvoiceStatus::Overdue;
                } elseif ($monthOffset <= 2) {
                    $status = $i < 9 ? InvoiceStatus::Paid : InvoiceStatus::Overdue;
                } else {
                    $status = InvoiceStatus::Paid;
                }

                $issuedAt = $billingMonth->copy()->addDays(3);
                $dueDate = $issuedAt->copy()->addDays(30);

                $invoice = Invoice::create([
                    'establishment_id' => $establishment->id,
                    'sample_id' => null,
                    'billing_month' => $billingMonth,
                    'status' => $status,
                    'total_amount' => $invoiceAmount,
                    'issued_at' => $issuedAt,
                    'due_date' => $dueDate,
                ]);

                $this->createInvoiceItems($invoice, $invoiceAmount);
            }
        }
    }

    private function createInvoiceItems(Invoice $invoice, float $totalAmount): void
    {
        $collectionFee = 250.0;
        $adminFee = round($collectionFee * 0.20, 2);
        $analysisFee = 355.0;
        $issuanceFee = 0.50;
        $feesSubtotal = $collectionFee + $adminFee + $analysisFee + $issuanceFee;
        $vat = round($feesSubtotal * 0.14, 2);
        $pollutantCharge = max(0, round($totalAmount - $feesSubtotal - $vat, 2));

        $items = [
            [InvoiceItemType::PollutantCharge, $pollutantCharge],
            [InvoiceItemType::CollectionFee, $collectionFee],
            [InvoiceItemType::AdminFee, $adminFee],
            [InvoiceItemType::AnalysisFee, $analysisFee],
            [InvoiceItemType::IssuanceFee, $issuanceFee],
            [InvoiceItemType::Vat, $vat],
        ];

        foreach ($items as [$type, $amount]) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => $type,
                'violation_id' => null,
                'pollutant_id' => null,
                'violation_rule_id' => null,
                'tier_order' => null,
                'price_per_unit' => 0,
                'detected_value' => 0,
                'amount' => $amount,
            ]);
        }
    }
}
