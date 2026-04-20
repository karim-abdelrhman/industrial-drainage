<?php

namespace App\Filament\Resources\Samples\RelationManagers;

use App\Models\Pollutant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'readings';

    protected static ?string $title = 'قراءات الملوثات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pollutant_id')
                    ->label('الملوث')
                    ->options(
                        Pollutant::query()
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn (Pollutant $p) => [$p->id => "{$p->name} ({$p->code}) - {$p->unit}"])
                    )
                    ->searchable()
                    ->required()
                    ->unique(
                        table: 'sample_readings',
                        column: 'pollutant_id',
                        ignorable: fn ($record) => $record,
                        modifyRuleUsing: fn ($rule) => $rule->where('sample_id', $this->getOwnerRecord()->id),
                    ),
                TextInput::make('detected_value')
                    ->label('القيمة المرصودة')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('pollutant.name')
            ->columns([
                TextColumn::make('pollutant.name')
                    ->label('الملوث'),
                TextColumn::make('pollutant.unit')
                    ->label('الوحدة'),
                TextColumn::make('detected_value')
                    ->label('القيمة')
                    ->numeric(),
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
