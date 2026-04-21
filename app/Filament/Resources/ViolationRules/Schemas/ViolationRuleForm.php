<?php

namespace App\Filament\Resources\ViolationRules\Schemas;

use App\Enums\ActivityType;
use App\Models\Pollutant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViolationRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل القاعدة')
                    ->schema([
                        Select::make('pollutant_id')
                            ->label('الملوث')
                            ->relationship('pollutant', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Pollutant $record) => $record->name ?? $record->code)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('activity_type')
                            ->label('نوع النشاط')
                            ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()]))
                            ->required(),
                        TextInput::make('from')
                            ->label('من')
                            ->numeric()
                            ->required(),
                        TextInput::make('to')
                            ->label('الي')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }
}
