<?php

namespace App\Filament\Resources\Samples\Schemas;

use App\Models\Sample;
use App\Support\DataSheet;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class SampleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('بيانات العينة')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sample_number')
                            ->label('رقم العينة')
                            ->weight(FontWeight::SemiBold)
                            ->copyable(),
                        TextEntry::make('establishment.name')
                            ->label('المنشأة'),
                        TextEntry::make('sample_date')
                            ->label('تاريخ العينة')
                            ->date('Y-m-d'),
                        TextEntry::make('water_usage')
                            ->label('استهلاك المياه')
                            ->numeric(4)
                            ->suffix(' م³'),
                        TextEntry::make('sample_type')
                            ->label('نوع العينة')
                            ->badge(),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                        TextEntry::make('collected_by')
                            ->label('جُمعت بواسطة')
                            ->placeholder('—'),
                        TextEntry::make('evaluated_at')
                            ->label('تاريخ التقييم')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('—'),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('تقرير المعمل')
                    ->schema([
                        ImageEntry::make('lab_report_image')
                            ->label('صورة تقرير المعمل')
                            ->placeholder('لا توجد صورة مرفقة'),
                    ])
                    ->collapsed(),

                Section::make('نتائج التحاليل')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('analysis_results')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull()
                            ->state(fn (Sample $record): HtmlString => new HtmlString(DataSheet::sampleResults($record))),
                    ]),
            ]);
    }
}
