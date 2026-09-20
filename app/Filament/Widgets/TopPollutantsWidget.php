<?php

namespace App\Filament\Widgets;

use App\Models\Pollutant;
use App\Models\Violation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopPollutantsWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        $totalViolations = Violation::query()->count();

        return $table
            ->heading('أكثر الملوثات تسبباً في المخالفات')
            ->description('أعلى خمس ملوثات حسب عدد المخالفات')
            ->query(
                fn (): Builder => Pollutant::query()
                    ->withCount('violations')
                    ->orderByDesc('violations_count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('الملوث')
                    ->description(fn (Pollutant $record): string => $record->code),
                TextColumn::make('violations_count')
                    ->label('عدد المخالفات')
                    ->alignEnd()
                    ->badge()
                    ->color(fn (int $state): string => $state > 5 ? 'danger' : ($state > 2 ? 'warning' : 'gray')),
                TextColumn::make('share')
                    ->label('النسبة')
                    ->state(function (Pollutant $record) use ($totalViolations): string {
                        if ($totalViolations === 0) {
                            return '—';
                        }

                        return number_format(((int) $record->violations_count / $totalViolations) * 100, 1).'%';
                    })
                    ->alignEnd(),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد مخالفات حسب الملوث')
            ->emptyStateDescription('ستظهر النسب هنا بعد تسجيل المخالفات.');
    }
}
