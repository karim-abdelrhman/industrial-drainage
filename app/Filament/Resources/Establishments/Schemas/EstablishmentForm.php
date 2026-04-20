<?php

namespace App\Filament\Resources\Establishments\Schemas;

use App\Enums\ActivityType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EstablishmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المنشأة')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المنشأة')
                            ->required()
                            ->maxLength(200),
                        Select::make('activity_type')
                            ->label('نوع النشاط')
                            ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()]))
                            ->required(),
                        TextInput::make('address')
                            ->label('العنوان')
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('بيانات التواصل')
                    ->schema([
                        TextInput::make('contact_person')
                            ->label('الشخص المسؤول')
                            ->maxLength(150),
                        TextInput::make('phone')
                            ->label('الهاتف')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(150),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
