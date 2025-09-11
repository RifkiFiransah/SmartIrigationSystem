<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationControlResource\Pages;
use App\Models\IrrigationValve;
use App\Models\IrrigationValveSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;

class IrrigationControlResource extends Resource
{
    protected static ?string $model = IrrigationValve::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Katup Node';
    
    protected static ?string $modelLabel = 'Katup Node';
    
    protected static ?string $pluralModelLabel = 'Katup Node';
    
    protected static ?string $navigationGroup = 'Sistem Irigasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Valve - Basic')
                    ->schema([
                        Forms\Components\Placeholder::make('node_uid_info')
                            ->label('Node UID')
                            ->content('Will be auto-generated: NODE-XXXXXX')
                            ->helperText('Node UID akan dibuat otomatis saat save'),
                            
                        Forms\Components\Select::make('device_id')
                            ->label('Device')
                            ->relationship('device', 'device_name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('device_name')->required(),
                                Forms\Components\TextInput::make('device_id')->required(),
                                Forms\Components\TextInput::make('location'),
                            ]),
                    ])
                    ->columns(2),

                

                Forms\Components\Section::make('Status & Mode')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Current Status')
                            ->required()
                            ->options([
                                'closed' => 'Closed',
                                'open' => 'Open',
                            ])
                            ->default('closed'),
                            
                        Forms\Components\Select::make('mode')
                            ->label('Control Mode')
                            ->required()
                            ->options([
                                'manual' => 'Manual',
                                'auto' => 'Automatic',
                            ])
                            ->default('manual'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->required()
                            ->default(true)
                            ->helperText('Enable/disable this valve'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Description of this valve/node...'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Schedules (Optional)')
                    ->description('Tambah satu atau lebih jadwal untuk node ini. Optional, bisa dikosongkan.')
                    ->schema([
                        Forms\Components\Repeater::make('schedules')
                            ->dehydrated(false)
                            ->label('Schedules')
                            ->addActionLabel('Add schedule')
                            ->schema([
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Start Time')
                                    ->seconds(false)
                                    ->required(),
                                Forms\Components\TextInput::make('duration_minutes')
                                    ->label('Duration (minutes)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\CheckboxList::make('days_of_week')
                                    ->label('Days of week')
                                    ->options([
                                        '0' => 'Sun',
                                        '1' => 'Mon',
                                        '2' => 'Tue',
                                        '3' => 'Wed',
                                        '4' => 'Thu',
                                        '5' => 'Fri',
                                        '6' => 'Sat',
                                    ])
                                    ->columns(4)
                                    ->helperText('Kosongkan untuk setiap hari.'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('node_uid')
                    ->label('Node UID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('device.device_name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('control_type')
                    ->label('Control')
                    ->badge()
                    ->state(function (IrrigationValve $record): string {
                        $hasSchedule = IrrigationValveSchedule::query()
                            ->where('node_uid', $record->node_uid)
                            ->where('is_active', true)
                            ->exists();
                        if ($hasSchedule) {
                            return 'Scheduled';
                        }
                        return $record->mode === 'auto' ? 'Auto' : 'Manual';
                    })
                    ->colors([
                
                                            Forms\Components\Section::make('Schedules (Optional)')
                                                ->description('Tambah satu atau lebih jadwal untuk node ini. Optional, bisa dikosongkan.')
                                                ->schema([
                                                    Forms\Components\Repeater::make('schedules')
                                                        ->dehydrated(false)
                                                        ->label('Schedules')
                                                        ->addActionLabel('Add schedule')
                                                        ->schema([
                                                            Forms\Components\TimePicker::make('start_time')
                                                                ->label('Start Time')
                                                                ->seconds(false)
                                                                ->required(),
                                                            Forms\Components\TextInput::make('duration_minutes')
                                                                ->label('Duration (minutes)')
                                                                ->numeric()
                                                                ->minValue(1)
                                                                ->required(),
                                                            Forms\Components\CheckboxList::make('days_of_week')
                                                                ->label('Days of week')
                                                                ->options([
                                                                    '0' => 'Sun',
                                                                    '1' => 'Mon',
                                                                    '2' => 'Tue',
                                                                    '3' => 'Wed',
                                                                    '4' => 'Thu',
                                                                    '5' => 'Fri',
                                                                    '6' => 'Sat',
                                                                ])
                                                                ->columns(4)
                                                                ->helperText('Kosongkan untuk setiap hari.'),
                                                            Forms\Components\Toggle::make('is_active')
                                                                ->label('Active')
                                                                ->default(true),
                                                        ])
                                                        ->columns(4)
                                                        ->grid(1),
                                                ])
                                                ->columns(1),
                        'info' => 'Scheduled',
                        'success' => 'Auto',
                        'primary' => 'Manual',
                    ]),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'open',
                        'danger' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Open',
                        'closed' => 'Closed',
                        default => $state,
                    }),
                    
                TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->colors([
                        'success' => 'auto',
                        'primary' => 'manual',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'auto' => 'Auto',
                        'manual' => 'Manual',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('last_open_at')
                    ->label('Last Open')
                    ->dateTime('M j, H:i')
                    ->sortable()
                    ->placeholder('Never'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('scheduled')
                    ->label('Scheduled')
                    ->query(function ($query) {
                        $query->whereExists(function ($sub) {
                            $sub->from('irrigation_valve_schedules as s')
                                ->selectRaw('1')
                                ->whereColumn('s.node_uid', 'irrigation_valves.node_uid')
                                ->where('s.is_active', true);
                        });
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'closed' => 'Closed',
                        'open' => 'Open',
                    ]),
                    
                Tables\Filters\SelectFilter::make('mode')
                    ->label('Mode')
                    ->options([
                        'manual' => 'Manual',
                        'auto' => 'Auto',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                    
                Tables\Filters\SelectFilter::make('device')
                    ->relationship('device', 'device_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('max_duration_minutes')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(180)
                            ->label('Max Duration (minutes)')
                            ->helperText('Optional'),
                    ])
                    ->action(function (IrrigationValve $record, array $data): void {
                        $record->open(isset($data['max_duration_minutes']) ? (int) $data['max_duration_minutes'] : null);
                        Notification::make()->title('Valve Opened')->success()->send();
                    }),
                
                Tables\Actions\Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (IrrigationValve $record): void {
                        $record->close();
                        Notification::make()->title('Valve Closed')->success()->send();
                    }),
                
                Tables\Actions\Action::make('toggle_mode')
                    ->label('Toggle Mode')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (IrrigationValve $record): void {
                        $record->toggleMode();
                        Notification::make()->title('Mode Toggled')->success()->send();
                    }),

                Tables\Actions\Action::make('manage_schedules')
                    ->label('Schedules')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->url(fn () => route('filament.admin.resources.irrigation-valve-schedules.index'))
                    ->openUrlInNewTab(),
                
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate (No-Op)')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (IrrigationValve $record): void {
                        $record->last_evaluated_at = now();
                        $record->save();
                        Notification::make()
                            ->title('Evaluated')
                            ->body('No rules. Recorded evaluate time.')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('close_all')
                        ->label('Close Selected')
                        ->icon('heroicon-o-stop')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->close();
                            }
                            Notification::make()->title('Selected valves closed')->success()->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListIrrigationControls::route('/'),
            'create' => Pages\CreateIrrigationControl::route('/create'),
            'edit' => Pages\EditIrrigationControl::route('/{record}/edit'),
        ];
    }
}
