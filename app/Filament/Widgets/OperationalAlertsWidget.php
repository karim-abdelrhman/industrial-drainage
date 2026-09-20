<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Enums\SampleStatus;
use App\Enums\ViolationStatus;
use App\Filament\Resources\Establishments\EstablishmentResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Samples\SampleResource;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Establishment;
use App\Models\Invoice;
use App\Models\Sample;
use App\Models\Violation;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class OperationalAlertsWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.operational-alerts';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $escalations = Violation::query()
            ->where('violations.status', ViolationStatus::Active)
            ->join('violation_rules', 'violations.violation_rule_id', '=', 'violation_rules.id')
            ->select(
                'violations.id',
                'violations.establishment_id',
                'violations.pollutant_id',
                'violations.current_tier',
                DB::raw('(CAST(violations.current_tier AS SIGNED) * CAST(violation_rules.duration_days AS SIGNED) - DATEDIFF(CURDATE(), violations.start_date)) as days_until_next')
            )
            ->whereRaw(
                '(CAST(violations.current_tier AS SIGNED) * CAST(violation_rules.duration_days AS SIGNED) - DATEDIFF(CURDATE(), violations.start_date)) BETWEEN 0 AND 6'
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('violation_rule_tiers')
                    ->whereColumn('violation_rule_tiers.violation_rule_id', 'violations.violation_rule_id')
                    ->whereColumn('violation_rule_tiers.tier_order', '>', 'violations.current_tier');
            })
            ->with(['establishment:id,name', 'pollutant:id,name,code'])
            ->orderBy('days_until_next')
            ->limit(5)
            ->get();

        $overdueInvoices = Invoice::query()
            ->where('status', InvoiceStatus::Overdue)
            ->with('establishment:id,name')
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'establishment_id', 'total_amount', 'due_date']);

        $pendingSamples = Sample::query()
            ->where('status', SampleStatus::Pending)
            ->with('establishment:id,name')
            ->orderBy('sample_date')
            ->limit(5)
            ->get(['id', 'establishment_id', 'sample_number', 'sample_date']);

        $repeatOffenders = Establishment::query()
            ->withCount(['violations as active_violations_count' => fn ($query) => $query->where('status', ViolationStatus::Active)])
            ->having('active_violations_count', '>=', 3)
            ->orderByDesc('active_violations_count')
            ->limit(5)
            ->get(['id', 'name']);

        $alerts = [];

        foreach ($escalations as $violation) {
            $days = (int) $violation->days_until_next;
            $alerts[] = [
                'tone' => $days <= 2 ? 'urgent' : 'watch',
                'tone_label' => $days <= 2 ? 'عاجل' : 'تحذير',
                'title' => 'مخالفة تقترب من مستوى مالي أعلى',
                'meta' => ($violation->establishment?->name ?? 'منشأة').' — '.($violation->pollutant?->name ?? 'ملوث').' — متبقٍ '.$days.' أيام',
                'url' => ViolationResource::getUrl('view', ['record' => $violation->id]),
            ];
        }

        foreach ($overdueInvoices as $invoice) {
            $alerts[] = [
                'tone' => 'overdue',
                'tone_label' => 'متأخر',
                'title' => 'مطالبة متأخرة',
                'meta' => ($invoice->establishment?->name ?? 'منشأة').' — استحقاق '.optional($invoice->due_date)->format('Y-m-d'),
                'url' => InvoiceResource::getUrl('view', ['record' => $invoice->id]),
            ];
        }

        foreach ($pendingSamples as $sample) {
            $alerts[] = [
                'tone' => 'watch',
                'tone_label' => 'تحذير',
                'title' => 'عينة لم يتم تقييمها',
                'meta' => ($sample->establishment?->name ?? 'منشأة').' — رقم '.$sample->sample_number,
                'url' => SampleResource::getUrl('view', ['record' => $sample->id]),
            ];
        }

        foreach ($repeatOffenders as $establishment) {
            $alerts[] = [
                'tone' => 'urgent',
                'tone_label' => 'عاجل',
                'title' => 'منشأة لديها مخالفات متكررة',
                'meta' => $establishment->name.' — '.$establishment->active_violations_count.' مخالفات نشطة',
                'url' => EstablishmentResource::getUrl('edit', ['record' => $establishment->id]),
            ];
        }

        return [
            'alerts' => $alerts,
        ];
    }
}
