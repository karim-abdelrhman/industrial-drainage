<?php

namespace App\Filament\Resources\Violations\Schemas;

use App\Models\Violation;
use App\Models\ViolationRuleTier;
use App\Support\InclusiveBoundToggles;
use App\Support\Money;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class ViolationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('بيانات المخالفة')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('establishment.name')
                            ->label('المنشأة')
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('pollutant.name')
                            ->label('الملوث')
                            ->badge()
                            ->color('danger'),
                        TextEntry::make('detected_value')
                            ->label('التركيز المرصود')
                            ->numeric(4),
                        TextEntry::make('allowed_range')
                            ->label('النطاق المطبق')
                            ->state(function (Violation $record): string {
                                $rule = $record->violationRule;

                                if ($rule === null) {
                                    return '—';
                                }

                                return InclusiveBoundToggles::formatLower($rule->from, (bool) $rule->from_inclusive)
                                    .' — '
                                    .InclusiveBoundToggles::formatUpper($rule->to, (bool) $rule->to_inclusive);
                            }),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                        TextEntry::make('start_date')
                            ->label('تاريخ بدء المخالفة')
                            ->date('Y-m-d'),
                    ]),

                Section::make('المستويات المالية')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('current_tier')
                            ->label('المستوى الحالي')
                            ->formatStateUsing(fn ($state): string => 'المستوى '.$state)
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('current_tier_start_date')
                            ->label('تاريخ بدء المستوى')
                            ->date('Y-m-d'),
                        TextEntry::make('elapsed_days')
                            ->label('الأيام المنقضية')
                            ->state(fn (Violation $record): string => (int) $record->start_date->diffInDays(now()).' يوم'),
                        TextEntry::make('next_tier')
                            ->label('المستوى التالي')
                            ->state(function (Violation $record): string {
                                $maxTier = (int) ($record->violationRule?->tiers->max('tier_order') ?? 1);

                                if ((int) $record->current_tier >= $maxTier) {
                                    return 'آخر مستوى';
                                }

                                return 'المستوى '.((int) $record->current_tier + 1);
                            }),
                        TextEntry::make('remaining_days')
                            ->label('المدة المتبقية للمستوى التالي')
                            ->state(function (Violation $record): string {
                                $duration = (int) ($record->violationRule?->duration_days ?? 0);
                                $maxTier = (int) ($record->violationRule?->tiers->max('tier_order') ?? 1);

                                if ($duration <= 0 || (int) $record->current_tier >= $maxTier) {
                                    return '—';
                                }

                                $elapsed = (int) $record->start_date->diffInDays(now());
                                $remaining = ((int) $record->current_tier * $duration) - $elapsed;

                                return $remaining <= 0 ? 'استحق التصعيد' : $remaining.' يوم';
                            }),
                        TextEntry::make('unit_price')
                            ->label('سعر الوحدة الحالي')
                            ->state(function (Violation $record): string {
                                $tier = $record->violationRule?->tiers
                                    ->firstWhere('tier_order', (int) $record->current_tier);

                                return Money::format($tier?->price_per_unit);
                            }),
                        TextEntry::make('tier_track')
                            ->label('تسلسل المستويات')
                            ->html()
                            ->columnSpanFull()
                            ->state(fn (Violation $record): HtmlString => new HtmlString(self::tierTrack($record))),
                    ]),
            ]);
    }

    private static function tierTrack(Violation $record): string
    {
        $tiers = $record->violationRule?->tiers ?? collect();

        if ($tiers->isEmpty()) {
            return '—';
        }

        $current = (int) $record->current_tier;
        $parts = [];

        foreach ($tiers->sortBy('tier_order') as $index => $tier) {
            /** @var ViolationRuleTier $tier */
            $order = (int) $tier->tier_order;
            $class = $order === $current ? 'is-current' : ($order < $current ? 'is-done' : '');
            $label = 'المستوى '.$order.' — '.e(Money::format($tier->price_per_unit));
            $parts[] = '<span class="tier-track__step '.$class.'">'.$label.'</span>';

            if ($index < $tiers->count() - 1) {
                $parts[] = '<span class="tier-track__arrow">←</span>';
            }
        }

        return '<div class="tier-track">'.implode('', $parts).'</div>';
    }
}
