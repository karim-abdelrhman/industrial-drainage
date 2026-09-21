<?php

namespace App\Filament\Resources\Pollutants\RelationManagers;

use App\Enums\CustomerZone;
use App\Models\PollutantLimit;
use App\Support\InclusiveBoundToggles;
use App\Support\NumericInterval;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
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

    protected static ?string $title = 'حدود المطابقة';

    protected static ?string $modelLabel = 'حد المطابقة';

    public function form(Schema $schema): Schema
    {
        $ownerRecord = $this->getOwnerRecord();

        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    Select::make('customer_zone')
                        ->label('منطقة العميل')
                        ->options(collect(CustomerZone::cases())->mapWithKeys(fn (CustomerZone $c) => [$c->value => $c->getLabel()]))
                        ->required(),

                    Group::make([
                        TextInput::make('min_value')
                            ->label('الحد الأدنى')
                            ->numeric()
                            ->required(),
                        InclusiveBoundToggles::lower('min_inclusive')->default(true),
                    ]),
                    Group::make([
                        TextInput::make('max_value')
                            ->label('الحد الأقصى (فارغ = مفتوح)')
                            ->numeric()
                            ->rules(
                                fn (Get $get, ?Model $record): array => [
                                    function (string $attribute, mixed $value, Closure $fail) use ($get, $record, $ownerRecord): void {
                                        $minValue = (float) ($get('min_value') ?? 0);
                                        $customerZone = $get('customer_zone');

                                        if ($value !== null && $value !== '' && (float) $value < $minValue) {
                                            $fail('يجب أن يكون الحد الأقصى أكبر من أو يساوي الحد الأدنى.');

                                            return;
                                        }

                                        if (! $customerZone) {
                                            return;
                                        }

                                        $candidate = new NumericInterval(
                                            $minValue,
                                            (bool) $get('min_inclusive'),
                                            ($value !== null && $value !== '') ? (float) $value : null,
                                            (bool) $get('max_inclusive'),
                                        );

                                        $overlaps = PollutantLimit::query()
                                            ->where('pollutant_id', $ownerRecord->id)
                                            ->where('customer_zone', $customerZone)
                                            ->when($record?->id, fn ($query) => $query->where('id', '!=', $record->id))
                                            ->get()
                                            ->contains(fn (PollutantLimit $limit) => $limit->interval()->overlaps($candidate));

                                        if ($overlaps) {
                                            $fail('يوجد تداخل في نطاق القيم مع حد امتثال آخر لنفس الملوث ومنطقة العميل.');
                                        }
                                    },
                                ]
                            ),
                        InclusiveBoundToggles::upper('max_inclusive')->default(true),
                    ]),
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
            ->recordTitleAttribute('customer_zone')
            ->defaultSort('customer_zone')
            ->columns([
                TextColumn::make('customer_zone')
                    ->label('منطقة العميل')
                    ->badge(),
                TextColumn::make('min_value')
                    ->label('من')
                    ->formatStateUsing(fn ($state, PollutantLimit $record): string => InclusiveBoundToggles::formatLower($state, $record->min_inclusive)),
                TextColumn::make('max_value')
                    ->label('إلى')
                    ->formatStateUsing(fn ($state, PollutantLimit $record): string => InclusiveBoundToggles::formatUpper($state, $record->max_inclusive)),
                TextColumn::make('price_per_unit')
                    ->label('السعر / وحدة')
                    ->money('EGP'),
            ])
            ->filters([
                SelectFilter::make('customer_zone')
                    ->label('منطقة العميل')
                    ->options(collect(CustomerZone::cases())->mapWithKeys(fn (CustomerZone $c) => [$c->value => $c->getLabel()])),
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
