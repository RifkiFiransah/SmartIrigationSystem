<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SensorDataResource\Pages;
use App\Filament\Resources\SensorDataResource\RelationManagers;
use App\Models\Device;
use App\Models\SensorData;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SensorDataResource extends Resource
{
    protected static ?string $model = SensorData::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationLabel = 'Sensor Data';
    protected static ?string $modelLabel = 'Sensor Data';
    protected static ?string $pluralModelLabel = 'Sensor Data';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('device_id')
                    ->label('Device')
                    ->options(function () {
                        return Device::where('is_active', true)
                            ->get()
                            ->pluck('device_name', 'id'); // Menggunakan 'id' bukan 'device_id'
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->placeholder('Select a device'),
                    
                DateTimePicker::make('recorded_at')
                    ->label('Recorded At')
                    ->default(now())
                    ->required()
                    ->timezone('Asia/Jakarta')
                    ->displayFormat('d/m/Y H:i')
                    ->seconds(false),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('temperature')
                            ->label('Temperature (°C)')
                            ->numeric()
                            ->required()
                            ->step(0.1)
                            ->minValue(-50)
                            ->maxValue(100)
                            ->suffix('°C')
                            ->placeholder('25.5'),
                            
                        TextInput::make('humidity')
                            ->label('Humidity (%)')
                            ->numeric()
                            ->required()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->placeholder('65.0'),
                    ]),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('soil_moisture')
                            ->label('Soil Moisture (%)')
                            ->numeric()
                            ->required()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->placeholder('45.0'),
                            
                        TextInput::make('water_flow')
                            ->label('Water Flow (L/h)')
                            ->numeric()
                            ->required()
                            ->step(0.1)
                            ->minValue(0)
                            ->suffix('L/h')
                            ->placeholder('150.0'),
                    ]),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('light_intensity')
                            ->label('Light Intensity (lux)')
                            ->numeric()
                            ->step(1)
                            ->minValue(0)
                            ->suffix('lux')
                            ->placeholder('25000'),
                            
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'normal' => 'Normal',
                                'peringatan' => 'Peringatan', 
                                'kritis' => 'Kritis',
                            ])
                            ->default('normal')
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.device_name')
                    ->label('Device Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-cpu-chip'),
                    
                TextColumn::make('recorded_at')
                    ->label('Recorded At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->timezone('Asia/Jakarta')
                    ->description(fn (SensorData $record): string => 
                        $record->recorded_at->diffForHumans()
                    ),
                    
                TextColumn::make('temperature')
                    ->label('Temperature')
                    ->suffix('°C')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->color(fn ($state) => match (true) {
                        $state > 35 => 'danger',
                        $state > 30 => 'warning', 
                        $state < 10 => 'info',
                        default => 'success'
                    }),
                    
                TextColumn::make('humidity')
                    ->label('Humidity')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->color(fn ($state) => match (true) {
                        $state > 80 => 'info',
                        $state < 30 => 'warning',
                        default => 'success'
                    }),
                    
                TextColumn::make('soil_moisture')
                    ->label('Soil Moisture')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->color(fn ($state) => match (true) {
                        $state < 20 => 'danger',
                        $state < 40 => 'warning',
                        default => 'success'
                    }),
                    
                TextColumn::make('water_flow')
                    ->label('Water Flow')
                    ->suffix('L/h')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->color('info'),
                    
                TextColumn::make('light_intensity')
                    ->label('Light')
                    ->suffix('lux')
                    ->sortable()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->toggleable(),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'normal',
                        'warning' => 'peringatan',
                        'danger' => 'kritis',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'normal' => 'Normal',
                        'peringatan' => 'Peringatan',
                        'kritis' => 'Kritis',
                        default => ucfirst($state),
                    }),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                SelectFilter::make('device_id')
                    ->label('Device')
                    ->options(function () {
                        return Device::where('is_active', true)
                            ->get()
                            ->pluck('device_name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'normal' => 'Normal',
                        'peringatan' => 'Peringatan',
                        'kritis' => 'Kritis',
                    ])
                    ->native(false),
                    
                Filter::make('recorded_at')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('recorded_from')
                                    ->label('From Date')
                                    ->displayFormat('d/m/Y')
                                    ->placeholder('Select start date'),
                                Forms\Components\DatePicker::make('recorded_until')
                                    ->label('Until Date')
                                    ->displayFormat('d/m/Y')
                                    ->placeholder('Select end date'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['recorded_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('recorded_at', '>=', $date),
                            )
                            ->when(
                                $data['recorded_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('recorded_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['recorded_from'] ?? null) {
                            $indicators['recorded_from'] = 'From: ' . Carbon::parse($data['recorded_from'])->toFormattedDateString();
                        }
                        if ($data['recorded_until'] ?? null) {
                            $indicators['recorded_until'] = 'Until: ' . Carbon::parse($data['recorded_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
                    
                // Temperature range filter
                Filter::make('temperature_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('temp_min')
                                    ->label('Min Temperature')
                                    ->numeric()
                                    ->suffix('°C')
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('temp_max')
                                    ->label('Max Temperature')
                                    ->numeric()
                                    ->suffix('°C')
                                    ->placeholder('50'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['temp_min'] !== null,
                                fn (Builder $query): Builder => $query->where('temperature', '>=', $data['temp_min']),
                            )
                            ->when(
                                $data['temp_max'] !== null,
                                fn (Builder $query): Builder => $query->where('temperature', '<=', $data['temp_max']),
                            );
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('2xl'),
                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected sensor data? This action cannot be undone.'),
                ]),
            ])
            ->emptyStateHeading('No sensor data found')
            ->emptyStateDescription('Start by creating your first sensor data entry.')
            ->emptyStateIcon('heroicon-o-chart-bar-square')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s'); // Auto refresh every 30 seconds
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
            'create' => Pages\CreateSensorData::route('/create'),
            'edit' => Pages\EditSensorData::route('/{record}/edit'),
            // 'view' => Pages\ViewSensorData::route('/{record}'),
        ];
    }
    
    // Navigation badge untuk menampilkan jumlah data 
    public static function getNavigationBadge(): ?string
    {
        // return static::getModel()::whereDate('created_at', today())->count();
        return static::getModel()::count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 10 ? 'success' : 'primary';
    }
}