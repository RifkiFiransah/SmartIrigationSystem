<?php

namespace App\Filament\Resources;

use App\Models\IrrigationValveLog;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class IrrigationValveLogResource extends Resource
{
    protected static ?string $model = IrrigationValveLog::class;
    protected static ?string $navigationGroup = 'Sistem Irigasi';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Log Katup';
    protected static ?string $modelLabel = 'Log Katup';
    protected static ?string $pluralModelLabel = 'Log Katup';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('node_uid')->label('Node')->searchable(),
            Tables\Columns\TextColumn::make('action')->badge(),
            Tables\Columns\TextColumn::make('trigger')->badge(),
            Tables\Columns\TextColumn::make('duration_seconds')->label('Duration (s)'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => IrrigationValveLogResource\Pages\ListIrrigationValveLogs::route('/'),
        ];
    }
}
