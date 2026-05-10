<?php

namespace App\Filament\Widgets;

use App\Models\Pollutant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopPollutantsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->heading('أكثر الملوثات مخالفةً')
            ->query(
                fn (): Builder => Pollutant::query()
                    ->withCount('violations')
                    ->orderByDesc('violations_count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('الملوث')
                    ->description(fn (Pollutant $record) => $record->code)
                    ->searchable(),

                TextColumn::make('unit')
                    ->label('الوحدة')
                    ->alignCenter(),

                TextColumn::make('violations_count')
                    ->label('عدد المخالفات')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state) => $state > 5 ? 'danger' : ($state > 2 ? 'warning' : 'gray')),
            ])
            ->paginated(false);
    }
}
