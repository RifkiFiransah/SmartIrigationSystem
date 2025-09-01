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
                Forms\Components\TextInput::make('tank_name')
                    ->label('Tank Name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Select::make('device_id')
                    ->label('Device')
                    ->relationship('device', 'device_name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                
                Forms\Components\TextInput::make('total_capacity')
                    ->label('Total Capacity (Liters)')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->suffix('L'),
                
                Forms\Components\TextInput::make('current_volume')
                    ->label('Current Volume (Liters)')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->suffix('L')
                    ->default(0),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'normal' => 'Normal',
                        'low' => 'Low',
                        'empty' => 'Empty',
                        'full' => 'Full',
                    ])
                    ->default('normal')
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tank_name')
                    ->label('Tank Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('device.device_name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_capacity')
                    ->label('Total Capacity')
                    ->suffix(' L')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_volume')
                    ->label('Current Volume')
                    ->suffix(' L')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Percentage')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 25 => 'primary',
                        $state >= 10 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'info' => 'normal',
                        'success' => 'full',
                        'warning' => 'low',
                        'danger' => 'empty',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'normal' => 'Normal',
                        'low' => 'Low',
                        'empty' => 'Empty',
                        'full' => 'Full',
                    ]),
                
                Tables\Filters\SelectFilter::make('device')
                    ->relationship('device', 'device_name'),
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
