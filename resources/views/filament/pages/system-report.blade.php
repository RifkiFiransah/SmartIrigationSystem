@php /** @var \App\Filament\Pages\SystemReport $this */ @endphp
<x-filament::page>
    <div class="space-y-6">
        <form wire:submit.prevent="generate" class="p-4 bg-white rounded shadow border">
            {{ $this->form }}
            <div class="mt-4 flex items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-play-circle">Generate</x-filament::button>
                @if($generated)
                    <x-filament::button color="success" wire:click="exportSummaryExcel" icon="heroicon-o-document-arrow-down">Export Excel</x-filament::button>
                    <x-filament::button color="danger" wire:click="exportSummaryPdf" icon="heroicon-o-document-text">Export PDF</x-filament::button>
                @endif
            </div>
        </form>

        @if($generated)
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @php
                    $statCards = [
                        ['label' => 'Total Records', 'value' => $summary['total_records'] ?? 0],
                        ['label' => 'Total Devices', 'value' => $summary['total_devices'] ?? 0],
                        ['label' => 'Total Irrigation (Δ L)', 'value' => isset($summary['total_irrigation_usage_delta_l']) ? number_format($summary['total_irrigation_usage_delta_l'], 2) : '—'],
                        ['label' => 'Total Water Log (L)', 'value' => isset($summary['total_water_usage_log_sum_l']) ? number_format($summary['total_water_usage_log_sum_l'], 2) : '—'],
                        ['label' => 'Avg Soil Moist (%)', 'value' => isset($summary['avg_soil_moisture_pct']) ? number_format($summary['avg_soil_moisture_pct'], 2) : '—'],
                    ];
                @endphp
                @foreach($statCards as $c)
                    <div class="p-4 bg-white border rounded shadow-sm">
                        <div class="text-[11px] font-medium text-gray-500 tracking-wide uppercase">{{ $c['label'] }}</div>
                        <div class="mt-1 text-lg font-semibold text-gray-800">{{ $c['value'] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Trend Charts --}}
            @if(!empty($chartData['labels']))
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="p-4 bg-white border rounded shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Trend Suhu Tanah (°C)</h3>
                    <canvas id="groundTempChart" width="400" height="200"></canvas>
                </div>
                <div class="p-4 bg-white border rounded shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Trend Kelembaban Tanah (%)</h3>
                    <canvas id="soilMoistureChart" width="400" height="200"></canvas>
                </div>
                <div class="p-4 bg-white border rounded shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Penggunaan Air (L)</h3>
                    <canvas id="waterUsageChart" width="400" height="200"></canvas>
                </div>
                <div class="p-4 bg-white border rounded shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-3">Jumlah Record</h3>
                    <canvas id="recordsChart" width="400" height="200"></canvas>
                </div>
            </div>
            @endif

            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Export Ringkasan</h3>
                        <p class="text-xs text-gray-500 mt-1">Agregasi harian per device periode terpilih.</p>
                    </div>
                    <div class="mt-3">
                        <x-filament::button size="sm" color="success" wire:click="exportSummaryExcel" icon="heroicon-o-document-arrow-down">Excel</x-filament::button>
                        <x-filament::button size="sm" color="danger" class="ml-2" wire:click="exportSummaryPdf" icon="heroicon-o-document-text">PDF</x-filament::button>
                    </div>
                </div>
                <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Export Devices</h3>
                        <p class="text-xs text-gray-500 mt-1">Daftar device & status.</p>
                    </div>
                    <div class="mt-3">
                        <x-filament::button size="sm" color="success" wire:click="exportDevicesExcel" icon="heroicon-o-document-arrow-down">Excel</x-filament::button>
                        <x-filament::button size="sm" color="danger" class="ml-2" wire:click="exportDevicesPdf" icon="heroicon-o-document-text">PDF</x-filament::button>
                    </div>
                </div>
                <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Export Tangki Air</h3>
                        <p class="text-xs text-gray-500 mt-1">Data kapasitas & level.</p>
                    </div>
                    <div class="mt-3">
                        <x-filament::button size="sm" color="success" wire:click="exportWaterStoragesExcel" icon="heroicon-o-document-arrow-down">Excel</x-filament::button>
                        <x-filament::button size="sm" color="danger" class="ml-2" wire:click="exportWaterStoragesPdf" icon="heroicon-o-document-text">PDF</x-filament::button>
                    </div>
                </div>
                <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Export Sensor Data</h3>
                        <p class="text-xs text-gray-500 mt-1">Data mentah (maks 50k baris).</p>
                    </div>
                    <div class="mt-3">
                        <x-filament::button size="sm" color="success" wire:click="exportSensorDataExcel" icon="heroicon-o-document-arrow-down">Excel</x-filament::button>
                        <x-filament::button size="sm" color="danger" class="ml-2" wire:click="exportSensorDataPdf" icon="heroicon-o-document-text">PDF</x-filament::button>
                    </div>
                </div>
                <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Export Water Usage Logs</h3>
                        <p class="text-xs text-gray-500 mt-1">Log penggunaan air harian.</p>
                    </div>
                    <div class="mt-3">
                        <x-filament::button size="sm" color="success" wire:click="exportWaterUsageLogsExcel" icon="heroicon-o-document-arrow-down">Excel</x-filament::button>
                        <x-filament::button size="sm" color="danger" class="ml-2" wire:click="exportWaterUsageLogsPdf" icon="heroicon-o-document-text">PDF</x-filament::button>
                    </div>
                </div>

            </div>

            <div class="overflow-auto border rounded bg-white shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left">
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Device</th>
                            <th class="px-3 py-2">Records</th>
                            <th class="px-3 py-2">Temp Avg</th>
                            <th class="px-3 py-2">Temp Min</th>
                            <th class="px-3 py-2">Temp Max</th>
                            <th class="px-3 py-2">Soil Avg</th>
                            <th class="px-3 py-2">Soil Min</th>
                            <th class="px-3 py-2">Soil Max</th>
                            <th class="px-3 py-2">Water H Avg</th>
                            <th class="px-3 py-2">Batt V Avg</th>
                            <th class="px-3 py-2">Batt V Min</th>
                            <th class="px-3 py-2">Irr Δ (L)</th>
                            <th class="px-3 py-2">Water Log (L)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-3 py-1 whitespace-nowrap">{{ $r['tanggal'] }}</td>
                                <td class="px-3 py-1 whitespace-nowrap">{{ $r['device_name'] }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['records_count'] ?? 0 }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['ground_temp_avg'] ? number_format($r['ground_temp_avg'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['ground_temp_min'] ? number_format($r['ground_temp_min'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['ground_temp_max'] ? number_format($r['ground_temp_max'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['soil_moisture_avg'] ? number_format($r['soil_moisture_avg'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['soil_moisture_min'] ? number_format($r['soil_moisture_min'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['soil_moisture_max'] ? number_format($r['soil_moisture_max'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['water_height_avg'] ? number_format($r['water_height_avg'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['battery_voltage_avg'] ? number_format($r['battery_voltage_avg'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ $r['battery_voltage_min'] ? number_format($r['battery_voltage_min'], 2) : '—' }}</td>
                                <td class="px-3 py-1 text-right">{{ isset($r['irrigation_usage_delta_l']) ? number_format($r['irrigation_usage_delta_l'], 3) : '0.000' }}</td>
                                <td class="px-3 py-1 text-right">{{ isset($r['water_usage_log_sum_l']) ? number_format($r['water_usage_log_sum_l'], 2) : '0.00' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="14" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Chart.js Scripts --}}
    @if($generated && !empty($chartData['labels']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            
            // Common chart configuration
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { 
                        title: { display: true, text: 'Tanggal' }
                    },
                    y: { 
                        beginAtZero: true 
                    }
                }
            };

            // Ground Temperature Chart
            if (document.getElementById('groundTempChart')) {
                new Chart(document.getElementById('groundTempChart'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Suhu Tanah (°C)',
                            data: chartData.datasets.ground_temp,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            ...commonOptions.scales,
                            y: { ...commonOptions.scales.y, title: { display: true, text: 'Suhu (°C)' } }
                        }
                    }
                });
            }

            // Soil Moisture Chart
            if (document.getElementById('soilMoistureChart')) {
                new Chart(document.getElementById('soilMoistureChart'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Kelembaban Tanah (%)',
                            data: chartData.datasets.soil_moisture,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            ...commonOptions.scales,
                            y: { ...commonOptions.scales.y, title: { display: true, text: 'Kelembaban (%)' } }
                        }
                    }
                });
            }

            // Water Usage Chart
            if (document.getElementById('waterUsageChart')) {
                new Chart(document.getElementById('waterUsageChart'), {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Penggunaan Air (L)',
                            data: chartData.datasets.water_usage,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            ...commonOptions.scales,
                            y: { ...commonOptions.scales.y, title: { display: true, text: 'Volume (L)' } }
                        }
                    }
                });
            }

            // Records Count Chart
            if (document.getElementById('recordsChart')) {
                new Chart(document.getElementById('recordsChart'), {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Jumlah Record',
                            data: chartData.datasets.records_count,
                            backgroundColor: 'rgba(255, 206, 86, 0.5)',
                            borderColor: 'rgb(255, 206, 86)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            ...commonOptions.scales,
                            y: { ...commonOptions.scales.y, title: { display: true, text: 'Jumlah' } }
                        }
                    }
                });
            }
        });
    </script>
    @endif
</x-filament::page>
