<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationValveScheduleResource\Pages;
use App\Models\IrrigationValve;
use App\Models\IrrigationValveSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IrrigationValveScheduleResource extends Resource
{
    protected static ?string $model = IrrigationValveSchedule::class;
    protected static ?string $navigationGroup = 'Sistem Irigasi';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Jadwal Node Irigasi';
    protected static ?string $modelLabel = 'Jadwal Node Irigasi';
    protected static ?string $pluralModelLabel = 'Jadwal Node Irigasi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $dayOptions = [
            '0' => 'Sun',
            '1' => 'Mon',
            '2' => 'Tue',
            '3' => 'Wed',
            '4' => 'Thu',
            '5' => 'Fri',
            '6' => 'Sat',
        ];

        return $form->schema([
            Forms\Components\Select::make('node_uid')
                ->label('Nama Node')
                ->options(function () {
                    $options = [];
                    
                    // Get all devices that have irrigation valves
                    $devices = \App\Models\Device::with('irrigationValves')
                        ->whereHas('irrigationValves')
                        ->orderBy('device_name')
                        ->get();
                    
                    foreach ($devices as $device) {
                        $deviceDisplay = "{$device->device_name} - {$device->location}";
                        
                        // If device has multiple valves, show them separately
                        if ($device->irrigationValves->count() > 1) {
                            foreach ($device->irrigationValves as $valve) {
                                $valveName = $valve->description ?? 'Valve ' . $valve->id;
                                $display = "{$deviceDisplay} - {$valveName}";
                                $options[$valve->node_uid] = $display;
                            }
                        } else {
                            // Single valve, show device name with valve description
                            $valve = $device->irrigationValves->first();
                            $valveName = $valve->description ?? 'Main Valve';
                            $options[$valve->node_uid] = "{$deviceDisplay} - {$valveName}";
                        }
                    }
                    
                    return $options;
                })
                ->searchable()
                ->required(),
            Forms\Components\TimePicker::make('start_time')
                ->label('Start Time')
                ->seconds(false)
                ->required(),
            Forms\Components\TextInput::make('duration_minutes')
                ->label('Duration (minutes)')
                ->numeric()
                ->minValue(1)
                ->required(),
            Forms\Components\TextInput::make('water_usage_target_liters')
                ->label('Target Penggunaan Air (Liter)')
                ->numeric()
                ->minValue(0)
                ->step(0.1)
                ->suffix('L')
                ->helperText('Jumlah air yang akan digunakan selama jadwal irigasi ini berjalan'),
            Forms\Components\CheckboxList::make('days_of_week')
                ->label('Days of week')
                ->options($dayOptions)
                ->columns(4)
                ->helperText('Leave empty to run every day.'),
            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        $dayMap = [
            '0' => 'Sun', '1' => 'Mon', '2' => 'Tue', '3' => 'Wed',
            '4' => 'Thu', '5' => 'Fri', '6' => 'Sat',
        ];

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('node_uid')
                    ->label('Nama Node')
                    ->formatStateUsing(function ($state) {
                        $valve = IrrigationValve::where('node_uid', $state)->with('device')->first();
                        if ($valve && $valve->device) {
                            $device = $valve->device;
                            $deviceDisplay = "{$device->device_name} - {$device->location}";
                            
                            // Check if device has multiple valves
                            $deviceValves = $device->irrigationValves;
                            if ($deviceValves->count() > 1) {
                                $valveIndex = $deviceValves->search(function($v) use ($state) {
                                    return $v->node_uid === $state;
                                }) + 1;
                                return "{$deviceDisplay} - Katup #{$valveIndex}";
                            } else {
                                return $deviceDisplay;
                            }
                        }
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')->label('Start')->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Minutes')->sortable(),
                Tables\Columns\TextColumn::make('water_usage_target_liters')
                    ->label('Target Air')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' L' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_of_week')
                    ->label('Days')
                    ->formatStateUsing(function ($state) use ($dayMap) {
                        if (empty($state) || !is_array($state)) {
                            return 'Everyday';
                        }
                        $labels = [];
                        foreach ($state as $d) {
                            $labels[] = $dayMap[(string) $d] ?? (string) $d;
                        }
                        return implode(', ', $labels);
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
                Tables\Columns\TextColumn::make('last_run_at')->label('Last run')->dateTime()->since(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIrrigationValveSchedules::route('/'),
            'create' => Pages\CreateIrrigationValveSchedule::route('/create'),
            'edit' => Pages\EditIrrigationValveSchedule::route('/{record}/edit'),
        ];
    }
}
