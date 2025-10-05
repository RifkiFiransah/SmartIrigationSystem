<?php

namespace App\Filament\Resources;

use App\Models\IrrigationValveLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class IrrigationValveLogResource extends Resource
{
    protected static ?string $model = IrrigationValveLog::class;
    protected static ?string $navigationGroup = 'Sistem Irigasi';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Node Valve';
    protected static ?string $modelLabel = 'Riwayat Node Valve';
    protected static ?string $pluralModelLabel = 'Riwayat Node Valve';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('device.device_name')
                ->label('Device')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('node_uid')
                ->label('Node UID')
                ->searchable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('action')
                ->badge()
                ->colors([
                    'success' => ['open', 'device_connect', 'schedule_create', 'schedule_execute'],
                    'danger' => ['close', 'device_disconnect', 'schedule_delete'],
                    'warning' => ['toggle_mode', 'schedule_update'],
                    'primary' => ['system_auto_open', 'system_auto_close', 'schedule_complete'],
                ]),
            Tables\Columns\TextColumn::make('trigger')
                ->badge()
                ->colors([
                    'success' => ['manual'],
                    'primary' => ['system', 'auto'],
                    'warning' => ['schedule'],
                    'info' => ['api', 'admin_panel'],
                ]),
            Tables\Columns\TextColumn::make('duration_seconds')
                ->label('Duration (s)')
                ->numeric()
                ->toggleable(),
            Tables\Columns\TextColumn::make('notes')
                ->label('Notes')
                ->limit(50)
                ->toggleable(),
            Tables\Columns\TextColumn::make('user_id')
                ->label('User')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('source_ip')
                ->label('Source IP')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('action')
                ->options([
                    'open' => 'Open',
                    'close' => 'Close', 
                    'toggle_mode' => 'Toggle Mode',
                    'device_connect' => 'Device Connect',
                    'device_disconnect' => 'Device Disconnect',
                    'schedule_create' => 'Schedule Create',
                    'schedule_update' => 'Schedule Update',
                    'schedule_delete' => 'Schedule Delete',
                    'schedule_execute' => 'Schedule Execute',
                    'schedule_complete' => 'Schedule Complete',
                    'system_auto_open' => 'System Auto Open',
                    'system_auto_close' => 'System Auto Close',
                ]),
            Tables\Filters\SelectFilter::make('trigger')
                ->options([
                    'manual' => 'Manual',
                    'auto' => 'Auto',
                    'schedule' => 'Schedule',
                    'api' => 'API',
                    'system' => 'System',
                    'device_event' => 'Device Event',
                    'admin_panel' => 'Admin Panel',
                    'mobile_app' => 'Mobile App',
                    'web_interface' => 'Web Interface',
                ]),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('created_from'),
                    \Filament\Forms\Components\DatePicker::make('created_until'),
                ])
                ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => IrrigationValveLogResource\Pages\ListIrrigationValveLogs::route('/'),
        ];
    }
}
