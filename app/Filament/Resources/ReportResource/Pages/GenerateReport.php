<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Device;
use App\Services\SystemReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryReportExport;

class GenerateReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ReportResource::class;
    protected static string $view = 'filament.resources.report-resource.pages.generate-report';
    protected static ?string $title = 'Generate Laporan';

    public ?array $data = [];
    public array $reportData = [];
    public array $summary = [];
    public bool $generated = false;

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->subDays(6)->startOfDay(),
            'date_to' => now()->endOfDay(),
            'device_ids' => [],
            'only_active' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buat Laporan Sistem Irigasi')
                    ->description('Pilih periode dan device untuk generate laporan sistem')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date_from')
                                    ->label('Pilih Bulan')
                                    ->default(now()->subDays(6))
                                    ->maxDate(fn ($get) => $get('date_to')),
                                Forms\Components\DatePicker::make('date_to')
                                    ->label('Sampai Tanggal')
                                    ->default(now())
                                    ->minDate(fn ($get) => $get('date_from')),
                            ]),
                        Forms\Components\Select::make('device_ids')
                            ->label('Tampilan Device')
                            ->multiple()
                            ->options(function (callable $get) {
                                $q = Device::query()->orderBy('device_name');
                                if ($get('only_active')) {
                                    $q->where('is_active', true);
                                }
                                return $q->pluck('device_name', 'id');
                            })
                            ->placeholder('Tampilkan di Browser'),
                        Forms\Components\Toggle::make('only_active')
                            ->label('Sertakan grafik dan visualisasi')
                            ->inline(false),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        
        $from = Carbon::parse($data['date_from'])->startOfDay();
        $to = Carbon::parse($data['date_to'])->endOfDay();
        $deviceIds = array_filter($data['device_ids'] ?? []);
        $onlyActive = (bool)($data['only_active'] ?? false);

        if ($from->gt($to)) {
            Notification::make()->danger()->title('Range tanggal tidak valid')->send();
            return;
        }

        $cacheKey = 'system_report:' . md5(json_encode([$from, $to, $deviceIds, $onlyActive]));
        $service = app(SystemReportService::class);
        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($service, $from, $to, $deviceIds, $onlyActive) {
            return $service->buildAggregation($from, $to, $deviceIds, $onlyActive);
        });

        $this->reportData = $result['rows'];
        $this->summary = $result['summary'];
        $this->generated = true;

        Notification::make()->success()->title('Laporan berhasil dibuat')->send();
    }

    public function exportBulatin(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->generated) {
            $this->generate();
        }

        $filename = 'bulatin_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'tanggal','device_name','records_count',
            'ground_temp_avg','soil_moisture_avg','battery_voltage_avg'
        ];

        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($this->reportData as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $line[] = $row[$col] ?? '';
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function exportTahunan()
    {
        if (!$this->generated) {
            $this->generate();
        }
        return Excel::download(new SummaryReportExport($this->reportData), 'tahunan_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportCustom(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->generated) {
            $this->generate();
        }

        $filename = 'custom_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv', 
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'tanggal','device_name','ground_temp_avg','soil_moisture_avg',
            'water_height_avg','irrigation_usage_delta_l','water_usage_log_sum_l'
        ];

        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($this->reportData as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $line[] = $row[$col] ?? '';
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, 200, $headers);
    }
}
