<?php

namespace App\Filament\Widgets;

use App\Models\Establishment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopViolatingEstablishmentsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->heading('أكثر المنشآت مخالفةً')
            ->query(
                fn (): Builder => Establishment::query()
                    ->withCount('violations')
                    ->withMax('violations', 'start_date')
                    ->orderByDesc('violations_count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('المنشأة')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('violations_count')
                    ->label('عدد المخالفات')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state) => $state > 5 ? 'danger' : ($state > 2 ? 'warning' : 'gray')),

                TextColumn::make('violations_max_start_date')
                    ->label('آخر مخالفة')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
