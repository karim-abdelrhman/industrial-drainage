<?php

namespace App\Filament\Resources\Pollutants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PollutantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->collapsible()
                    ->collapsed(false)
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('unit')
                            ->label('الوحدة')
                            ->default('مليجرام/لتر')
                            ->maxLength(30),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ]),
            ])->columns(1);
    }
}
