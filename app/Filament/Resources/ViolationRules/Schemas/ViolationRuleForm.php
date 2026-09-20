<?php

namespace App\Filament\Resources\ViolationRules\Schemas;

use App\Models\Pollutant;
use App\Support\InclusiveBoundToggles;
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
                            ->required(),
                        TextInput::make('to')
                            ->label('الحد الأقصى (فارغ = مفتوح)')
                            ->numeric()
                            ->minValue(0),
                        InclusiveBoundToggles::lower('from_inclusive')->default(true),
                        InclusiveBoundToggles::upper('to_inclusive')->default(false),
                        TextInput::make('duration_days')
                            ->label('مهلة توفيق الأوضاع (أيام)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('مدة كل مرحلة قبل الانتقال للتالية'),
                    ])
                    ->columns(4),
            ])->columns(1);
    }
}
