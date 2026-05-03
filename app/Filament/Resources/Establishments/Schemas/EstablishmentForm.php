<?php

namespace App\Filament\Resources\Establishments\Schemas;

use App\Enums\ActivityType;
use App\Enums\LocationType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                        Select::make('location_type')
                            ->label('الموقع الجغرافي')
                            ->options(collect(LocationType::cases())->mapWithKeys(fn (LocationType $c) => [$c->value => $c->getLabel()]))
                            ->default(LocationType::InsideCity->value)
                            ->required()
                            ->helperText('يحدد رسوم جمع العينة (داخل أو خارج النطاق العمراني)'),
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

                    ])
                    ->columns(2),
            ]);
    }
}
