<?php

namespace App\Filament\Resources\Violations\Schemas;

use App\Enums\ViolationStatus;
use App\Models\Pollutant;
use App\Models\ViolationRule;
use App\Support\InclusiveBoundToggles;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViolationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المخالفة')
                    ->columns(2)
                    ->schema([
                        Select::make('establishment_id')
                            ->label('المنشأة')
                            ->relationship('establishment', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('pollutant_id')
                            ->label('الملوث')
                            ->relationship('pollutant', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Pollutant $record) => ($record->code ? $record->code.' — ' : '').$record->name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('violation_rule_id')
                            ->label('قاعدة المخالفة')
                            ->relationship('violationRule', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn (ViolationRule $record) => ($record->pollutant?->name ?? 'ملوث').' — '
                                    .InclusiveBoundToggles::formatLower($record->from, (bool) $record->from_inclusive)
                                    .' / '
                                    .InclusiveBoundToggles::formatUpper($record->to, (bool) $record->to_inclusive)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('detected_value')
                            ->label('التركيز المرصود')
                            ->numeric()
                            ->required(),
                    ]),

                Section::make('بيانات المتابعة')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required(),
                        TextInput::make('current_tier')
                            ->label('المستوى الحالي')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        DatePicker::make('current_tier_start_date')
                            ->label('تاريخ بدء المستوى')
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(ViolationStatus::cases())->mapWithKeys(fn (ViolationStatus $case) => [$case->value => $case->getLabel()]))
                            ->default(ViolationStatus::Active->value)
                            ->required(),
                    ]),
            ]);
    }
}
