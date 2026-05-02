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
                            ->getOptionLabelFromRecordUsing(fn (Pollutant $record) => $record->code ?? $record->name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('from')
                            ->label('الحد الأدنى')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('to')
                            ->label('الحد الأقصى (فارغ = مفتوح)')
                            ->numeric()
                            ->minValue(0),
                        Select::make('activity_type')
                            ->label('نوع النشاط')
                            ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()]))
                            ->required(),
                        TextInput::make('duration_days')
                            ->label('مهلة توفيق الأوضاع (أيام)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('مدة كل مرحلة قبل الانتقال للتالية'),
                    ])
                    ->columns(3),
            ])->columns(1);
    }
}
