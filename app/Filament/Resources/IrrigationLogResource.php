<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationLogResource\Pages;
use App\Filament\Resources\IrrigationLogResource\RelationManagers;
use App\Models\IrrigationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IrrigationLogResource extends Resource
{
    protected static ?string $model = IrrigationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('irrigation_control_id')
                    ->relationship('irrigationControl', 'id')
                    ->required(),
                Forms\Components\Select::make('irrigation_schedule_id')
                    ->relationship('irrigationSchedule', 'id'),
                Forms\Components\TextInput::make('action')
                    ->required(),
                Forms\Components\TextInput::make('trigger_type')
                    ->required(),
                Forms\Components\TextInput::make('triggered_by')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('started_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ended_at'),
                Forms\Components\TextInput::make('duration_seconds')
                    ->numeric(),
                Forms\Components\TextInput::make('water_flow_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('total_water_used')
                    ->numeric(),
                Forms\Components\TextInput::make('sensor_data_snapshot'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('error_message')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('irrigationControl.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('irrigationSchedule.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action'),
                Tables\Columns\TextColumn::make('trigger_type'),
                Tables\Columns\TextColumn::make('triggered_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('water_flow_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_water_used')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
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
            'index' => Pages\ListIrrigationLogs::route('/'),
            'create' => Pages\CreateIrrigationLog::route('/create'),
            'edit' => Pages\EditIrrigationLog::route('/{record}/edit'),
        ];
    }
}
