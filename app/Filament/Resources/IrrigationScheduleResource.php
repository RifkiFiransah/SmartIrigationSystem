<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationScheduleResource\Pages;
use App\Filament\Resources\IrrigationScheduleResource\RelationManagers;
use App\Models\IrrigationSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IrrigationScheduleResource extends Resource
{
    protected static ?string $model = IrrigationSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('schedule_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('irrigation_control_id')
                    ->relationship('irrigationControl', 'id')
                    ->required(),
                Forms\Components\TextInput::make('schedule_type')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('duration_minutes')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('days_of_week'),
                Forms\Components\TextInput::make('trigger_conditions'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('is_enabled')
                    ->required(),
                Forms\Components\DateTimePicker::make('last_run_at'),
                Forms\Components\DateTimePicker::make('next_run_at'),
                Forms\Components\TextInput::make('run_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schedule_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('irrigationControl.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule_type'),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_run_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_run_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('run_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIrrigationSchedules::route('/'),
            'create' => Pages\CreateIrrigationSchedule::route('/create'),
            'edit' => Pages\EditIrrigationSchedule::route('/{record}/edit'),
        ];
    }
}
