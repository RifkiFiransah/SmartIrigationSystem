<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationControlResource\Pages;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IrrigationControlResource extends Resource
{
    protected static ?string $model = Device::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Kontrol Node Irigasi';
    
    protected static ?string $modelLabel = 'Kontrol Node Irigasi';
    
    protected static ?string $pluralModelLabel = 'Kontrol Node Irigasi';
    
    protected static ?string $navigationGroup = 'Sistem Irigasi';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_name')
                    ->label('Device Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('connection_state')
                    ->label('Connection')
                    ->badge()
                    ->colors([
                        'success' => 'connected',
                        'danger' => 'disconnected',
                    ]),
                Tables\Columns\TextColumn::make('valve_state')
                    ->label('Valve')
                    ->badge()
                    ->colors([
                        'success' => 'open',
                        'danger' => 'closed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('connection_state')
                    ->options([
                        'connected' => 'Connected',
                        'disconnected' => 'Disconnected',
                    ]),
                Tables\Filters\SelectFilter::make('valve_state')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
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
        ];
    }
}
