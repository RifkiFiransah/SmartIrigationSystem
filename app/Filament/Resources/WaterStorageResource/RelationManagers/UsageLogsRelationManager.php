<?php
namespace App\Filament\Resources\WaterStorageResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsageLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'usageLogs';
    protected static ?string $title = 'Riwayat Penggunaan Air';
    protected static ?string $modelLabel = 'Log Penggunaan';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('usage_date')
                ->label('Tanggal')
                ->default(now())
                ->required(),
            Forms\Components\TextInput::make('volume_used_l')
                ->label('Volume (L)')
                ->numeric()
                ->required()
                ->suffix('L'),
            Forms\Components\Select::make('source')
                ->label('Sumber')
                ->options([
                    'irrigation' => 'Irigasi',
                    'manual' => 'Manual',
                    'adjust' => 'Penyesuaian',
                    'auto_calc' => 'Otomatis',
                ])
                ->default('manual')
                ->required(),
            Forms\Components\Textarea::make('meta.notes')
                ->label('Catatan')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('usage_date')
                ->label('Tanggal')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('volume_used_l')
                ->label('Volume (L)')
                ->suffix(' L')
                ->alignRight()
                ->sortable(),
            TextColumn::make('source')
                ->label('Sumber')
                ->badge()
                ->colors([
                    'primary' => 'irrigation',
                    'warning' => 'manual',
                    'info' => 'adjust',
                    'success' => 'auto_calc',
                ])
                ->formatStateUsing(fn ($state) => match($state) {
                    'irrigation' => 'Irigasi',
                    'manual' => 'Manual',
                    'adjust' => 'Penyesuaian',
                    'auto_calc' => 'Otomatis',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Dicatat')
                ->since()
                ->sortable(),
        ])->headerActions([
            Tables\Actions\CreateAction::make()
                ->label('Tambah Log Manual')
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->defaultSort('usage_date', 'desc');
    }
}
