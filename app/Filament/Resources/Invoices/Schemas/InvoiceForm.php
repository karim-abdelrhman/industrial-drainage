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
                Section::make('بيانات الفاتورة')
                    ->schema([
                        Select::make('establishment_id')
                            ->label('المنشأة')
                            ->relationship('establishment', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('billing_month')
                            ->label('شهر الفوترة')
                            ->displayFormat('Y-m')
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn (InvoiceStatus $c) => [$c->value => $c->getLabel()]))
                            ->default(InvoiceStatus::Draft->value)
                            ->required(),
                        TextInput::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->numeric(),
                        DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
