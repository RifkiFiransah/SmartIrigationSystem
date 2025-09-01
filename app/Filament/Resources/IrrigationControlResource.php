<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationControlResource\Pages;
use App\Filament\Resources\IrrigationControlResource\RelationManagers;
use App\Models\IrrigationControl;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;

class IrrigationControlResource extends Resource
{
    protected static ?string $model = IrrigationControl::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Irrigation Controls';
    
    protected static ?string $modelLabel = 'Irrigation Control';
    
    protected static ?string $pluralModelLabel = 'Irrigation Controls';
    
    protected static ?string $navigationGroup = 'Irrigation System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('control_name')
                            ->label('Control Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Pump Zone A, Valve Garden 1'),
                            
                        Forms\Components\Select::make('control_type')
                            ->label('Control Type')
                            ->required()
                            ->options([
                                'pump' => 'Pump',
                                'valve' => 'Valve', 
                                'motor' => 'Motor',
                                'sprinkler' => 'Sprinkler',
                                'solenoid' => 'Solenoid Valve',
                            ])
                            ->default('pump'),
                            
                        Forms\Components\Select::make('device_id')
                            ->label('Device')
                            ->relationship('device', 'device_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('device_name')
                                    ->required(),
                                Forms\Components\TextInput::make('device_id')
                                    ->required(),
                                Forms\Components\TextInput::make('location'),
                            ]),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Hardware Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('pin_number')
                            ->label('GPIO Pin')
                            ->placeholder('e.g., GPIO_2, PIN_14')
                            ->helperText('Hardware pin untuk kontroling device'),
                            
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Default Duration (Minutes)')
                            ->numeric()
                            ->default(30)
                            ->suffix('minutes')
                            ->helperText('Default irrigation duration'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Mode')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Current Status')
                            ->required()
                            ->options([
                                'off' => '🔴 Off',
                                'on' => '🟢 On',
                                'auto' => '🤖 Auto',
                                'manual' => '👤 Manual',
                                'error' => '❌ Error',
                            ])
                            ->default('off'),
                            
                        Forms\Components\Select::make('mode')
                            ->label('Control Mode')
                            ->required()
                            ->options([
                                'manual' => '👤 Manual Control',
                                'auto' => '🤖 Automatic Control',
                            ])
                            ->default('manual'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->required()
                            ->default(true)
                            ->helperText('Enable/disable this control'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->label('Custom Settings')
                            ->keyLabel('Setting Name')
                            ->valueLabel('Setting Value')
                            ->addActionLabel('Add Setting')
                            ->helperText('Additional configuration for this control'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Description of this irrigation control...'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('control_name')
                    ->label('Control Name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('control_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'pump',
                        'success' => 'valve',
                        'warning' => 'motor',
                        'info' => 'sprinkler',
                        'secondary' => 'solenoid',
                    ]),
                    
                Tables\Columns\TextColumn::make('device.device_name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pin_number')
                    ->label('Pin')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'on',
                        'danger' => 'off',
                        'warning' => 'auto',
                        'info' => 'manual',
                        'danger' => 'error',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'on' => '🟢 On',
                        'off' => '🔴 Off',
                        'auto' => '🤖 Auto',
                        'manual' => '👤 Manual',
                        'error' => '❌ Error',
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
                        'auto' => '🤖 Auto',
                        'manual' => '👤 Manual',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->numeric()
                    ->sortable()
                    ->suffix(' min')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('last_activated_at')
                    ->label('Last Activated')
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
                    
                Tables\Columns\TextColumn::make('today_duration')
                    ->label('Today Usage')
                    ->state(function (IrrigationControl $record): string {
                        $seconds = $record->today_duration;
                        if ($seconds == 0) return '0 min';
                        
                        $hours = intval($seconds / 3600);
                        $minutes = intval(($seconds % 3600) / 60);
                        
                        if ($hours > 0) {
                            return "{$hours}h {$minutes}m";
                        }
                        return "{$minutes} min";
                    })
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('control_type')
                    ->label('Type')
                    ->options([
                        'pump' => 'Pump',
                        'valve' => 'Valve',
                        'motor' => 'Motor',
                        'sprinkler' => 'Sprinkler',
                        'solenoid' => 'Solenoid Valve',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'off' => 'Off',
                        'on' => 'On',
                        'auto' => 'Auto',
                        'manual' => 'Manual',
                        'error' => 'Error',
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
                Tables\Actions\Action::make('toggle')
                    ->label('Start/Stop')
                    ->icon(fn (IrrigationControl $record): string => 
                        $record->isRunning() ? 'heroicon-o-stop' : 'heroicon-o-play'
                    )
                    ->color(fn (IrrigationControl $record): string => 
                        $record->isRunning() ? 'danger' : 'success'
                    )
                    ->requiresConfirmation()
                    ->modalHeading(fn (IrrigationControl $record): string => 
                        $record->isRunning() ? 'Stop Irrigation?' : 'Start Irrigation?'
                    )
                    ->modalDescription(fn (IrrigationControl $record): string => 
                        $record->isRunning() 
                            ? "This will stop the irrigation for {$record->control_name}."
                            : "This will start the irrigation for {$record->control_name} for {$record->duration_minutes} minutes."
                    )
                    ->action(function (IrrigationControl $record): void {
                        try {
                            if ($record->isRunning()) {
                                // Stop irrigation
                                $record->update([
                                    'status' => 'off',
                                    'last_deactivated_at' => now(),
                                ]);
                                
                                Notification::make()
                                    ->title('Irrigation Stopped')
                                    ->body("Successfully stopped {$record->control_name}")
                                    ->success()
                                    ->send();
                            } else {
                                // Start irrigation
                                $record->update([
                                    'status' => 'on',
                                    'mode' => 'manual',
                                    'last_activated_at' => now(),
                                ]);
                                
                                Notification::make()
                                    ->title('Irrigation Started')
                                    ->body("Successfully started {$record->control_name} for {$record->duration_minutes} minutes")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to toggle irrigation: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                    
                Tables\Actions\Action::make('toggle_mode')
                    ->label('Toggle Mode')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (IrrigationControl $record): void {
                        $newMode = $record->mode === 'auto' ? 'manual' : 'auto';
                        $record->update(['mode' => $newMode]);
                        
                        Notification::make()
                            ->title('Mode Changed')
                            ->body("Changed {$record->control_name} to {$newMode} mode")
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('emergency_stop')
                        ->label('Emergency Stop All')
                        ->icon('heroicon-o-stop')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Emergency Stop All Selected Controls?')
                        ->modalDescription('This will immediately stop all selected irrigation controls.')
                        ->action(function ($records): void {
                            $stopped = 0;
                            foreach ($records as $record) {
                                if ($record->isRunning()) {
                                    $record->update([
                                        'status' => 'off',
                                        'last_deactivated_at' => now(),
                                    ]);
                                    $stopped++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Emergency Stop Complete')
                                ->body("Stopped {$stopped} irrigation controls")
                                ->success()
                                ->send();
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
