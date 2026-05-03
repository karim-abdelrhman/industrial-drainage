<?php

namespace App\Filament\Resources\Samples\Schemas;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SampleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات العينة')
                    ->schema([
                        Select::make('establishment_id')
                            ->label('المنشأة')
                            ->relationship('establishment', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('sample_number')
                            ->label('رقم العينة')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('sample_date')
                            ->label('تاريخ أخذ العينة')
                            ->required(),
                        TextInput::make('water_usage')
                            ->label('الاستخدام المائي (م³)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.0001)
                            ->required(),
                        Select::make('sample_type')
                            ->label('نوع العينة')
                            ->options(collect(SampleType::cases())->mapWithKeys(fn (SampleType $c) => [$c->value => $c->getLabel()]))
                            ->default(SampleType::Regular->value)
                            ->required()
                            ->helperText('المركبة: 1400 ج.م — العادية: تبعًا لموقع المنشأة'),
                        TextInput::make('collected_by')
                            ->label('جُمعت بواسطة')
                            ->maxLength(150),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(SampleStatus::cases())->mapWithKeys(fn (SampleStatus $c) => [$c->value => $c->getLabel()]))
                            ->default(SampleStatus::Pending->value)
                            ->required(),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
