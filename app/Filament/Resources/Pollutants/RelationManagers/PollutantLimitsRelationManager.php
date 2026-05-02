<?php

namespace App\Filament\Resources\Pollutants\RelationManagers;

use App\Enums\ActivityType;
use App\Models\PollutantLimit;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PollutantLimitsRelationManager extends RelationManager
{
    protected static string $relationship = 'limits';

    protected static ?string $title = 'حدود الامتثال';

    protected static ?string $modelLabel = 'حد امتثال';

    public function form(Schema $schema): Schema
    {
        $ownerRecord = $this->getOwnerRecord();

        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    Select::make('activity_type')
                        ->label('نوع النشاط')
                        ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()]))
                        ->required(),

                    TextInput::make('min_value')
                        ->label('الحد الأدنى')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    TextInput::make('max_value')
                        ->label('الحد الأقصى (فارغ = مفتوح)')
                        ->numeric()
                        ->minValue(0)
                        ->rules(
                            fn (Get $get, ?Model $record): array => [
                                function (string $attribute, mixed $value, Closure $fail) use ($get, $record, $ownerRecord): void {
                                    $minValue = (float) ($get('min_value') ?? 0);
                                    $activityType = $get('activity_type');

                                    if ($value !== null && $value !== '' && (float) $value <= $minValue) {
                                        $fail('يجب أن يكون الحد الأقصى أكبر من الحد الأدنى.');

                                        return;
                                    }

                                    if (! $activityType) {
                                        return;
                                    }

                                    $maxValue = ($value !== null && $value !== '') ? (float) $value : null;

                                    $query = PollutantLimit::query()
                                        ->where('pollutant_id', $ownerRecord->id)
                                        ->where('activity_type', $activityType)
                                        ->where('min_value', '<=', $maxValue ?? PHP_INT_MAX)
                                        ->where(fn ($q) => $q
                                            ->whereNull('max_value')
                                            ->orWhere('max_value', '>=', $minValue)
                                        );

                                    if ($record?->id) {
                                        $query->where('id', '!=', $record->id);
                                    }

                                    if ($query->exists()) {
                                        $fail('يوجد تداخل في نطاق القيم مع حد امتثال آخر لنفس الملوث ونوع النشاط.');
                                    }
                                },
                            ]
                        ),

                    TextInput::make('price_per_unit')
                        ->label('السعر / وحدة (ج.م)')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                ]),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('activity_type')
            ->defaultSort('activity_type')
            ->columns([
                TextColumn::make('activity_type')
                    ->label('نوع النشاط')
                    ->badge(),
                TextColumn::make('min_value')
                    ->label('الحد الأدنى')
                    ->numeric(1),
                TextColumn::make('max_value')
                    ->label('الحد الأقصى')
                    ->numeric(1)
                    ->placeholder('مفتوح'),
                TextColumn::make('price_per_unit')
                    ->label('السعر / وحدة')
                    ->money('EGP'),
            ])
            ->filters([
                SelectFilter::make('activity_type')
                    ->label('نوع النشاط')
                    ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()])),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
