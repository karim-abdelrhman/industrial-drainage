<?php

namespace App\Filament\Widgets;

use App\Enums\ViolationStatus;
use App\Filament\Resources\Establishments\EstablishmentResource;
use App\Models\Establishment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopViolatingEstablishmentsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->heading('أكثر المنشآت مخالفة')
            ->description('أعلى خمس منشآت حسب عدد المخالفات')
            ->query(
                fn (): Builder => Establishment::query()
                    ->withCount('violations')
                    ->withCount(['violations as active_violations_count' => fn (Builder $query) => $query->where('status', ViolationStatus::Active)])
                    ->withMax('violations', 'start_date')
                    ->orderByDesc('violations_count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('المنشأة')
                    ->limit(28)
                    ->url(fn (Establishment $record): string => EstablishmentResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('violations_count')
                    ->label('عدد المخالفات')
                    ->alignEnd()
                    ->badge()
                    ->color(fn (int $state): string => $state > 5 ? 'danger' : ($state > 2 ? 'warning' : 'gray')),
                TextColumn::make('violations_max_start_date')
                    ->label('آخر مخالفة')
                    ->date('Y-m-d')
                    ->placeholder('—'),
                TextColumn::make('active_violations_count')
                    ->label('الحالة')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? 'نشطة' : 'مستقر')
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد مخالفات مسجّلة')
            ->emptyStateDescription('ستظهر هنا المنشآت الأعلى مخالفة عند توفر البيانات.');
    }
}
