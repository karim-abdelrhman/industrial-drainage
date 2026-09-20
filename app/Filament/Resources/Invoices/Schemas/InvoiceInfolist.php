<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use App\Support\DataSheet;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('بيانات المنشأة')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('establishment.name')
                            ->label('المنشأة')
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('establishment.activity_type')
                            ->label('نوع النشاط')
                            ->badge(),
                        TextEntry::make('establishment.customer_zone')
                            ->label('منطقة التعريفة')
                            ->badge(),
                    ]),

                Section::make('تفاصيل العينة')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sample.sample_number')
                            ->label('رقم العينة')
                            ->placeholder('—'),
                        TextEntry::make('sample.sample_date')
                            ->label('تاريخ العينة')
                            ->date('Y-m-d')
                            ->placeholder('—'),
                        TextEntry::make('sample.water_usage')
                            ->label('استهلاك المياه')
                            ->numeric(4)
                            ->suffix(' م³')
                            ->placeholder('—'),
                        TextEntry::make('billing_month')
                            ->label('شهر الفوترة')
                            ->date('Y-m'),
                        TextEntry::make('issued_at')
                            ->label('تاريخ الإصدار')
                            ->date('Y-m-d')
                            ->placeholder('—'),
                        TextEntry::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->date('Y-m-d')
                            ->placeholder('—'),
                        TextEntry::make('status')
                            ->label('حالة المطالبة')
                            ->badge(),
                    ]),

                Section::make('بنود المطالبة')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('items_table')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull()
                            ->state(fn (Invoice $record): HtmlString => new HtmlString(DataSheet::invoiceItems($record))),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
