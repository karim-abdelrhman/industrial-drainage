<?php

namespace App\Filament\Resources\Violations\Tables;

use App\Enums\ViolationStatus;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Establishment;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViolationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('المنشأة')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->weight('medium'),
                TextColumn::make('activity_type')
                    ->label('نوع النشاط')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('active_violations_count')
                    ->label('مخالفات نشطة')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('violations_count')
                    ->label('إجمالي المخالفات')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('pollutants')
                    ->label('الملوثات')
                    ->wrap()
                    ->state(fn (Establishment $record): string => $record->violations
                        ->pluck('pollutant.name')
                        ->filter()
                        ->unique()
                        ->values()
                        ->implode('، ') ?: '—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة المخالفة')
                    ->options(collect(ViolationStatus::cases())->mapWithKeys(
                        fn (ViolationStatus $case) => [$case->value => $case->getLabel()]
                    ))
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'violations',
                            fn (Builder $violations): Builder => $violations->where('status', $data['value'])
                        );
                    }),
            ])
            ->defaultSort('active_violations_count', 'desc')
            ->striped()
            ->recordTitle(fn (Establishment $record): string => $record->name)
            ->recordUrl(fn (Establishment $record): string => ViolationResource::getUrl('establishment', ['record' => $record]))
            ->recordActions([
                ViewAction::make()
                    ->label('عرض المخالفات')
                    ->url(fn (Establishment $record): string => ViolationResource::getUrl('establishment', ['record' => $record])),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد منشآت عليها مخالفات')
            ->emptyStateDescription('ستظهر المنشآت هنا عند خروج قراءة عن الحدود المسموحة.');
    }
}
