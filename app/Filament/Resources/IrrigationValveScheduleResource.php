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
    protected static ?string $navigationLabel = 'Jadwal Katup';
    protected static ?string $modelLabel = 'Jadwal Katup';
    protected static ?string $pluralModelLabel = 'Jadwal Katup';

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
                ->label('Node UID')
                ->options(fn () => IrrigationValve::query()->pluck('node_uid', 'node_uid')->toArray())
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
                Tables\Columns\TextColumn::make('node_uid')->label('Node')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_time')->label('Start')->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Minutes')->sortable(),
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
