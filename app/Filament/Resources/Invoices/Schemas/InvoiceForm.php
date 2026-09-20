<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المنشأة')
                    ->columns(2)
                    ->schema([
                        Select::make('establishment_id')
                            ->label('المنشأة')
                            ->relationship('establishment', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('تفاصيل المطالبة')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('billing_month')
                            ->label('شهر الفوترة')
                            ->displayFormat('Y-m')
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn (InvoiceStatus $case) => [$case->value => $case->getLabel()]))
                            ->default(InvoiceStatus::Draft->value)
                            ->required(),
                        TextInput::make('total_amount')
                            ->label('إجمالي المطالبة (ج.م)')
                            ->numeric()
                            ->prefix('ج.م'),
                        DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
