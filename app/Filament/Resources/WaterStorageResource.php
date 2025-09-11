<?php
namespace App\Filament\Resources;

use App\Filament\Resources\WaterStorageResource\Pages;
use App\Models\WaterStorage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;

class WaterStorageResource extends Resource
{
    protected static ?string $model = WaterStorage::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Tangki Air';
    protected static ?string $modelLabel = 'Tangki Air';
    protected static ?string $pluralModelLabel = 'Tangki Air';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tangki')
                ->description('Informasi dasar tangki air')
                ->schema([
                    Forms\Components\TextInput::make('tank_name')
                        ->label('Nama Tangki')
                        ->default('Tangki Utama')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('capacity_liters')
                        ->label('Kapasitas Total (L)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->suffix('L'),

                    Forms\Components\TextInput::make('current_volume_liters')
                        ->label('Volume Saat Ini (L)')
                        ->readOnly()
                        ->dehydrated()
                        ->numeric()
                        ->suffix('L')
                        ->helperText('Otomatis dihitung dari ketinggian air * kapasitas'),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'normal' => '🟢 Normal',
                            'low' => '🟡 Rendah',
                            'empty' => '🔴 Kosong',
                            'full' => '🔵 Penuh',
                            'maintenance' => '🔧 Pemeliharaan',
                        ])
                        ->default('normal'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Kalibrasi Sensor Ketinggian Air')
                ->description('Atur tinggi fisik tangki dan offset sensor untuk perhitungan volume otomatis')
                ->schema([
                    Forms\Components\TextInput::make('height_cm')
                        ->label('Tinggi Tangki (cm)')
                        ->numeric()
                        ->suffix('cm')
                        ->minValue(0)
                        ->helperText('Tinggi penuh dari dasar sampai penuh'),
                    Forms\Components\TextInput::make('calibration_offset_cm')
                        ->label('Offset Kalibrasi (cm)')
                        ->numeric()
                        ->default(0)
                        ->suffix('cm')
                        ->helperText('Jarak sensor ke permukaan air saat benar-benar kosong'),
                    Forms\Components\TextInput::make('last_height_cm')
                        ->label('Ketinggian Terakhir (cm)')
                        ->disabled()
                        ->suffix('cm'),
                ])->columns(3),

            Forms\Components\Section::make('Catatan')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->placeholder('Catatan tambahan'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tank_name')
                    ->label('Tangki')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('capacity_liters')
                    ->label('Kapasitas')
                    ->suffix(' L')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_volume_liters')
                    ->label('Volume Saat Ini')
                    ->suffix(' L')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fill_percent')
                    ->label('Level Pengisian')
                    ->state(fn (WaterStorage $record) => $record->capacity_liters > 0
                        ? round(($record->current_volume_liters / $record->capacity_liters) * 100)
                        : 0)
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 50 => 'primary',
                        $state >= 25 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter()
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_capacity')
                    ->label('Rendah (<25%)')
                    ->query(fn (Builder $q) => $q->whereRaw('(current_volume_liters / capacity_liters) * 100 < 25'))
                    ->toggle(),

                Tables\Filters\Filter::make('critical_capacity')
                    ->label('Kritis (<10%)')
                    ->query(fn (Builder $q) => $q->whereRaw('(current_volume_liters / capacity_liters) * 100 < 10'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => WaterStorage::count() > 1), // cegah hapus jika hanya satu
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => WaterStorage::count() > 1),
                ]),
            ]);
    }

    // Batasi hanya satu record (satu tangki)
    public static function canCreate(): bool
    {
        return WaterStorage::count() === 0;
    }

    public static function getRelations(): array
    {
        return [
            WaterStorageResource\RelationManagers\UsageLogsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            WaterStorageResource\Widgets\WaterStorageOverview::class,
            WaterStorageResource\Widgets\WaterUsageSummary::class,
            WaterStorageResource\Widgets\WaterUsageHistoryChart::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) WaterStorage::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
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