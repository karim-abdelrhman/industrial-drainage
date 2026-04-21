<?php

namespace App\Filament\Resources\Establishments\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Enums\SampleStatus;
use App\Models\Invoice;
use App\Models\Pollutant;
use App\Models\Sample;
use App\Services\SampleCalculationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
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
                        ->default(fn () => 'SMP-'.now()->format('Ymd').'-'.str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT))
                        ->required(),
                    DatePicker::make('sample_date')
                        ->label('تاريخ أخذ العينة')
                        ->required(),
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
                        ->label('')
                        ->relationship('readings')
                        ->schema([
                            Select::make('pollutant_id')
                                ->label('الملوث')
                                ->options(
                                    Pollutant::where('is_active', true)
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->required(),
                            TextInput::make('detected_value')
                                ->label('القيمة المكتشفة')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('إضافة ملوث')
                        ->columnSpanFull(),
                ]),
        ]);
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
                TextColumn::make('readings_count')
                    ->label('عدد الملوثات')
                    ->counts('readings')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (SampleStatus $state) => $state->getColor()),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('preview_calculation')
                    ->label('معاينة الحساب')
                    ->icon(Heroicon::OutlinedCalculator)
                    ->color('info')
                    ->modalHeading('تفاصيل الحساب')
                    ->modalContent(function (Sample $record): HtmlString {
                        $service = app(SampleCalculationService::class);
                        $result = $service->calculateSample($record);

                        return new HtmlString($this->renderCalculationTable($result));
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),

                Action::make('generate_invoice')
                    ->label('إنشاء فاتورة')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('إنشاء فاتورة')
                    ->modalDescription('هل تريد إنشاء فاتورة مسودة بناءً على حسابات هذه العينة؟')
                    ->action(function (Sample $record): void {
                        $service = app(SampleCalculationService::class);
                        $result = $service->calculateSample($record);

                        if (empty($result['lines']) || $result['total'] <= 0) {
                            return;
                        }

                        DB::transaction(function () use ($record, $result) {
                            $invoice = Invoice::create([
                                'establishment_id' => $record->establishment_id,
                                'sample_id' => $record->id,
                                'billing_month' => $record->sample_date->startOfMonth()->toDateString(),
                                'status' => InvoiceStatus::Draft,
                                'total_amount' => $result['total'],
                            ]);

                            foreach ($result['lines'] as $line) {
                                if ($line['rule_id'] === null) {
                                    continue;
                                }

                                $invoice->items()->create([
                                    'violation_id' => null,
                                    'pollutant_id' => $line['pollutant_id'],
                                    'violation_rule_id' => $line['rule_id'],
                                    'tier_order' => $line['tier_order'] ?? 1,
                                    'price_per_unit' => $line['price_per_unit'],
                                    'detected_value' => $line['detected_value'],
                                    'amount' => $line['subtotal'],
                                ]);
                            }
                        });
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private function renderCalculationTable(array $result): string
    {
        $rows = '';
        foreach ($result['lines'] as $line) {
            $tier = $line['tier_order'] !== null ? 'المرحلة '.$line['tier_order'] : '—';
            $price = $line['price_per_unit'] !== null ? number_format($line['price_per_unit'], 2) : '—';
            $subtotal = number_format($line['subtotal'], 2);

            $rows .= "<tr>
                <td style='padding:8px 12px;border-bottom:1px solid #e5e7eb'>{$line['pollutant_name']}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e5e7eb'>{$line['detected_value']} {$line['unit']}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e5e7eb'>{$tier}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e5e7eb'>{$price}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e5e7eb;font-weight:600'>{$subtotal} ج.م</td>
            </tr>";
        }

        $total = number_format($result['total'], 2);

        return "<div style='direction:rtl'>
            <table style='width:100%;border-collapse:collapse;font-size:14px'>
                <thead>
                    <tr style='background:#f3f4f6'>
                        <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #d1d5db'>الملوث</th>
                        <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #d1d5db'>القيمة المكتشفة</th>
                        <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #d1d5db'>المرحلة</th>
                        <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #d1d5db'>السعر/وحدة</th>
                        <th style='padding:10px 12px;text-align:right;border-bottom:2px solid #d1d5db'>الإجمالي الفرعي</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
                <tfoot>
                    <tr style='background:#f9fafb'>
                        <td colspan='4' style='padding:10px 12px;font-weight:700;text-align:right'>الإجمالي</td>
                        <td style='padding:10px 12px;font-weight:700;color:#16a34a'>{$total} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>";
    }
}
