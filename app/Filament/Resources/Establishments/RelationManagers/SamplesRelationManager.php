<?php

namespace App\Filament\Resources\Establishments\RelationManagers;

use App\Enums\SampleStatus;
use App\Enums\SampleType;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Samples\Pages\EditSample;
use App\Models\Invoice;
use App\Models\Pollutant;
use App\Models\Sample;
use App\Services\SampleCalculationService;
use App\Services\SampleEvaluationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SamplesRelationManager extends RelationManager
{
    protected static string $relationship = 'samples';

    protected static ?string $title = 'العينات';

    protected static ?string $modelLabel = 'عينة';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات العينة')
                ->schema([
                    TextInput::make('sample_number')
                        ->label('رقم العينة')
                        ->required(),
                    DatePicker::make('sample_date')
                        ->label('تاريخ أخذ العينة')
                        ->required(),
                    TextInput::make('water_usage')
                        ->label('الاستخدام المائي (م³)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.0001)
                        ->required(),
                    Select::make('sample_type')
                        ->label('نوع العينة')
                        ->options(collect(SampleType::cases())->mapWithKeys(fn (SampleType $c) => [$c->value => $c->getLabel()]))
                        ->default(SampleType::Regular->value)
                        ->required()
                        ->helperText('المركبة: 1400 ج.م — العادية: تبعًا لموقع المنشأة'),
                    TextInput::make('collected_by')
                        ->label('جُمعت بواسطة')
                        ->maxLength(150),
                    FileUpload::make('lab_report_image')
                        ->label('صورة تقرير المعمل')
                        ->image()
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('قراءات الملوثات')
                ->schema([
                    Repeater::make('readings')
                        ->relationship('readings')
                        ->schema([
                            Select::make('pollutant_id')
                                ->label('الملوث')
                                ->options(
                                    Pollutant::where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(
                                            fn (Pollutant $p) => [
                                                $p->id => "({$p->code}) - {$p->unit}",
                                            ]
                                        )
                                )
                                ->searchable()
                                ->required(),
                            TextInput::make('detected_value')
                                ->label('القيمة المرصودة')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('إضافة ملوث')
                        ->columnSpanFull(),
                ]),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sample_number')
                    ->label('رقم العينة')
                    ->searchable(),
                TextColumn::make('sample_date')
                    ->label('تاريخ العينة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('water_usage')
                    ->label('الاستخدام المائي (م³)')
                    ->numeric(4),
                TextColumn::make('readings_count')
                    ->label('الملوثات')
                    ->counts('readings')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->defaultSort('sample_date', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('preview_calculation')
                    ->label('معاينة الحساب')
                    ->icon(Heroicon::OutlinedCalculator)
                    ->color('info')
                    ->modalHeading('معاينة تفاصيل الحساب')
                    ->modalContent(function (Sample $record): HtmlString {
                        if (! $record->water_usage || $record->readings()->count() === 0) {
                            return new HtmlString(
                                '<p class="text-center text-gray-500 py-6">يجب إدخال الاستخدام المائي وإضافة قراءات الملوثات أولًا.</p>'
                            );
                        }
                        $result = app(SampleCalculationService::class)->calculateSample($record);

                        return new HtmlString(EditSample::buildBreakdownHtml($result));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                Action::make('evaluate')
                    ->label('تقييم')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('success')
                    ->visible(fn (Sample $record) => $record->status === SampleStatus::Pending)
                    ->requiresConfirmation()
                    ->modalHeading('تقييم العينة')
                    ->modalDescription('سيتم تقييم جميع قراءات العينة وإنشاء الفاتورة تلقائيًا. لا يمكن التراجع عن هذا الإجراء.')
                    ->modalSubmitActionLabel('تأكيد التقييم')
                    ->action(function (Sample $record): void {
                        $invoice = app(SampleEvaluationService::class)->evaluate($record);

                        Notification::make()
                            ->title('تم تقييم العينة وإنشاء الفاتورة')
                            ->success()
                            ->send();

                        $this->redirect(InvoiceResource::getUrl('edit', ['record' => $invoice->id]));
                    }),

                Action::make('view_invoice')
                    ->label('الفاتورة')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('gray')
                    ->visible(fn (Sample $record) => $record->status === SampleStatus::Evaluated)
                    ->url(function (Sample $record): string {
                        $invoice = Invoice::where('sample_id', $record->id)->first();

                        return $invoice
                            ? InvoiceResource::getUrl('edit', ['record' => $invoice->id])
                            : '#';
                    }),

                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Sample $record) => $record->status === SampleStatus::Pending),
            ]);
    }
}
