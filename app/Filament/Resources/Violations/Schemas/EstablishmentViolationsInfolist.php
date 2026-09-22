<?php

namespace App\Filament\Resources\Violations\Schemas;

use App\Enums\ViolationStatus;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Establishment;
use App\Models\Violation;
use App\Support\InclusiveBoundToggles;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class EstablishmentViolationsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('بيانات المنشأة')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('المنشأة')
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('activity_type')
                            ->label('نوع النشاط')
                            ->badge(),
                        TextEntry::make('customer_zone')
                            ->label('منطقة التعريفة')
                            ->badge(),
                        TextEntry::make('active_count')
                            ->label('مخالفات نشطة')
                            ->badge()
                            ->color('danger')
                            ->state(fn (Establishment $record): int => $record->violations
                                ->where('status', ViolationStatus::Active)
                                ->count()),
                        TextEntry::make('resolved_count')
                            ->label('مخالفات معالجة')
                            ->badge()
                            ->color('success')
                            ->state(fn (Establishment $record): int => $record->violations
                                ->where('status', ViolationStatus::Resolved)
                                ->count()),
                        TextEntry::make('violations_count')
                            ->label('إجمالي المخالفات')
                            ->state(fn (Establishment $record): int => $record->violations->count()),
                    ]),

                Section::make('مخالفات المنشأة')
                    ->schema([
                        RepeatableEntry::make('violations')
                            ->hiddenLabel()
                            ->placeholder('لا توجد مخالفات لهذه المنشأة.')
                            ->columnSpanFull()
                            ->table([
                                TableColumn::make('الملوث'),
                                TableColumn::make('التركيز'),
                                TableColumn::make('نطاق القاعدة'),
                                TableColumn::make('المستوى'),
                                TableColumn::make('تاريخ البدء'),
                                TableColumn::make('الحالة'),
                                TableColumn::make('التفاصيل'),
                            ])
                            ->schema([
                                TextEntry::make('pollutant.name')
                                    ->label('الملوث')
                                    ->formatStateUsing(function ($state, Violation $record): string {
                                        $code = $record->pollutant?->code;

                                        return filled($code) ? $code.' — '.$state : (string) $state;
                                    }),
                                TextEntry::make('detected_value')
                                    ->label('التركيز')
                                    ->numeric(4),
                                TextEntry::make('rule_range')
                                    ->label('نطاق القاعدة')
                                    ->state(function (Violation $record): string {
                                        $rule = $record->violationRule;

                                        if ($rule === null) {
                                            return '—';
                                        }

                                        return InclusiveBoundToggles::formatLower($rule->from, (bool) $rule->from_inclusive)
                                            .' — '
                                            .InclusiveBoundToggles::formatUpper($rule->to, (bool) $rule->to_inclusive);
                                    }),
                                TextEntry::make('current_tier')
                                    ->label('المستوى')
                                    ->badge()
                                    ->formatStateUsing(fn ($state): string => 'المستوى '.$state)
                                    ->color(fn ($state): string => match ((int) $state) {
                                        1 => 'warning',
                                        2 => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->date('Y-m-d'),
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge(),
                                TextEntry::make('details')
                                    ->label('التفاصيل')
                                    ->state('عرض')
                                    ->url(fn (Violation $record): string => ViolationResource::getUrl('view', ['record' => $record]))
                                    ->color('primary'),
                            ]),
                    ]),
            ]);
    }
}
