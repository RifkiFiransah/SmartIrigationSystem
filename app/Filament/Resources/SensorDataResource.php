<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SensorDataResource\Pages;
use App\Filament\Resources\SensorDataResource\RelationManagers;
use App\Models\SensorData;
use DateTime;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Date;

class SensorDataResource extends Resource
{
    protected static ?string $model = SensorData::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('device_id')
                    ->label('Device ID')
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('recorded_at')
                    ->label('Recorded At')
                    ->default(fn () => Date::now())
                    ->required(),
                TextInput::make('temperature')
                    ->label('Temperature (°C)')
                    ->numeric()
                    ->required(),
                TextInput::make('humidity')
                    ->label('Humidity (%)')
                    ->numeric()
                    ->required(),
                TextInput::make('soil_moisture')
                    ->label('Soil Moisture (%)')
                    ->numeric()
                    ->required(),
                TextInput::make('water_flow')
                    ->label('Water Flow (L/min)')
                    ->numeric()
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'normal' => 'Normal',
                        'alert' => 'Alert',
                        'critical' => 'Critical',
                    ])
                    ->default('normal')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recorded_at')
                    ->label('Recorderd At')
                    ->dateTime(),
                TextColumn::make('temperature')
                    ->label('Temperature (°C)')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('humidity')
                    ->label('Humidity (%)')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('soil_moisture')
                    ->label('Soil Moisture (%)')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('water_flow')
                    ->label('Water Flow (L/min)')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Status')
            ])->defaultSort('recorded_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSensorData::route('/'),
            // 'create' => Pages\CreateSensorData::route('/create'),
            // 'edit' => Pages\EditSensorData::route('/{record}/edit'),
        ];
    }
}
