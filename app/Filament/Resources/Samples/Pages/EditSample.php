<?php

namespace App\Filament\Resources\Samples\Pages;

use App\Enums\SampleStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Samples\SampleResource;
use App\Services\SampleCalculationService;
use App\Services\SampleEvaluationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class EditSample extends EditRecord
{
    protected static string $resource = SampleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview_calculation')
                ->label('معاينة الحساب')
                ->icon(Heroicon::OutlinedCalculator)
                ->color('info')
                ->modalHeading('معاينة تفاصيل الحساب')
                ->modalContent(function (): HtmlString {
                    $record = $this->getRecord();

                    if (! $record->water_usage || $record->readings()->count() === 0) {
                        return new HtmlString(
                            '<p class="text-center text-gray-500 py-6">يجب إدخال الاستخدام المائي وإضافة قراءات الملوثات أولًا.</p>'
                        );
                    }

                    $result = app(SampleCalculationService::class)->calculateSample($record);

                    return new HtmlString(self::buildBreakdownHtml($result));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('إغلاق'),

            Action::make('evaluate')
                ->label('تقييم العينة')
                ->icon(Heroicon::OutlinedPlay)
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status === SampleStatus::Pending)
                ->requiresConfirmation()
                ->modalHeading('تقييم العينة')
                ->modalDescription('سيتم تقييم جميع قراءات العينة وإنشاء الفاتورة تلقائيًا. لا يمكن التراجع عن هذا الإجراء.')
                ->modalSubmitActionLabel('تأكيد التقييم')
                ->action(function (): void {
                    $invoice = app(SampleEvaluationService::class)->evaluate($this->getRecord());

                    Notification::make()
                        ->title('تم تقييم العينة وإنشاء الفاتورة')
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('edit', ['record' => $invoice->id]));
                }),

            DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->status === SampleStatus::Pending),
        ];
    }

    public static function buildBreakdownHtml(array $result): string
    {
        $resultLabels = [
            'compliant' => 'مطابق',
            'violation' => 'مخالفة',
            'unclassified' => 'غير مصنف',
        ];

        $resultColors = [
            'compliant' => '#16a34a',
            'violation' => '#dc2626',
            'unclassified' => '#9ca3af',
        ];

        // --- Pollutant charge rows ---
        $rows = '';
        foreach ($result['lines'] as $line) {
            $evalLabel = $resultLabels[$line['evaluation_result']] ?? $line['evaluation_result'];
            $evalColor = $resultColors[$line['evaluation_result']] ?? '#6b7280';
            $tier = $line['tier_order'] !== null ? 'المرحلة '.$line['tier_order'] : '—';
            $price = number_format((float) $line['price_per_unit'], 2);
            $amount = number_format((float) $line['amount'], 2);
            $value = number_format((float) $line['detected_value'], 4).' '.$line['unit'];

            $rows .= "
                <tr>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb'>{$line['pollutant_name']}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;text-align:center'>{$value}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;text-align:center'>
                        <span style='color:{$evalColor};font-weight:600'>{$evalLabel}</span>
                    </td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;text-align:center'>{$tier}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;text-align:center'>{$price} ج.م</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;text-align:center;font-weight:600'>{$amount} ج.م</td>
                </tr>";
        }

        $pollutantSubtotal = number_format((float) ($result['pollutant_subtotal'] ?? array_sum(array_column($result['lines'], 'amount'))), 2);

        $rows .= "
            <tr style='background:#f9fafb'>
                <td colspan='5' style='padding:8px 14px;font-weight:600;text-align:right;color:#374151'>إجمالي رسوم الملوثات</td>
                <td style='padding:8px 14px;font-weight:700;text-align:center;color:#1d4ed8'>{$pollutantSubtotal} ج.م</td>
            </tr>";

        // --- Fee rows ---
        $feeRows = '';
        foreach ($result['fees'] ?? [] as $fee) {
            $feeAmount = number_format((float) $fee['amount'], 2);
            $feeNote = htmlspecialchars($fee['notes'] ?? '');
            $feeLabel = htmlspecialchars($fee['label'] ?? '');
            $feeRows .= "
                <tr>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb'>{$feeLabel}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px'>{$feeNote}</td>
                    <td style='padding:8px 14px;border-bottom:1px solid #e5e7eb;text-align:center;font-weight:600'>{$feeAmount} ج.م</td>
                </tr>";
        }

        $grandTotal = number_format((float) ($result['grand_total'] ?? 0), 2);

        return "
            <div style='direction:rtl;font-family:inherit'>

                <h3 style='margin:0 0 8px;font-size:15px;font-weight:700;color:#1f2937;padding:0 4px'>رسوم الملوثات</h3>
                <table style='width:100%;border-collapse:collapse;font-size:14px;margin-bottom:20px'>
                    <thead>
                        <tr style='background:#4b5563;color:#fff'>
                            <th style='padding:9px 14px;text-align:right;font-weight:700'>الملوث</th>
                            <th style='padding:9px 14px;text-align:center;font-weight:700'>القيمة المرصودة</th>
                            <th style='padding:9px 14px;text-align:center;font-weight:700'>التصنيف</th>
                            <th style='padding:9px 14px;text-align:center;font-weight:700'>المرحلة</th>
                            <th style='padding:9px 14px;text-align:center;font-weight:700'>سعر الوحدة</th>
                            <th style='padding:9px 14px;text-align:center;font-weight:700'>المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>{$rows}</tbody>
                </table>

                <h3 style='margin:0 0 8px;font-size:15px;font-weight:700;color:#1f2937;padding:0 4px'>الرسوم الإضافية</h3>
                <table style='width:100%;border-collapse:collapse;font-size:14px;margin-bottom:20px'>
                    <thead>
                        <tr style='background:#4b5563;color:#fff'>
                            <th style='padding:9px 14px;text-align:right;font-weight:700'>البند</th>
                            <th style='padding:9px 14px;text-align:right;font-weight:700'>تفاصيل</th>
                            <th style='padding:9px 14px;text-align:center;font-weight:700'>المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>{$feeRows}</tbody>
                </table>

                <table style='width:100%;border-collapse:collapse;font-size:15px'>
                    <tr style='background:#1d4ed8;color:#fff'>
                        <td style='padding:14px;font-weight:700;text-align:right;font-size:16px'>الإجمالي النهائي</td>
                        <td style='padding:14px;font-weight:700;text-align:center;font-size:18px'>{$grandTotal} ج.م</td>
                    </tr>
                </table>

            </div>";
    }
}
