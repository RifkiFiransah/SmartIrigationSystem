<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IrrigationSessionResource\Pages;
use App\Models\IrrigationSession;
use App\Models\IrrigationDailyPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class IrrigationSessionResource extends Resource
{
    protected static ?string $model = IrrigationSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Jadwal Node Irigasi';

    protected static ?string $navigationGroup = 'Sistem Irigasi';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Jadwal Node Irigasi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jadwal Node Irigasi';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sesi')
                    ->schema([
                        Forms\Components\Select::make('irrigation_daily_plan_id')
                            ->label('Rencana Harian')
                            ->relationship('plan', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->device->device_name . ' - ' . $record->plan_date->format('Y-m-d')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('session_index')
                            ->label('Nomor Sesi')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(1)
                            ->required(),
                        
                        Forms\Components\TimePicker::make('scheduled_time')
                            ->label('Waktu Jadwal')
                            ->seconds(false)
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'planned' => 'Direncanakan',
                                'pending' => 'Menunggu',
                                'running' => 'Berjalan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'failed' => 'Gagal',
                            ])
                            ->default('planned')
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Volume Air')
                    ->schema([
                        Forms\Components\TextInput::make('planned_volume_l')
                            ->label('Volume Rencana (L)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->suffix('L')
                            ->required(),
                        
                        Forms\Components\TextInput::make('adjusted_volume_l')
                            ->label('Volume Disesuaikan (L)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->suffix('L')
                            ->helperText('Volume yang disesuaikan berdasarkan kondisi cuaca'),
                        
                        Forms\Components\TextInput::make('actual_volume_l')
                            ->label('Volume Aktual (L)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->suffix('L')
                            ->helperText('Volume yang benar-benar terpakai'),
                    ])->columns(3),
                
                Forms\Components\Section::make('Waktu Eksekusi')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Mulai')
                            ->seconds(false),
                        
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Selesai')
                            ->seconds(false),
                    ])->columns(2),
                
                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\KeyValue::make('meta')
                            ->label('Data Tambahan')
                            ->keyLabel('Kunci')
                            ->valueLabel('Nilai'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.plan_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('session_index')
                    ->label('Sesi')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('planned_volume_l')
                    ->label('Rencana')
                    ->suffix(' L')
                    ->numeric(2)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('adjusted_volume_l')
                    ->label('Disesuaikan')
                    ->suffix(' L')
                    ->numeric(2)
                    ->placeholder('-')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('actual_volume_l')
                    ->label('Aktual')
                    ->suffix(' L')
                    ->numeric(2)
                    ->placeholder('-')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planned' => 'gray',
                        'pending' => 'warning',
                        'running' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Direncanakan',
                        'pending' => 'Menunggu',
                        'running' => 'Berjalan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'failed' => 'Gagal',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d/m H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime('d/m H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => 'Direncanakan',
                        'pending' => 'Menunggu',
                        'running' => 'Berjalan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'failed' => 'Gagal',
                    ]),
                
                Filter::make('plan_date')
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
                                fn (Builder $query, $date): Builder => $query->whereHas('plan', function ($q) use ($date) {
                                    $q->whereDate('plan_date', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereHas('plan', function ($q) use ($date) {
                                    $q->whereDate('plan_date', '<=', $date);
                                }),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('plan.plan_date', 'desc');
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
            'index' => Pages\ListIrrigationSessions::route('/'),
            'create' => Pages\CreateIrrigationSession::route('/create'),
            'edit' => Pages\EditIrrigationSession::route('/{record}/edit'),
        ];
    }
}
