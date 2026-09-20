<?php

namespace App\Filament\Resources\Violations\Tables;

use App\Enums\ViolationStatus;
use App\Filament\Resources\Violations\ViolationResource;
use App\Models\Violation;
use App\Support\InclusiveBoundToggles;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ViolationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('establishment.name')
                    ->label('المنشأة')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('pollutant.name')
                    ->label('الملوث')
                    ->searchable()
                    ->description(fn (Violation $record): ?string => $record->pollutant?->code),
                TextColumn::make('detected_value')
                    ->label('التركيز')
                    ->numeric(4)
                    ->alignEnd(),
                TextColumn::make('violationRule.from')
                    ->label('نطاق القاعدة')
                    ->formatStateUsing(function ($state, Violation $record): string {
                        $rule = $record->violationRule;

                        if ($rule === null) {
                            return '—';
                        }

                        return InclusiveBoundToggles::formatLower($rule->from, (bool) $rule->from_inclusive)
                            .' — '
                            .InclusiveBoundToggles::formatUpper($rule->to, (bool) $rule->to_inclusive);
                    }),
                TextColumn::make('current_tier')
                    ->label('المستوى')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => 'المستوى '.$state)
                    ->color(fn ($state): string => match ((int) $state) {
                        1 => 'warning',
                        2 => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('establishment_id')
                    ->label('المنشأة')
                    ->relationship('establishment', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('حالة المخالفة')
                    ->options(collect(ViolationStatus::cases())->mapWithKeys(fn (ViolationStatus $case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('pollutant_id')
                    ->label('الملوث')
                    ->relationship('pollutant', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('start_date', 'desc')
            ->striped()
            ->recordUrl(fn (Violation $record): string => ViolationResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد مخالفات نشطة')
            ->emptyStateDescription('ستظهر المخالفات هنا عند خروج قراءة عن الحدود المسموحة.');
    }
}
