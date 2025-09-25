<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <form wire:submit.prevent="generate" class="bg-white rounded-lg shadow border">
            <div class="p-6">
                {{ $this->form }}
                
                <div class="mt-6 flex items-center justify-center">
                    <x-filament::button 
                        type="submit" 
                        size="lg"
                        icon="heroicon-o-play-circle"
                        class="px-8"
                    >
                        Generate Laporan
                    </x-filament::button>
                </div>
            </div>
        </form>

        {{-- Results Section --}}
        @if($generated)
        <div class="bg-white rounded-lg shadow border">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Laporan Cepat</h3>
                
                {{-- Export Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    {{-- Bulatin --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-document-text class="w-6 h-6 text-blue-600" />
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">Bulatin</h4>
                        <p class="text-sm text-gray-600 mb-3">Laporan singkat bulanan</p>
                        <x-filament::button 
                            wire:click="exportBulatin" 
                            size="sm" 
                            color="primary"
                            class="w-full"
                        >
                            Download CSV
                        </x-filament::button>
                    </div>

                    {{-- Tahunan --}}
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-calendar class="w-6 h-6 text-green-600" />
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">Tahunan</h4>
                        <p class="text-sm text-gray-600 mb-3">Laporan tahunan lengkap</p>
                        <x-filament::button 
                            wire:click="exportTahunan" 
                            size="sm" 
                            color="success"
                            class="w-full"
                        >
                            Download Excel
                        </x-filament::button>
                    </div>

                    {{-- Custom --}}
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-purple-600" />
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">Custom</h4>
                        <p class="text-sm text-gray-600 mb-3">Sesuaikan format laporan</p>
                        <x-filament::button 
                            wire:click="exportCustom" 
                            size="sm" 
                            color="warning"
                            class="w-full"
                        >
                            Download CSV
                        </x-filament::button>
                    </div>
                </div>

                {{-- Summary Stats --}}
                @if(!empty($summary))
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_records'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Total Records</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ $summary['total_devices'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Devices</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ isset($summary['avg_ground_temp_c']) ? number_format($summary['avg_ground_temp_c'], 1) : '—' }}°C</div>
                        <div class="text-sm text-gray-600">Avg Temp</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ isset($summary['avg_soil_moisture_pct']) ? number_format($summary['avg_soil_moisture_pct'], 1) : '—' }}%</div>
                        <div class="text-sm text-gray-600">Avg Moisture</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ isset($summary['total_water_usage_log_sum_l']) ? number_format($summary['total_water_usage_log_sum_l'], 1) : '0' }}L</div>
                        <div class="text-sm text-gray-600">Water Usage</div>
                    </div>
                </div>
                @endif

                {{-- Data Preview Table --}}
                @if(!empty($reportData))
                <div class="overflow-hidden border border-gray-200 rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Temp (°C)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture (%)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Battery (V)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(array_slice($reportData, 0, 10) as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $row['tanggal'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $row['device_name'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row['records_count'] ?? 0 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row['ground_temp_avg'] ? number_format($row['ground_temp_avg'], 1) : '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row['soil_moisture_avg'] ? number_format($row['soil_moisture_avg'], 1) : '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row['battery_voltage_avg'] ? number_format($row['battery_voltage_avg'], 2) : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($reportData) > 10)
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <p class="text-sm text-gray-700 text-center">
                            Menampilkan 10 dari {{ count($reportData) }} baris. Download laporan untuk melihat semua data.
                        </p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>