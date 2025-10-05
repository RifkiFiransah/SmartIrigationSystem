<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaterUsageLogResource\Pages;
use App\Models\WaterUsageLog;
use App\Models\Device;
use App\Models\WaterStorage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class WaterUsageLogResource extends Resource
{
    protected static ?string $model = WaterUsageLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Riwayat Node Valve';

    protected static ?string $navigationGroup = 'Sistem Irigasi';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Riwayat Node Valve';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Riwayat Node Valve';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penggunaan')
                    ->schema([
                        Forms\Components\Select::make('water_storage_id')
                            ->label('Tangki Air')
                            ->relationship('waterStorage', 'tank_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        
                        Forms\Components\Select::make('device_id')
                            ->label('Device')
                            ->relationship('device', 'device_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\DatePicker::make('usage_date')
                            ->label('Tanggal Penggunaan')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\TextInput::make('volume_used_l')
                            ->label('Volume Terpakai (Liter)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('L')
                            ->required()
                            ->helperText('Volume air yang terpakai dalam liter'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\Select::make('source')
                            ->label('Sumber Data')
                            ->options([
                                'sensor' => 'Sensor Otomatis',
                                'manual' => 'Input Manual',
                                'api' => 'API External',
                                'system' => 'Sistem',
                            ])
                            ->default('sensor')
                            ->required(),
                        
                        Forms\Components\KeyValue::make('meta')
                            ->label('Data Tambahan')
                            ->keyLabel('Kunci')
                            ->valueLabel('Nilai')
                            ->helperText('Data tambahan dalam format key-value'),
                    ])->columns(1)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('device.device_name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('waterStorage.tank_name')
                    ->label('Tangki')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('volume_used_l')
                    ->label('Volume (L)')
                    ->suffix(' L')
                    ->numeric(2)
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->suffix(' L')
                            ->numeric(2),
                        Tables\Columns\Summarizers\Average::make()
                            ->label('Rata-rata')
                            ->suffix(' L')
                            ->numeric(2),
                    ]),
                
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sensor' => 'success',
                        'manual' => 'warning',
                        'api' => 'info',
                        'system' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sensor' => 'Sensor',
                        'manual' => 'Manual',
                        'api' => 'API',
                        'system' => 'Sistem',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('device_id')
                    ->label('Device')
                    ->relationship('device', 'device_name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('water_storage_id')
                    ->label('Tangki')
                    ->relationship('waterStorage', 'tank_name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'sensor' => 'Sensor',
                        'manual' => 'Manual',
                        'api' => 'API',
                        'system' => 'Sistem',
                    ]),
                
                Filter::make('usage_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('usage_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('usage_date', '<=', $date),
                            );
                    }),
                
                Filter::make('volume_range')
                    ->form([
                        Forms\Components\TextInput::make('min_volume')
                            ->label('Volume Minimum (L)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_volume')
                            ->label('Volume Maximum (L)')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_volume'],
                                fn (Builder $query, $volume): Builder => $query->where('volume_used_l', '>=', $volume),
                            )
                            ->when(
                                $data['max_volume'],
                                fn (Builder $query, $volume): Builder => $query->where('volume_used_l', '<=', $volume),
                            );
                    }),
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
            ])
            ->defaultSort('usage_date', 'desc');
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
            'index' => Pages\ListWaterUsageLogs::route('/'),
            'create' => Pages\CreateWaterUsageLog::route('/create'),
            'edit' => Pages\EditWaterUsageLog::route('/{record}/edit'),
        ];
    }
}
