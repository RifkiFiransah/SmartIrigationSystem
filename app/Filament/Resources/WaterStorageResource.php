<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaterStorageResource\Pages;
use App\Filament\Resources\WaterStorageResource\RelationManagers;
use App\Models\WaterStorage;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaterStorageResource extends Resource
{
    protected static ?string $model = WaterStorage::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    
    protected static ?string $navigationLabel = 'Water Storage';
    
    protected static ?string $modelLabel = 'Water Storage';
    
    protected static ?string $pluralModelLabel = 'Water Storages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tank Information')
                    ->description('Basic tank and zone information')
                    ->schema([
                        Forms\Components\TextInput::make('tank_name')
                            ->label('Tank Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Main Tank Zone A'),
                        
                        Forms\Components\TextInput::make('zone_name')
                            ->label('Zone/Lokasi Utama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Greenhouse A, Outdoor Field B')
                            ->helperText('Zona utama atau lokasi tangki ini'),
                        
                        Forms\Components\TextInput::make('area_name')
                            ->label('Area/Blok Spesifik')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Blok Tomat A1, Sektor Sayuran B2')
                            ->helperText('Area spesifik yang dilayani tangki ini'),
                        
                        Forms\Components\Textarea::make('zone_description')
                            ->label('Zone Description')
                            ->rows(2)
                            ->placeholder('Deskripsi detail zona tanaman, jenis tanaman, dll.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Irrigation Lines Configuration')
                    ->description('Setup jalur-jalur irigasi di area ini')
                    ->schema([
                        Forms\Components\TextInput::make('total_lines')
                            ->label('Total Jalur Irigasi')
                            ->numeric()
                            ->default(0)
                            ->helperText('Jumlah jalur irigasi di area ini'),
                        
                        Forms\Components\TextInput::make('area_size_sqm')
                            ->label('Luas Area (m²)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('m²')
                            ->helperText('Luas total area yang dilayani'),
                        
                        Forms\Components\TextInput::make('plant_types')
                            ->label('Jenis Tanaman')
                            ->placeholder('e.g., Tomat, Cabai, Lettuce')
                            ->helperText('Jenis tanaman yang ditanam di area ini'),
                        
                        Forms\Components\Select::make('irrigation_system_type')
                            ->label('Sistem Irigasi')
                            ->options([
                                'drip' => '💧 Drip Irrigation',
                                'sprinkler' => '🌧️ Sprinkler System',
                                'nft' => '🌊 NFT (Nutrient Film Technique)',
                                'flood' => '🌊 Flood/Ebb & Flow',
                                'misting' => '💨 Misting System',
                                'manual' => '🚿 Manual Watering',
                                'mixed' => '🔄 Mixed System',
                            ])
                            ->placeholder('Pilih sistem irigasi'),
                        
                        Forms\Components\Repeater::make('irrigation_lines')
                            ->label('Detail Jalur Irigasi')
                            ->schema([
                                Forms\Components\TextInput::make('line_id')
                                    ->label('ID Jalur')
                                    ->required()
                                    ->placeholder('L001, L002, dst.'),
                                Forms\Components\TextInput::make('line_name')
                                    ->label('Nama Jalur')
                                    ->required()
                                    ->placeholder('Jalur Tomat A1'),
                                Forms\Components\Select::make('line_type')
                                    ->label('Tipe')
                                    ->options([
                                        'drip' => 'Drip',
                                        'sprinkler' => 'Sprinkler',
                                        'misting' => 'Misting'
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('plant_count')
                                    ->label('Jumlah Tanaman')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('coverage_sqm')
                                    ->label('Luas Coverage (m²)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('m²'),
                                Forms\Components\TextInput::make('flow_rate_lpm')
                                    ->label('Flow Rate (L/min)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('L/min'),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => '🟢 Active',
                                        'maintenance' => '🔧 Maintenance',
                                        'inactive' => '🔴 Inactive'
                                    ])
                                    ->default('active'),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->helperText('Konfigurasi detail setiap jalur irigasi'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Device Association')
                    ->description('Devices and nodes in this zone')
                    ->schema([
                        Forms\Components\Select::make('device_id')
                            ->label('Primary Device/Node')
                            ->relationship('device', 'device_name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Node utama yang mengontrol tangki ini'),
                        
                        Forms\Components\Repeater::make('associated_devices')
                            ->label('Additional Nodes in Zone')
                            ->schema([
                                Forms\Components\Select::make('device_id')
                                    ->label('Device/Node')
                                    ->options(Device::all()->pluck('device_name', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('role')
                                    ->label('Role/Function')
                                    ->placeholder('e.g., Soil Sensor, Temperature Monitor')
                            ])
                            ->columnSpanFull()
                            ->helperText('Node-node tambahan yang ada di zona ini')
                            ->collapsible(),
                    ]),
                
                Forms\Components\Section::make('Tank Capacity & Status')
                    ->description('Tank specifications and current status')
                    ->schema([
                        Forms\Components\TextInput::make('total_capacity')
                            ->label('Total Capacity (Liters)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('L')
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('current_volume')
                            ->label('Current Volume (Liters)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('L')
                            ->default(0)
                            ->minValue(0),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'normal' => '🟢 Normal (25-90%)',
                                'low' => '🟡 Low (10-25%)',
                                'empty' => '🔴 Empty (<10%)',
                                'full' => '🔵 Full (>90%)',
                                'maintenance' => '🔧 Maintenance',
                            ])
                            ->default('normal')
                            ->required(),
                        
                        Forms\Components\TextInput::make('max_daily_usage')
                            ->label('Max Daily Usage (L/day)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('L/day')
                            ->helperText('Estimasi penggunaan maksimal per hari untuk zona ini'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Catatan tambahan tentang tangki, maintenance schedule, dll.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tank_name')
                    ->label('Tank Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('zone_name')
                    ->label('Zone')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('area_name')
                    ->label('Area/Blok')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                
                Tables\Columns\TextColumn::make('total_lines')
                    ->label('Jalur')
                    ->suffix(' lines')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('plant_types')
                    ->label('Tanaman')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return $column->getRecord()->plant_types;
                    }),
                
                Tables\Columns\TextColumn::make('area_size_sqm')
                    ->label('Area Size')
                    ->suffix(' m²')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('irrigation_system_type')
                    ->label('Sistem')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'drip' => 'info',
                        'sprinkler' => 'primary',
                        'nft' => 'success',
                        'misting' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'drip' => '💧 Drip',
                        'sprinkler' => '🌧️ Sprinkler',
                        'nft' => '🌊 NFT',
                        'misting' => '💨 Misting',
                        'flood' => '🌊 Flood',
                        'manual' => '🚿 Manual',
                        'mixed' => '🔄 Mixed',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('device.device_name')
                    ->label('Primary Node')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-cpu-chip')
                    ->placeholder('No primary node')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('total_capacity')
                    ->label('Capacity')
                    ->suffix(' L')
                    ->sortable()
                    ->alignRight(),
                
                Tables\Columns\TextColumn::make('current_volume')
                    ->label('Current')
                    ->suffix(' L')
                    ->sortable()
                    ->alignRight(),
                
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Fill Level')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 50 => 'primary',
                        $state >= 25 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'normal',
                        'info' => 'full',
                        'warning' => 'low',
                        'danger' => 'empty',
                        'gray' => 'maintenance',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'normal',
                        'heroicon-o-beaker' => 'full',
                        'heroicon-o-exclamation-triangle' => 'low',
                        'heroicon-o-x-circle' => 'empty',
                        'heroicon-o-wrench' => 'maintenance',
                    ]),
                
                Tables\Columns\TextColumn::make('max_daily_usage')
                    ->label('Daily Usage')
                    ->suffix(' L/day')
                    ->placeholder('Not set')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Filter')
                    ->options([
                        'normal' => '🟢 Normal',
                        'low' => '🟡 Low',
                        'empty' => '🔴 Empty',
                        'full' => '🔵 Full',
                        'maintenance' => '🔧 Maintenance',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('zone_name')
                    ->label('Zone Filter')
                    ->options(function () {
                        return WaterStorage::distinct('zone_name')
                            ->whereNotNull('zone_name')
                            ->pluck('zone_name', 'zone_name')
                            ->toArray();
                    })
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('device')
                    ->label('Primary Node')
                    ->relationship('device', 'device_name')
                    ->multiple(),
                
                Tables\Filters\Filter::make('low_capacity')
                    ->label('Low Capacity (<25%)')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(current_volume / total_capacity) * 100 < 25'))
                    ->toggle(),
                
                Tables\Filters\Filter::make('critical_capacity')
                    ->label('Critical (<10%)')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(current_volume / total_capacity) * 100 < 10'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function getWidgets(): array
    {
        return [
            WaterStorageResource\Widgets\WaterStorageOverview::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return WaterStorage::count() > 0 ? (string) WaterStorage::count() : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return WaterStorage::count() > 0 ? 'danger' : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaterStorages::route('/'),
            'create' => Pages\CreateWaterStorage::route('/create'),
            'edit' => Pages\EditWaterStorage::route('/{record}/edit'),
        ];
    }
}
