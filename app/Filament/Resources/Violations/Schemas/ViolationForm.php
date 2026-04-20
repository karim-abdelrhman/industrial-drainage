<?php

namespace App\Filament\Resources\Violations\Schemas;

use App\Enums\ViolationStatus;
use App\Models\Pollutant;
use App\Models\ViolationRule;
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
                    ->schema([
                        TextInput::make('establishment_id')
                            ->label('رقم المنشأة')
                            ->numeric()
                            ->required(),
                        Select::make('pollutant_id')
                            ->label('الملوث')
                            ->relationship('pollutant', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Pollutant $record) => $record->name ?? $record->code)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('violation_rule_id')
                            ->label('قاعدة المخالفة')
                            ->relationship('violationRule', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn (ViolationRule $record) => "{$record->pollutant?->name} ({$record->min_value} – ".($record->max_value ?? '∞').')'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('detected_value')
                            ->label('القيمة المرصودة')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('بيانات المتابعة')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required(),
                        TextInput::make('current_tier')
                            ->label('المرحلة الحالية')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        DatePicker::make('current_tier_start_date')
                            ->label('تاريخ بدء المرحلة')
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(ViolationStatus::cases())->mapWithKeys(fn (ViolationStatus $c) => [$c->value => $c->getLabel()]))
                            ->default(ViolationStatus::Active->value)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
