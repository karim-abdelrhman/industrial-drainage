<?php

namespace App\Filament\Resources\Pollutants\RelationManagers;

use App\Enums\ActivityType;
use App\Filament\Resources\ViolationRules\ViolationRuleResource;
use App\Models\ViolationRule;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ViolationRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'violationRules';

    protected static ?string $title = 'قواعد المخالفات';

    protected static ?string $modelLabel = 'قاعدة مخالفة';

    public function form(Schema $schema): Schema
    {
        $ownerRecord = $this->getOwnerRecord();

        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        Select::make('activity_type')
                            ->label('نوع النشاط')
                            ->options(collect(ActivityType::cases())->mapWithKeys(fn (ActivityType $c) => [$c->value => $c->getLabel()]))
                            ->required(),
                        TextInput::make('from')
                            ->label('الحد الأدنى')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('to')
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

                                        $query = ViolationRule::query()
                                            ->where('pollutant_id', $ownerRecord->id)
                                            ->where('activity_type', $activityType)
                                            ->where('min_value', '<', $maxValue ?? PHP_INT_MAX)
                                            ->where(fn ($q) => $q
                                                ->whereNull('max_value')
                                                ->orWhere('max_value', '>', $minValue)
                                            );

                                        if ($record?->id) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        if ($query->exists()) {
                                            $fail('يوجد تداخل في نطاق القيم مع قاعدة أخرى لنفس الملوث ونوع النشاط.');
                                        }
                                    },
                                ]
                            ),
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
                TextColumn::make('from')
                    ->label('من')
                    ->numeric(),
                TextColumn::make('to')
                    ->label('إلى')
                    ->numeric()
                    ->placeholder('مفتوح'),
                TextColumn::make('tiers_count')
                    ->label('عدد المراحل')
                    ->counts('tiers')
                    ->badge()
                    ->color('info'),
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
                Action::make('manageTiers')
                    ->label('المراحل')
                    ->icon(Heroicon::OutlinedListBullet)
                    ->url(fn (ViolationRule $record): string => ViolationRuleResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
