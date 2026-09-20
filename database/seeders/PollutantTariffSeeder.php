<?php

namespace Database\Seeders;

use App\Enums\CustomerZone;
use App\Enums\PollutantStatus;
use App\Models\InvoiceItem;
use App\Models\Pollutant;
use App\Models\PollutantLimit;
use App\Models\SampleViolationSnapshot;
use App\Models\Violation;
use App\Models\ViolationRule;
use App\Models\ViolationRuleTier;
use Illuminate\Database\Seeder;

class PollutantTariffSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $definition) {
            $pollutant = Pollutant::updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'unit' => $definition['unit'],
                    'is_active' => true,
                ]
            );

            $this->syncLimits($pollutant, $definition['limits']);
            $this->syncRules($pollutant, $definition['rules']);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $limits
     */
    private function syncLimits(Pollutant $pollutant, array $limits): void
    {
        PollutantLimit::query()->where('pollutant_id', $pollutant->id)->delete();

        foreach ($limits as $limit) {
            PollutantLimit::create([
                'pollutant_id' => $pollutant->id,
                'customer_zone' => $limit['customer_zone'],
                'min_value' => $limit['min_value'],
                'max_value' => $limit['max_value'],
                'min_inclusive' => $limit['min_inclusive'],
                'max_inclusive' => $limit['max_inclusive'],
                'price_per_unit' => $limit['price_per_unit'],
                'status' => PollutantStatus::Compliant->value,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     */
    private function syncRules(Pollutant $pollutant, array $rules): void
    {
        $keptIds = [];

        foreach ($rules as $ruleData) {
            $rule = ViolationRule::query()
                ->where('pollutant_id', $pollutant->id)
                ->where('from', $ruleData['from'])
                ->where('from', '<', 900000)
                ->first();

            if ($rule === null) {
                $rule = new ViolationRule(['pollutant_id' => $pollutant->id]);
            }

            $rule->fill([
                'from' => $ruleData['from'],
                'to' => $ruleData['to'],
                'from_inclusive' => $ruleData['from_inclusive'],
                'to_inclusive' => $ruleData['to_inclusive'],
                'duration_days' => $ruleData['duration_days'],
            ]);
            $rule->save();

            $keptIds[] = $rule->id;
            $this->syncTiers($rule, $ruleData['tiers']);
        }

        ViolationRule::query()
            ->where('pollutant_id', $pollutant->id)
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(fn (ViolationRule $leftover) => $this->archiveOrDeleteRule($leftover));
    }

    /**
     * @param  list<float>  $prices
     */
    private function syncTiers(ViolationRule $rule, array $prices): void
    {
        foreach ($prices as $index => $price) {
            ViolationRuleTier::updateOrCreate(
                ['violation_rule_id' => $rule->id, 'tier_order' => $index + 1],
                ['price_per_unit' => $price]
            );
        }

        ViolationRuleTier::query()
            ->where('violation_rule_id', $rule->id)
            ->where('tier_order', '>', count($prices))
            ->delete();
    }

    private function archiveOrDeleteRule(ViolationRule $rule): void
    {
        $inUse = Violation::query()->where('violation_rule_id', $rule->id)->exists()
            || InvoiceItem::query()->where('violation_rule_id', $rule->id)->exists()
            || SampleViolationSnapshot::query()->where('violation_rule_id', $rule->id)->exists();

        if ($inUse) {
            $sentinel = 900000 + $rule->id;
            $rule->update([
                'from' => $sentinel,
                'to' => $sentinel,
                'from_inclusive' => true,
                'to_inclusive' => false,
                'duration_days' => max(1, (int) $rule->duration_days),
            ]);

            return;
        }

        $rule->tiers()->delete();
        $rule->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function definitions(): array
    {
        return [
            [
                'code' => 'BOD',
                'name' => 'الطلب البيولوجي للأكسجين',
                'unit' => 'mg/L',
                'limits' => [
                    $this->limit(CustomerZone::City, 244, 600, false, true, 1),
                    $this->limit(CustomerZone::IndustrialZone, 0, 2676, true, false, 1),
                ],
                'rules' => [
                    $this->rule(600, 660, false, false, 180, [3, 6, 15]),
                    $this->rule(660, 2000, true, false, 90, [9, 18, 45]),
                    $this->rule(2000, null, true, false, 14, [18, 36, 90]),
                ],
            ],
            [
                'code' => 'TSS',
                'name' => 'المواد الصلبة العالقة الكلية',
                'unit' => 'mg/L',
                'limits' => [
                    $this->limit(CustomerZone::City, 500, 800, false, true, 0.5),
                    $this->limit(CustomerZone::IndustrialZone, 0, 1964, true, true, 0.5),
                ],
                'rules' => [
                    $this->rule(800, 880, false, false, 180, [2, 4, 10]),
                    $this->rule(880, 3000, true, false, 90, [5, 10, 25]),
                    $this->rule(3000, null, true, false, 7, [15, 30, 75]),
                ],
            ],
            [
                'code' => 'COD',
                'name' => 'الطلب الكيميائي للأكسجين',
                'unit' => 'mg/L',
                'limits' => [
                    $this->limit(CustomerZone::IndustrialZone, 0, 6400, true, true, 0),
                ],
                'rules' => [
                    $this->rule(1100, 2000, false, false, 90, [6, 12, 30]),
                    $this->rule(2000, 5000, true, false, 60, [15, 35, 90]),
                    $this->rule(5000, null, true, false, 7, [30, 60, 150]),
                ],
            ],
            [
                'code' => 'PH',
                'name' => 'الرقم الهيدروجيني',
                'unit' => 'pH',
                'limits' => [
                    $this->limit(CustomerZone::City, 6, 9.5, true, true, 0),
                    $this->limit(CustomerZone::IndustrialZone, 6, 9.5, true, true, 0),
                ],
                'rules' => [
                    $this->rule(0, 2, true, false, 7, [60, 120, 300]),
                    $this->rule(2, 6, true, false, 14, [30, 60, 75]),
                    $this->rule(9.5, 12, false, true, 14, [30, 60, 75]),
                    $this->rule(12, null, false, false, 7, [60, 120, 300]),
                ],
            ],
            [
                'code' => 'G&O',
                'name' => 'الزيوت والشحوم',
                'unit' => 'mg/L',
                'limits' => [
                    $this->limit(CustomerZone::IndustrialZone, 0, 120, true, true, 0),
                ],
                'rules' => [
                    $this->rule(100, 1000, false, false, 30, [10, 20, 50]),
                    $this->rule(1000, null, true, false, 14, [25, 50, 125]),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function limit(CustomerZone $zone, float $min, ?float $max, bool $minInclusive, bool $maxInclusive, float $price): array
    {
        return [
            'customer_zone' => $zone->value,
            'min_value' => $min,
            'max_value' => $max,
            'min_inclusive' => $minInclusive,
            'max_inclusive' => $maxInclusive,
            'price_per_unit' => $price,
        ];
    }

    /**
     * @param  list<float>  $tiers
     * @return array<string, mixed>
     */
    private function rule(float $from, ?float $to, bool $fromInclusive, bool $toInclusive, int $durationDays, array $tiers): array
    {
        return [
            'from' => $from,
            'to' => $to,
            'from_inclusive' => $fromInclusive,
            'to_inclusive' => $toInclusive,
            'duration_days' => $durationDays,
            'tiers' => $tiers,
        ];
    }
}
