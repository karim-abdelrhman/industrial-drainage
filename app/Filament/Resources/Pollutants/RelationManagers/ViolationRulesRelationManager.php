<?php

namespace App\Filament\Resources\Pollutants\RelationManagers;

use App\Filament\Resources\ViolationRules\ViolationRuleResource;
use App\Models\ViolationRule;
use App\Support\InclusiveBoundToggles;
use App\Support\NumericInterval;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ViolationRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'violationRules';

    protected static ?string $title = 'قواعد المخالفة';

    protected static ?string $modelLabel = 'قاعدة مخالفة';

    public function form(Schema $schema): Schema
    {
        $ownerRecord = $this->getOwnerRecord();

        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        Group::make([
                            TextInput::make('from')
                                ->label('الحد الأدنى')
                                ->numeric()
                                ->required(),
                            InclusiveBoundToggles::lower('from_inclusive')->default(true),
                        ]),
                        Group::make([
                            TextInput::make('to')
                                ->label('الحد الأقصى (فارغ = مفتوح)')
                                ->numeric()
                                ->rules(
                                    fn (Get $get, ?Model $record): array => [
                                        function (string $_attribute, mixed $value, Closure $fail) use ($get, $record, $ownerRecord): void {
                                            $minValue = (float) ($get('from') ?? 0);

                                            if ($value !== null && $value !== '' && (float) $value < $minValue) {
                                                $fail('يجب أن يكون الحد الأقصى أكبر من أو يساوي الحد الأدنى.');

                                                return;
                                            }

                                            $candidate = new NumericInterval(
                                                $minValue,
                                                (bool) $get('from_inclusive'),
                                                ($value !== null && $value !== '') ? (float) $value : null,
                                                (bool) $get('to_inclusive'),
                                            );

                                            $overlaps = ViolationRule::query()
                                                ->where('pollutant_id', $ownerRecord->id)
                                                ->when($record?->id, fn ($query) => $query->where('id', '!=', $record->id))
                                                ->get()
                                                ->contains(fn (ViolationRule $rule) => $rule->interval()->overlaps($candidate));

                                            if ($overlaps) {
                                                $fail('يوجد تداخل في نطاق القيم مع قاعدة أخرى لنفس الملوث.');
                                            }
                                        },
                                    ]
                                ),
                            InclusiveBoundToggles::upper('to_inclusive')->default(false),
                        ]),
                        TextInput::make('duration_days')
                            ->label('مهلة توفيق الأوضاع (أيام)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('مدة كل مرحلة قبل الانتقال للتالية'),
                    ]),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('from')
            ->defaultSort('from')
            ->columns([
                TextColumn::make('from')
                    ->label('من')
                    ->formatStateUsing(fn ($state, ViolationRule $record): string => InclusiveBoundToggles::formatLower($state, $record->from_inclusive)),
                TextColumn::make('to')
                    ->label('إلى')
                    ->formatStateUsing(fn ($state, ViolationRule $record): string => InclusiveBoundToggles::formatUpper($state, $record->to_inclusive)),
                TextColumn::make('duration_days')
                    ->label('مهلة الأوضاع (يوم)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tiers_count')
                    ->label('عدد المراحل')
                    ->counts('tiers')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([])
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
