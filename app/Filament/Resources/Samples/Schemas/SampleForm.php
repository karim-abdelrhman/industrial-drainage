<?php

namespace App\Filament\Resources\Samples\Schemas;

use App\Enums\SampleStatus;
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
