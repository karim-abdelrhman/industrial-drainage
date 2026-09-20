<?php

namespace App\Filament\Widgets;

use App\Enums\ViolationStatus;
use App\Models\Violation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EscalationAlertsWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('تصعيد المخالفات خلال 7 أيام')
            ->description('المخالفات النشطة التي ستنتقل إلى المستوى التالي خلال أسبوع')
            ->query(fn (): Builder => $this->escalationQuery())
            ->columns([
                TextColumn::make('establishment.name')
                    ->label('المنشأة')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('pollutant.name')
                    ->label('الملوث')
                    ->searchable(),

                TextColumn::make('current_tier')
                    ->label('المستوى الحالي')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state) => match ($state) {
                        1 => 'warning',
                        2 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state) => 'المستوى '.$state),

                TextColumn::make('days_until_next')
                    ->label('الأيام المتبقية')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (mixed $state) => (int) $state <= 2 ? 'danger' : 'warning')
                    ->formatStateUsing(fn (mixed $state) => (int) $state.' أيام'),

                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('days_until_next', 'asc')
            ->paginated(false)
            ->emptyStateHeading('لا توجد مخالفات قريبة من التصعيد')
            ->emptyStateDescription('لا توجد مخالفات نشطة ستتصاعد خلال الأيام السبعة القادمة.');
    }

    private function escalationQuery(): Builder
    {
        return Violation::query()
            ->where('violations.status', ViolationStatus::Active)
            ->join('violation_rules', 'violations.violation_rule_id', '=', 'violation_rules.id')
            ->select(
                'violations.*',
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
            ->with(['establishment', 'pollutant'])
            ->orderBy('days_until_next');
    }
}
