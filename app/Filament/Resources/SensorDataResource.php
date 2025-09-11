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
    protected static ?string $navigationLabel = 'Data Sensor';
    protected static ?string $modelLabel = 'Data Sensor';
    protected static ?string $pluralModelLabel = 'Data Sensor';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('device_id')
                    ->label('Perangkat')
                    ->options(function () {
                        return Device::where('is_active', true)
                            ->get()
                            ->pluck('device_name', 'id'); // Menggunakan 'id' bukan 'device_id'
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->placeholder('Pilih perangkat'),
                    
                DateTimePicker::make('device_ts')
                    ->label('Waktu Perangkat (RTC)')
                    ->timezone('Asia/Jakarta')
                    ->displayFormat('d/m/Y H:i')
                    ->seconds(false)
                    ->helperText('Waktu dari perangkat (opsional). Jika kosong, gunakan Waktu Rekam.'),

                DateTimePicker::make('recorded_at')
                    ->label('Waktu Rekam (Server)')
                    ->default(now())
                    ->required()
                    ->timezone('Asia/Jakarta')
                    ->displayFormat('d/m/Y H:i')
                    ->seconds(false),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('temperature_c')
                            ->label('Suhu (°C)')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(-50)
                            ->maxValue(100)
                            ->suffix('°C')
                            ->placeholder('25.5'),

                        TextInput::make('soil_moisture_pct')
                            ->label('Kelembapan Tanah (%)')
                            ->numeric()
                            ->step(1)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->placeholder('45'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('water_height_cm')
                            ->label('Tinggi Air (cm)')
                            ->numeric()
                            ->step(1)
                            ->minValue(0)
                            ->suffix('cm')
                            ->placeholder('50'),

                        TextInput::make('water_volume_l')
                            ->label('Volume Air (L)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->suffix('L')
                            ->placeholder('120.50'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('light_lux')
                            ->label('Cahaya (lux)')
                            ->numeric()
                            ->step(1)
                            ->minValue(0)
                            ->suffix('lux')
                            ->placeholder('25000'),

                        TextInput::make('wind_speed_ms')
                            ->label('Kecepatan Angin (m/s)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->suffix('m/s')
                            ->placeholder('3.5'),
                    ])
                    ->columns(2),

                Forms\Components\Fieldset::make('INA226 (Daya)')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('ina226_bus_voltage_v')
                                    ->label('Tegangan Bus (V)')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->suffix('V'),
                                TextInput::make('ina226_shunt_voltage_mv')
                                    ->label('Tegangan Shunt (mV)')
                                    ->numeric()
                                    ->step(1)
                                    ->suffix('mV'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('ina226_current_ma')
                                    ->label('Arus (mA)')
                                    ->numeric()
                                    ->step(0.001)
                                    ->suffix('mA'),
                                TextInput::make('ina226_power_mw')
                                    ->label('Daya (mW)')
                                    ->numeric()
                                    ->step(0.001)
                                    ->suffix('mW'),
                            ]),
                    ]),

                Forms\Components\Fieldset::make('Field Lama (opsional)')
                    ->schema([
                        TextInput::make('temperature')->label('Suhu Lama (°C)')->numeric()->step(0.1)->suffix('°C'),
                        TextInput::make('humidity')->label('Kelembapan Udara (%)')->numeric()->step(0.1)->suffix('%'),
                        TextInput::make('soil_moisture')->label('Kelembapan Tanah Lama (%)')->numeric()->step(0.1)->suffix('%'),
                        TextInput::make('water_flow')->label('Aliran Air (L/h)')->numeric()->step(0.1)->suffix('L/h'),
                        TextInput::make('light_intensity')->label('Intensitas Cahaya Lama (lux)')->numeric()->step(1)->suffix('lux'),
                    ]),

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.device_name')
                    ->label('Nama Perangkat')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-cpu-chip'),
                    
                TextColumn::make('device_ts')
                    ->label('Waktu Perangkat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->timezone('Asia/Jakarta')
                    ->description(fn (SensorData $record): string => $record->device_ts?->diffForHumans() ?? '—')
                    ->toggleable(true, true),

                TextColumn::make('recorded_at')
                    ->label('Waktu Rekam (Server)')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->timezone('Asia/Jakarta')
                    ->description(fn (SensorData $record): string => $record->recorded_at?->diffForHumans() ?? '—')
                    ->toggleable(true, true),
                    
                TextColumn::make('temperature_c')
                    ->label('Suhu (°C)')
                    ->suffix('°C')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->color(fn ($state) => match (true) {
                        $state > 35 => 'danger',
                        $state > 30 => 'warning', 
                        $state < 10 => 'info',
                        default => 'success'
                    })
                    ->toggleable(),
                    
                TextColumn::make('soil_moisture_pct')
                    ->label('Kelembapan Tanah')
                    ->suffix('%')
                    ->sortable()
                    ->numeric()
                    ->color(fn ($state) => match (true) {
                        $state < 20 => 'danger',
                        $state < 40 => 'warning',
                        default => 'success'
                    })
                    ->toggleable(),
                    
                TextColumn::make('water_height_cm')
                    ->label('Tinggi Air')
                    ->suffix(' cm')
                    ->sortable()
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('water_volume_l')
                    ->label('Volume Air')
                    ->suffix(' L')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                    
                TextColumn::make('light_lux')
                    ->label('Cahaya')
                    ->suffix(' lux')
                    ->sortable()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color(fn ($state) => match (true) {
                        $state < 200 => 'info',
                        default => 'success'
                    })
                    ->toggleable(),

                TextColumn::make('wind_speed_ms')
                    ->label('Kecepatan Angin')
                    ->suffix(' m/s')
                    ->sortable()
                    ->numeric(decimalPlaces: 2)
                    ->color(fn ($state) => match (true) {
                        $state > 15 => 'danger',
                        $state > 8 => 'warning',
                        default => 'success'
                    })
                    ->toggleable(),

                TextColumn::make('ina226_current_ma')
                    ->label('Arus')
                    ->suffix(' mA')
                    ->sortable()
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(),

                TextColumn::make('ina226_bus_voltage_v')
                    ->label('Tegangan Bus')
                    ->suffix(' V')
                    ->sortable()
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(),

                TextColumn::make('ina226_power_mw')
                    ->label('Daya')
                    ->suffix(' mW')
                    ->sortable()
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(),

                // Legacy columns (hidden by default)
                TextColumn::make('temperature')
                    ->label('Suhu Lama')
                    ->suffix('°C')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('humidity')
                    ->label('Kelembapan Udara')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('soil_moisture')
                    ->label('Kelembapan Tanah Lama')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('water_flow')
                    ->label('Aliran Air')
                    ->suffix(' L/h')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('light_intensity')
                    ->label('Intensitas Cahaya Lama')
                    ->suffix(' lux')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
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
                                fn (Builder $query): Builder => $query->where('temperature_c', '>=', $data['temp_min']),
                            )
                            ->when(
                                $data['temp_max'] !== null,
                                fn (Builder $query): Builder => $query->where('temperature_c', '<=', $data['temp_max']),
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