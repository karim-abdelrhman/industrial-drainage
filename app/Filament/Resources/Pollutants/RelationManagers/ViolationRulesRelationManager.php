<?php

namespace App\Filament\Resources\Pollutants\RelationManagers;

use App\Filament\Resources\ViolationRules\ViolationRuleResource;
use App\Models\ViolationRule;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
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

    protected static ?string $title = 'أعباء المعالجة';

    protected static ?string $modelLabel = 'اعباء';

    public function form(Schema $schema): Schema
    {
        $ownerRecord = $this->getOwnerRecord();

        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('from')
                            ->label('الحد الأدنى')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('to')
                            ->label('الحد الأقصى (فارغ = مفتوح)')
                            ->numeric()
                            ->minValue(0)
                            ->rules(
                                fn (Get $get, ?Model $record): array => [
                                    function (string $_attribute, mixed $value, Closure $fail) use ($get, $record, $ownerRecord): void {
                                        $minValue = (float) ($get('from') ?? 0);

                                        if ($value !== null && $value !== '' && (float) $value <= $minValue) {
                                            $fail('يجب أن يكون الحد الأقصى أكبر من الحد الأدنى.');

                                            return;
                                        }

                                        $maxValue = ($value !== null && $value !== '') ? (float) $value : null;

                                        $query = ViolationRule::query()
                                            ->where('pollutant_id', $ownerRecord->id)
                                            ->where('from', '<', $maxValue ?? PHP_INT_MAX)
                                            ->where(fn ($q) => $q
                                                ->whereNull('to')
                                                ->orWhere('to', '>', $minValue)
                                            );

                                        if ($record?->id) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        if ($query->exists()) {
                                            $fail('يوجد تداخل في نطاق القيم مع قاعدة أخرى لنفس الملوث.');
                                        }
                                    },
                                ]
                            ),
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
                    ->label('من (مليجرام/لتر)')
                    ->numeric(),
                TextColumn::make('to')
                    ->label('إلى (مليجرام/لتر)')
                    ->numeric()
                    ->placeholder('مفتوح'),
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
