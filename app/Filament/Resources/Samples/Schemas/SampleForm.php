<?php

namespace App\Filament\Resources\Samples\Schemas;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use App\Models\Pollutant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SampleForm
{
    public static function configure(Schema $schema, bool $includeEstablishment = true): Schema
    {
        $details = [];

        if ($includeEstablishment) {
            $details[] = Select::make('establishment_id')
                ->label('المنشأة')
                ->relationship('establishment', 'name')
                ->searchable()
                ->preload()
                ->required();
        }

        $details = [
            ...$details,
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
        ];

        return $schema
            ->columns(1)
            ->components([
                Grid::make(4)
                    ->schema([
                        Section::make('بيانات العينة')
                            ->columns(2)
                            ->columnSpan(3)
                            ->schema($details),
                        Section::make('صورة تقرير المعمل')
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('lab_report_image')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imagePreviewHeight('16rem')
                                    ->directory('lab-reports')
                                    ->nullable()
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('اختياري'),
                            ]),
                    ]),
                self::readingsSection(),
            ]);
    }

    public static function readingsSection(): Section
    {
        return Section::make('قراءات الملوثات')
            ->description('أدخل نتائج التحليل لكل ملوث. لا يمكن تكرار نفس الملوث أكثر من مرة.')
            ->schema([
                Repeater::make('readings')
                    ->relationship('readings')
                    ->hiddenLabel()
                    ->schema([
                        Select::make('pollutant_id')
                            ->label('الملوث')
                            ->options(fn (): array => self::pollutantOptions())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('detected_value')
                            ->label('القيمة المرصودة')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->addActionLabel('إضافة ملوث')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function pollutantOptions(): array
    {
        return Pollutant::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(
                fn (Pollutant $pollutant): array => [
                    $pollutant->id => "{$pollutant->name} ({$pollutant->code}) — {$pollutant->unit}",
                ]
            )
            ->all();
    }
}
