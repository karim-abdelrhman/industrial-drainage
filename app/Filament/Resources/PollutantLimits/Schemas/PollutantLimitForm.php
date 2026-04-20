<?php

namespace App\Filament\Resources\PollutantLimits\Schemas;

use App\Enums\ActivityType;
use App\Enums\PollutantStatus;
use App\Models\Pollutant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PollutantLimitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الحد')
                    ->schema([
                        Select::make('pollutant_id')
                            ->label('الملوث')
                            ->relationship('pollutant', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn (Pollutant $record) => $record->name ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('activity_type')
                            ->label('نوع النشاط')
                            ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $case) => [$case->value => $case->getLabel()]))
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(PollutantStatus::cases())->mapWithKeys(fn (PollutantStatus $case) => [$case->value => $case->getLabel()]))
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('نطاق القيم')
                    ->schema([
                        TextInput::make('min_value')
                            ->label('الحد الأدنى')
                            ->numeric()
                            ->required(),
                        TextInput::make('max_value')
                            ->label('الحد الأقصى (اتركه فارغاً للمفتوح)')
                            ->numeric(),
                    ])
                    ->columns(2),

                Section::make('الفترة الزمنية')
                    ->schema([
                        DatePicker::make('effective_from')
                            ->label('تاريخ البدء')
                            ->required(),
                        DatePicker::make('effective_to')
                            ->label('تاريخ الانتهاء'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
