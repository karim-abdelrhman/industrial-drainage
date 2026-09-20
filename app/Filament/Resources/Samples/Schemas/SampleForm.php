<?php

namespace App\Filament\Resources\Samples\Schemas;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SampleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات العينة')
                    ->columns(2)
                    ->schema([
                        Select::make('establishment_id')
                            ->label('المنشأة')
                            ->relationship('establishment', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('sample_number')
                            ->label('رقم العينة')
                            ->disabled()
                            ->dehydrated(fn (string $operation): bool => $operation !== 'create')
                            ->placeholder('يُولَّد تلقائيًا')
                            ->helperText('رقم تسلسلي يُنشأ تلقائيًا عند الحفظ'),
                        DatePicker::make('sample_date')
                            ->label('تاريخ أخذ العينة')
                            ->required(),
                        TextInput::make('water_usage')
                            ->label('الاستخدام المائي (م³)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.0001)
                            ->required()
                            ->helperText('يُضرب تلقائيًا في معامل الصرف 80% داخل محرك الحساب'),
                        Select::make('sample_type')
                            ->label('نوع العينة')
                            ->options(collect(SampleType::cases())->mapWithKeys(fn (SampleType $case) => [$case->value => $case->getLabel()]))
                            ->default(SampleType::Regular->value)
                            ->required()
                            ->helperText('المركبة: رسم جمع ثابت — العادية: تبعًا لموقع المنشأة'),
                        TextInput::make('collected_by')
                            ->label('جُمعت بواسطة')
                            ->maxLength(150),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(collect(SampleStatus::cases())->mapWithKeys(fn (SampleStatus $case) => [$case->value => $case->getLabel()]))
                            ->default(SampleStatus::Pending->value)
                            ->required(),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('المرفقات')
                    ->schema([
                        FileUpload::make('lab_report_image')
                            ->label('تقرير المعمل')
                            ->image()
                            ->directory('lab-reports')
                            ->nullable()
                            ->helperText('صورة أو ملف تقرير التحليل الصادر عن المعمل'),
                    ]),
            ]);
    }
}
