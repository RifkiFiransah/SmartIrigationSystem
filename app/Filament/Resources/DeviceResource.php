<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Perangkat';
    protected static ?string $modelLabel = 'Perangkat';
    protected static ?string $pluralModelLabel = 'Perangkat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('device_id')
                    ->label('ID Perangkat')
                    ->placeholder('ID Perangkat (otomatis)')
                    ->readOnly()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('device_name')
                    ->label('Nama Perangkat')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('Lokasi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline()
                    ->required(),
                Section::make('Data Sensor Awal (Opsional)')
                    ->description('Isi jika ingin langsung membuat catatan sensor awal untuk perangkat baru.')
                    ->schema([
                        Toggle::make('init_sensor_enable')
                            ->label('Isi Data Awal?')
                            ->reactive(),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('init_ground_temperature_c')
                                    ->numeric()
                                    ->label('Suhu Tanah (°C)')
                                    ->visible(fn($get)=> (bool)$get('init_sensor_enable')),
                                TextInput::make('init_soil_moisture_pct')
                                    ->numeric()
                                    ->label('Kelembapan Tanah (%)')
                                    ->visible(fn($get)=> (bool)$get('init_sensor_enable')),
                                TextInput::make('init_irrigation_usage_total_l')
                                    ->numeric()
                                    ->label('Total Irigasi (L)')
                                    ->visible(fn($get)=> (bool)$get('init_sensor_enable')),
                                TextInput::make('init_battery_voltage_v')
                                    ->numeric()
                                    ->label('Tegangan Baterai (V)')
                                    ->visible(fn($get)=> (bool)$get('init_sensor_enable')),
                                TextInput::make('init_ina226_power_mw')
                                    ->numeric()
                                    ->label('Daya (mW)')
                                    ->visible(fn($get)=> (bool)$get('init_sensor_enable')),
                            ]),
                    ])->collapsed(),
            ])->columns(2);
            // ->columnSpan([1, 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device_id')
                    ->label('ID Perangkat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('device_name')
                    ->label('Nama Perangkat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('connection_state')
                    ->label('Koneksi')
                    ->badge()
                    ->colors([
                        'success' => 'online',
                        'danger' => 'offline',
                    ])
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '-')
                    ->description(fn ($record) => $record->connection_state_source === 'manual' ? 'Manual' : 'Auto')
                    ->toggleable(),
                TextColumn::make('valve_state')
                    ->label('Valve')
                    ->badge()
                    ->colors([
                        'success' => 'open',
                        'secondary' => 'closed',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'open' ? 'Open' : 'Closed')
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->label('Status Aktif')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => $state ? 'Aktif' : 'Tidak Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('toggleConnection')
                    ->label('Toggle Online')
                    ->icon('heroicon-o-signal')
                    ->color(fn ($record) => $record->connection_state === 'online' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record, $livewire) {
                        try {
                            $new = $record->connection_state === 'online' ? 'offline' : 'online';
                            $record->setConnectionState($new, 'manual');
                            $record->refresh();
                            Notification::make()->title('Status koneksi diubah ke '.strtoupper($new))->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Gagal mengubah status')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('toggleValve')
                    ->label('Toggle Valve')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color(fn ($record) => $record->valve_state === 'open' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $prev = $record->valve_state;
                            $record->toggleValve();
                            $record->refresh();
                            Notification::make()->title('Valve '.($prev === 'open' ? 'ditutup' : 'dibuka'))->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Gagal toggle valve')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    // Navigation badge untuk menampilkan jumlah data
    public static function getNavigationBadge(): ?string
    {
        // return static::getModel()::whereDate('created_at', today())->count();
        return static::getModel()::count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
