<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Sistem Irigasi Pintar</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet" />
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Styles -->
        <style>
            body { font-family: 'Figtree', sans-serif; }
            .gradient-bg { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
            .card-shadow { box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
            .status-normal { color: #10b981; }
            .status-peringatan { color: #f59e0b; }
            .status-kritis { color: #ef4444; }
            .pulse-animation { animation: pulse 2s infinite; }
            .chart-container { height: 300px; }
            .chart-container-small { height: 250px; }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
        </style>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="bg-gray-50" x-data="smartIrrigationApp()">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold text-green-600">🌱 Irigasi Pintar</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/admin') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                    Dashboard Admin
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                    Masuk
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                        Daftar
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="gradient-bg text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-bold mb-4">🌱 Sistem Irigasi Pintar</h1>
                <p class="text-lg mb-6 max-w-2xl mx-auto">
                    Sistem irigasi pertanian otomatis dengan sensor IoT untuk monitoring real-time 
                    suhu, kelembapan, kelembapan tanah, laju aliran air, dan manajemen penyimpanan air.
                </p>
                <div class="flex justify-center space-x-4 text-sm">
                    <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                        <span class="block font-semibold">Real-time</span>
                        <span class="text-xs">Monitoring</span>
                    </div>
                    <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                        <span class="block font-semibold">Sensor</span>
                        <span class="text-xs">IoT</span>
                    </div>
                    <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                        <span class="block font-semibold">Water</span>
                        <span class="text-xs">Storage</span>
                    </div>
                    <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                        <span class="block font-semibold">Protokol</span>
                        <span class="text-xs">MQTT</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Stats -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Temperature Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Suhu</p>
                            <p class="text-3xl font-bold text-red-500" x-text="stats.temperature + '°C'"></p>
                            <p class="text-sm" :class="getStatusColor(stats.temperatureStatus)" x-text="stats.temperatureStatus"></p>
                        </div>
                        <div class="text-4xl">🌡️</div>
                    </div>
                </div>

                <!-- Humidity Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Kelembapan</p>
                            <p class="text-3xl font-bold text-cyan-500" x-text="stats.humidity + '%'"></p>
                            <p class="text-sm" :class="getStatusColor(stats.humidityStatus)" x-text="stats.humidityStatus"></p>
                        </div>
                        <div class="text-4xl">💧</div>
                    </div>
                </div>

                <!-- Soil Moisture Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Kelembapan Tanah</p>
                            <p class="text-3xl font-bold text-green-500" x-text="stats.soilMoisture + '%'"></p>
                            <p class="text-sm" :class="getStatusColor(stats.soilStatus)" x-text="stats.soilStatus"></p>
                        </div>
                        <div class="text-4xl">🌱</div>
                    </div>
                </div>

                <!-- Water Flow Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Aliran Air</p>
                            <p class="text-3xl font-bold text-purple-500" x-text="stats.waterFlow + ' L/h'"></p>
                            <p class="text-sm" :class="getStatusColor(stats.flowStatus)" x-text="stats.flowStatus"></p>
                        </div>
                        <div class="text-4xl">🚰</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device/Node Status Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Status Node Sensor</h2>
            
            <!-- Loading State -->
            <div x-show="devices.length === 0" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat data node...
                </div>
            </div>

            <!-- Devices Grid -->
            <div x-show="devices.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="device in devices" :key="device.device_id">
                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow border-l-4" 
                         :class="getDeviceBorderColor(device.status)">
                        
                        <!-- Device Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800" x-text="device.device_name || `Node ${device.device_id}`"></h3>
                                <p class="text-sm text-gray-500" x-text="device.location || `Lokasi tidak diketahui`"></p>
                            </div>
                            <div class="text-2xl">
                                <span x-show="device.status === 'normal'">🟢</span>
                                <span x-show="device.status === 'peringatan' || device.status === 'alert'">🟡</span>
                                <span x-show="device.status === 'kritis' || device.status === 'critical'">🔴</span>
                                <span x-show="!device.status || device.status === 'unknown'">⚪</span>
                            </div>
                        </div>

                        <!-- Device Metrics -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">🌡️ Suhu</span>
                                <span class="font-semibold text-red-500" x-text="`${parseFloat(device.temperature || 0).toFixed(1)}°C`"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">💧 Kelembapan</span>
                                <span class="font-semibold text-cyan-500" x-text="`${parseFloat(device.humidity || 0).toFixed(1)}%`"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">🌱 Tanah</span>
                                <span class="font-semibold text-green-500" x-text="`${parseFloat(device.soil_moisture || 0).toFixed(1)}%`"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">🚰 Aliran</span>
                                <span class="font-semibold text-purple-500" x-text="`${parseFloat(device.water_flow || 0).toFixed(1)} L/h`"></span>
                            </div>
                        </div>

                        <!-- Device Status & Last Update -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium px-2 py-1 rounded-full"
                                      :class="getDeviceStatusClass(device.status)"
                                      x-text="getDeviceStatusText(device.status)">
                                </span>
                                <span class="text-xs text-gray-400" x-text="formatTime(device.recorded_at)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Debug Information (remove in production) -->
            {{-- <div x-show="devices.length === 0" class="mt-4 text-center">
                <button @click="loadSampleDevicesData()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Muat Data Sample
                </button>
            </div> --}}
        </div>

        <!-- Water Storage Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Status Penyimpanan Air</h2>
            
            <!-- Water Storage Loading State -->
            <div x-show="waterStorages.length === 0" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat data penyimpanan air...
                </div>
            </div>

            <!-- Water Storage Grid -->
            <div x-show="waterStorages.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="storage in waterStorages" :key="storage.id">
                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow border-l-4" 
                         :class="getWaterStorageBorderColor(storage.status)">
                        
                        <!-- Storage Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800" x-text="storage.tank_name"></h3>
                                <!-- Zone Information -->
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800" 
                                          x-text="storage.zone_name || 'Zona tidak diset'"></span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1" x-text="storage.device_name || 'Tidak terhubung ke device'"></p>
                                <!-- Total Nodes -->
                                <div class="flex items-center mt-1 text-xs text-gray-400" x-show="storage.total_nodes">
                                    <span>📡</span>
                                    <span x-text="`${storage.total_nodes || 0} nodes`" class="ml-1"></span>
                                </div>
                            </div>
                            <div class="text-2xl">
                                <span x-show="storage.status === 'full'">🟢</span>
                                <span x-show="storage.status === 'normal'">🔵</span>
                                <span x-show="storage.status === 'low'">🟡</span>
                                <span x-show="storage.status === 'empty'">🔴</span>
                                <span x-show="storage.status === 'maintenance'">🔧</span>
                            </div>
                        </div>

                        <!-- Zone Description (if available) -->
                        <div x-show="storage.zone_description" class="mb-3 p-2 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-600" x-text="storage.zone_description"></p>
                        </div>

                        <!-- Water Level Progress Bar -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Volume Air</span>
                                <span class="text-sm font-bold" 
                                      :class="getWaterLevelColor(storage.percentage)"
                                      x-text="`${parseFloat(storage.percentage || 0).toFixed(1)}%`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-500" 
                                     :class="getWaterLevelBgColor(storage.percentage)"
                                     :style="`width: ${storage.percentage}%`"></div>
                            </div>
                        </div>

                        <!-- Usage Prediction (if available) -->
                        <div x-show="storage.max_daily_usage && storage.max_daily_usage > 0" class="mb-3 p-2 bg-yellow-50 rounded-lg">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-yellow-700">📊 Prediksi:</span>
                                <span class="font-medium text-yellow-800" 
                                      x-text="`${Math.ceil((storage.current_volume || 0) / (storage.max_daily_usage || 1))} hari`"></span>
                            </div>
                            <div class="text-xs text-yellow-600 mt-1" 
                                 x-text="`Konsumsi: ${storage.max_daily_usage} L/hari`"></div>
                        </div>
                            </div>
                        </div>

                        <!-- Storage Metrics -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">💧 Volume Saat Ini</span>
                                <span class="font-semibold text-blue-600" x-text="`${parseFloat(storage.current_volume || 0).toFixed(1)} L`"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">📊 Kapasitas Total</span>
                                <span class="font-semibold text-gray-700" x-text="`${parseFloat(storage.total_capacity || 0).toFixed(1)} L`"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">🏷️ Status</span>
                                <span class="text-xs font-medium px-2 py-1 rounded-full"
                                      :class="getWaterStorageStatusClass(storage.status)"
                                      x-text="getWaterStorageStatusText(storage.status)">
                                </span>
                            </div>
                        </div>

                        <!-- Storage Notes -->
                        <div x-show="storage.notes" class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500" x-text="storage.notes"></p>
                        </div>

                        <!-- Last Update -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-400">Terakhir diperbarui</span>
                                <span class="text-xs text-gray-400" x-text="formatTime(storage.updated_at)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Water Storage Summary Stats -->
            <div x-show="waterStorages.length > 0" class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600" x-text="waterStorageStats.totalTanks"></div>
                    <div class="text-sm text-gray-600">Total Tangki</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-green-600" x-text="waterStorageStats.totalCapacity + ' L'"></div>
                    <div class="text-sm text-gray-600">Kapasitas Total</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-cyan-600" x-text="waterStorageStats.currentVolume + ' L'"></div>
                    <div class="text-sm text-gray-600">Volume Saat Ini</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold" 
                         :class="waterStorageStats.averagePercentage >= 70 ? 'text-green-600' : waterStorageStats.averagePercentage >= 40 ? 'text-yellow-600' : 'text-red-600'"
                         x-text="waterStorageStats.averagePercentage + '%'"></div>
                    <div class="text-sm text-gray-600">Rata-rata Terisi</div>
                </div>
            </div>
        </div>

        <!-- Irrigation Control Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Kontrol Sistem Irigasi</h2>
            
            <!-- Irrigation Control Loading State -->
            <div x-show="irrigationControls.length === 0" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat data kontrol irigasi...
                </div>
            </div>

            <!-- System Status Overview -->
            <div x-show="irrigationControls.length > 0" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600" x-text="irrigationStatus.system_overview.total_controls"></div>
                    <div class="text-sm text-gray-600">Total Kontrol</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-green-600" x-text="irrigationStatus.system_overview.running_controls"></div>
                    <div class="text-sm text-gray-600">Sedang Berjalan</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600" x-text="irrigationStatus.system_overview.auto_mode_controls"></div>
                    <div class="text-sm text-gray-600">Mode Otomatis</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-orange-600" x-text="irrigationStatus.today_stats.total_runs"></div>
                    <div class="text-sm text-gray-600">Aktivasi Hari Ini</div>
                </div>
            </div>

            <!-- Irrigation Controls Grid -->
            <div x-show="irrigationControls.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="control in irrigationControls" :key="control.id">
                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow border-l-4" 
                         :class="getControlBorderColor(control.status)">
                        
                        <!-- Control Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800" x-text="control.control_name"></h3>
                                <p class="text-sm text-gray-500" x-text="control.device.device_name"></p>
                                <p class="text-xs text-gray-400" x-text="getControlTypeIcon(control.control_type) + ' ' + control.control_type.toUpperCase()"></p>
                            </div>
                            <div class="text-3xl" x-text="control.status_icon"></div>
                        </div>

                        <!-- Control Status & Mode -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Status</div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getControlStatusClass(control.status)"
                                      x-text="control.status.toUpperCase()">
                                </span>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Mode</div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="control.is_auto_mode ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                      x-text="control.is_auto_mode ? '🤖 AUTO' : '👤 MANUAL'">
                                </span>
                            </div>
                        </div>

                        <!-- Control Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">⚡ GPIO Pin</span>
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded font-mono" x-text="control.pin_number || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">⏱️ Durasi Default</span>
                                <span class="font-semibold text-gray-700" x-text="`${control.duration_minutes} menit`"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">📊 Penggunaan Hari Ini</span>
                                <span class="font-semibold text-blue-600" x-text="formatDuration(control.today_duration)"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">📅 Jadwal Aktif</span>
                                <span class="font-semibold text-purple-600" x-text="`${control.active_schedules} jadwal`"></span>
                            </div>
                        </div>

                        <!-- Last Activation Info -->
                        <div x-show="control.last_activated_at" class="mb-4 pt-3 border-t border-gray-100">
                            <div class="text-xs text-gray-500">
                                <span>Terakhir aktif: </span>
                                <span x-text="formatTime(control.last_activated_at)"></span>
                            </div>
                        </div>

                        <!-- Control Actions -->
                        <div class="flex space-x-2">
                            <!-- Start/Stop Button -->
                            <button class="flex-1 px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="control.is_running ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                                    @click="toggleIrrigation(control)"
                                    :disabled="actionLoading">
                                <span x-show="!actionLoading">
                                    <span x-show="control.is_running">🛑 Stop</span>
                                    <span x-show="!control.is_running">▶️ Start</span>
                                </span>
                                <span x-show="actionLoading">⏳</span>
                            </button>
                            
                            <!-- Mode Toggle Button -->
                            <button class="px-3 py-2 text-sm font-medium rounded-lg transition-colors bg-purple-100 text-purple-700 hover:bg-purple-200"
                                    @click="toggleMode(control)"
                                    :disabled="actionLoading">
                                <span x-show="!actionLoading">
                                    <span x-show="control.is_auto_mode">👤</span>
                                    <span x-show="!control.is_auto_mode">🤖</span>
                                </span>
                                <span x-show="actionLoading">⏳</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Running Irrigation Info -->
            <div x-show="irrigationStatus.running_now && irrigationStatus.running_now.length > 0" class="mt-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-green-800 mb-2">🟢 Irigasi Sedang Berjalan</h4>
                    <div class="space-y-2">
                        <template x-for="running in irrigationStatus.running_now" :key="running.control_id">
                            <div class="flex items-center justify-between bg-white rounded p-3">
                                <div>
                                    <span class="font-medium text-gray-800" x-text="running.control_name"></span>
                                    <span class="text-sm text-gray-500 ml-2" x-text="`(${running.device_name})`"></span>
                                </div>
                                <div class="text-sm text-green-600 font-medium">
                                    <span x-text="`${Math.max(0, Math.round(running.duration_so_far))} menit`"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Emergency Stop Button -->
            <div x-show="irrigationStatus.system_overview && irrigationStatus.system_overview.running_controls > 0" class="mt-6 text-center">
                <button class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors"
                        @click="emergencyStop()"
                        :disabled="actionLoading">
                    <span x-show="!actionLoading">🚨 EMERGENCY STOP</span>
                    <span x-show="actionLoading">⏳ Stopping...</span>
                </button>
            </div>

            <!-- Today's Statistics -->
            <div x-show="irrigationStatus.today_stats" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-blue-800 mb-3">📊 Statistik Hari Ini</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" x-text="irrigationStatus.today_stats.total_runs"></div>
                        <div class="text-sm text-blue-700">Total Aktivasi</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" x-text="Math.round(irrigationStatus.today_stats.total_duration_minutes) + ' menit'"></div>
                        <div class="text-sm text-blue-700">Total Durasi</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-cyan-600" x-text="Math.round(irrigationStatus.today_stats.total_water_used) + ' L'"></div>
                        <div class="text-sm text-blue-700">Air Terpakai</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section - Optimized Layout -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Analitik Data Sensor</h2>
            
            <!-- Top Row: Individual Sensor Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Temperature Chart -->
                <div class="bg-white rounded-xl shadow-lg p-4 card-shadow">
                    <h3 class="text-lg font-semibold mb-3">Suhu (24 jam)</h3>
                    <div class="chart-container-small">
                        <canvas id="temperatureChart"></canvas>
                    </div>
                </div>

                <!-- Humidity Chart -->
                <div class="bg-white rounded-xl shadow-lg p-4 card-shadow">
                    <h3 class="text-lg font-semibold mb-3">Kelembapan (24 jam)</h3>
                    <div class="chart-container-small">
                        <canvas id="humidityChart"></canvas>
                    </div>
                </div>

                <!-- Soil Moisture Chart -->
                <div class="bg-white rounded-xl shadow-lg p-4 card-shadow">
                    <h3 class="text-lg font-semibold mb-3">Kelembapan Tanah (24 jam)</h3>
                    <div class="chart-container-small">
                        <canvas id="soilMoistureChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Overview Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Weekly Overview Chart -->
                <div class="bg-white rounded-xl shadow-lg p-4 card-shadow">
                    <h3 class="text-lg font-semibold mb-3">Ringkasan Mingguan</h3>
                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>

                <!-- System Status Chart -->
                <div class="bg-white rounded-xl shadow-lg p-4 card-shadow">
                    <h3 class="text-lg font-semibold mb-3">Status Sistem</h3>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-gray-100 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Fitur Sistem</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">📊</div>
                        <h3 class="text-xl font-semibold mb-3">Monitoring Real-time</h3>
                        <p class="text-gray-600">
                            Monitor suhu, kelembapan, kelembapan tanah, dan aliran air secara real-time dengan update data langsung.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">🔔</div>
                        <h3 class="text-xl font-semibold mb-3">Sistem Peringatan</h3>
                        <p class="text-gray-600">
                            Peringatan otomatis untuk kondisi kritis seperti kelembapan tanah rendah atau pembacaan suhu abnormal.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">📱</div>
                        <h3 class="text-xl font-semibold mb-3">Integrasi MQTT</h3>
                        <p class="text-gray-600">
                            Komunikasi perangkat IoT yang seamless menggunakan protokol MQTT untuk transmisi data yang handal.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">📈</div>
                        <h3 class="text-xl font-semibold mb-3">Data Historis</h3>
                        <p class="text-gray-600">
                            Lacak tren dan pola dengan analisis data historis yang komprehensif dan pelaporan.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">💧</div>
                        <h3 class="text-xl font-semibold mb-3">Manajemen Air</h3>
                        <p class="text-gray-600">
                            Monitor volume air dalam tangki secara real-time dengan peringatan otomatis untuk level air rendah.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">⚡</div>
                        <h3 class="text-xl font-semibold mb-3">Otomasi</h3>
                        <p class="text-gray-600">
                            Kontrol irigasi otomatis berdasarkan pembacaan sensor dan threshold yang telah ditentukan.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 card-shadow">
                        <div class="text-4xl mb-4">🔒</div>
                        <h3 class="text-xl font-semibold mb-3">API Aman</h3>
                        <p class="text-gray-600">
                            Autentikasi berbasis token memastikan akses data yang aman dan komunikasi perangkat yang terlindungi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-xl font-bold mb-3">🌱 Irigasi Pintar</h3>
                        <p class="text-gray-400 text-sm">
                            Sistem irigasi berbasis IoT canggih untuk pertanian modern dengan monitoring real-time dan otomasi.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-3">Fitur</h4>
                        <ul class="text-gray-400 text-sm space-y-1">
                            <li>• Monitoring sensor real-time</li>
                            <li>• Integrasi protokol MQTT</li>
                            <li>• Kontrol irigasi otomatis</li>
                            <li>• Analitik data historis</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-3">Teknologi</h4>
                        <ul class="text-gray-400 text-sm space-y-1">
                            <li>• Framework Laravel</li>
                            <li>• MQTT Daemon</li>
                            <li>• Sensor IoT</li>
                            <li>• Chart Real-time</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-6 pt-6 text-center text-gray-400 text-sm">
                    <p>&copy; 2025 Sistem Irigasi Pintar. Dibangun untuk pertanian modern.</p>
                </div>
            </div>
        </footer>

        <!-- JavaScript for Real-time Data and Charts -->
        <script>
            function smartIrrigationApp() {
                return {
                    stats: {
                        temperature: 0,
                        humidity: 0,
                        soilMoisture: 0,
                        waterFlow: 0,
                        temperatureStatus: 'memuat...',
                        humidityStatus: 'memuat...',
                        soilStatus: 'memuat...',
                        flowStatus: 'memuat...'
                    },
                    devices: [], // Data per device/node
                    waterStorages: [], // Water storage data
                    waterStorageStats: {
                        totalTanks: 0,
                        totalCapacity: 0,
                        currentVolume: 0,
                        averagePercentage: 0
                    },
                    irrigationControls: [], // Irrigation control devices
                    irrigationStatus: {
                        total_controls: 0,
                        active_controls: 0,
                        total_schedules: 0,
                        running_irrigation: [],
                        today_stats: {
                            total_runs: 0,
                            total_duration_minutes: 0,
                            total_water_used: 0
                        }
                    },
                    charts: {},
                    
                    async init() {
                        await this.loadLatestData();
                        await this.loadDevicesData();
                        await this.loadWaterStorageData();
                        await this.loadIrrigationData();
                        await this.initCharts();
                        // Update data every 30 seconds
                        setInterval(() => {
                            this.loadLatestData();
                            this.loadDevicesData();
                            this.loadWaterStorageData();
                            this.loadIrrigationData();
                            this.updateCharts();
                        }, 30000);
                    },

                    async loadLatestData() {
                        try {
                            const response = await fetch('/api/sensor-readings/latest');
                            const data = await response.json();
                            
                            if (data.success && data.data) {
                                const latest = data.data;
                                this.stats.temperature = parseFloat(latest.temperature).toFixed(1);
                                this.stats.humidity = parseFloat(latest.humidity).toFixed(1);
                                this.stats.soilMoisture = parseFloat(latest.soil_moisture).toFixed(1);
                                this.stats.waterFlow = parseFloat(latest.water_flow).toFixed(1);
                                
                                // Update status
                                this.stats.temperatureStatus = this.getStatus(latest.temperature, 20, 30);
                                this.stats.humidityStatus = this.getStatus(latest.humidity, 40, 80);
                                this.stats.soilStatus = this.getStatus(latest.soil_moisture, 30, 70);
                                this.stats.flowStatus = latest.water_flow > 0 ? 'aktif' : 'tidak aktif';
                            } else {
                                // Sample data if no real data available
                                this.loadSampleData();
                            }
                        } catch (error) {
                            console.error('Error loading data:', error);
                            this.loadSampleData();
                        }
                    },

                    loadSampleData() {
                        this.stats.temperature = (Math.random() * 15 + 20).toFixed(1);
                        this.stats.humidity = (Math.random() * 40 + 40).toFixed(1);
                        this.stats.soilMoisture = (Math.random() * 50 + 25).toFixed(1);
                        this.stats.waterFlow = (Math.random() * 300 + 100).toFixed(1);
                        
                        this.stats.temperatureStatus = this.getStatus(this.stats.temperature, 20, 30);
                        this.stats.humidityStatus = this.getStatus(this.stats.humidity, 40, 80);
                        this.stats.soilStatus = this.getStatus(this.stats.soilMoisture, 30, 70);
                        this.stats.flowStatus = 'aktif';
                    },

                    getStatus(value, minGood, maxGood) {
                        if (value < minGood || value > maxGood) return 'kritis';
                        if (value < minGood + 5 || value > maxGood - 5) return 'peringatan';
                        return 'normal';
                    },

                    getStatusColor(status) {
                        return {
                            'status-normal': status === 'normal',
                            'status-peringatan': status === 'peringatan', 
                            'status-kritis': status === 'kritis'
                        };
                    },

                    async initCharts() {
                        await this.initTemperatureChart();
                        await this.initHumidityChart();
                        await this.initSoilMoistureChart();
                        await this.initWeeklyChart();
                        await this.initStatusChart(); // Initialize after device data is loaded
                    },

                    async initTemperatureChart() {
                        const ctx = document.getElementById('temperatureChart').getContext('2d');
                        
                        // Try to fetch real hourly data
                        let chartData = await this.fetchHourlyData('temperature');
                        
                        this.charts.temperature = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Suhu (°C)',
                                    data: chartData.data,
                                    borderColor: 'rgb(239, 68, 68)',
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { 
                                    y: { beginAtZero: false },
                                    x: { display: true }
                                }
                            }
                        });
                    },

                    async initHumidityChart() {
                        const ctx = document.getElementById('humidityChart').getContext('2d');
                        
                        // Try to fetch real hourly data
                        let chartData = await this.fetchHourlyData('humidity');

                        this.charts.humidity = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Kelembapan (%)',
                                    data: chartData.data,
                                    borderColor: 'rgb(6, 182, 212)',
                                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { 
                                    y: { beginAtZero: true, max: 100 },
                                    x: { display: true }
                                }
                            }
                        });
                    },

                    async initSoilMoistureChart() {
                        const ctx = document.getElementById('soilMoistureChart').getContext('2d');
                        
                        // Try to fetch real hourly data
                        let chartData = await this.fetchHourlyData('soil_moisture');

                        this.charts.soilMoisture = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Kelembapan Tanah (%)',
                                    data: chartData.data,
                                    borderColor: 'rgb(34, 197, 94)',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: { 
                                    y: { beginAtZero: true, max: 100 },
                                    x: { display: true }
                                }
                            }
                        });
                    },

                    async initStatusChart() {
                        const ctx = document.getElementById('statusChart').getContext('2d');
                        
                        // Get real status data from devices or use sample data
                        const statusData = await this.getSystemStatusData();
                        
                        this.charts.status = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Normal', 'Peringatan', 'Kritis'],
                                datasets: [{
                                    data: [statusData.normal, statusData.peringatan, statusData.kritis],
                                    backgroundColor: [
                                        'rgba(34, 197, 94, 0.8)',
                                        'rgba(251, 191, 36, 0.8)',
                                        'rgba(239, 68, 68, 0.8)'
                                    ],
                                    borderColor: [
                                        'rgb(34, 197, 94)',
                                        'rgb(251, 191, 36)',
                                        'rgb(239, 68, 68)'
                                    ],
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { 
                                        position: 'bottom', 
                                        labels: { boxWidth: 12 }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((context.parsed * 100) / total).toFixed(1);
                                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    },

                    async getSystemStatusData() {
                        try {
                            // Try to get real status data from API
                            const response = await fetch('/api/sensor-readings/latest-per-device');
                            const data = await response.json();
                            
                            if (data.success && data.data && data.data.length > 0) {
                                const statusCount = { normal: 0, peringatan: 0, kritis: 0 };
                                
                                data.data.forEach(device => {
                                    const status = this.mapApiStatus(device.status, device);
                                    if (statusCount.hasOwnProperty(status)) {
                                        statusCount[status]++;
                                    }
                                });
                                
                                return statusCount;
                            }
                        } catch (error) {
                            console.error('Error fetching status data:', error);
                        }
                        
                        // Fallback to sample data
                        return { normal: 2, peringatan: 1, kritis: 0 };
                    },

                    async initWeeklyChart() {
                        const ctx = document.getElementById('weeklyChart').getContext('2d');
                        
                        // Try to fetch real daily data
                        let chartData = await this.fetchDailyData();
                        
                        this.charts.weekly = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: [
                                    {
                                        label: 'Suhu (°C)',
                                        data: chartData.temperature,
                                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                                        borderColor: 'rgb(239, 68, 68)',
                                        borderWidth: 1,
                                        yAxisID: 'y'
                                    },
                                    {
                                        label: 'Kelembapan (%)',
                                        data: chartData.humidity,
                                        backgroundColor: 'rgba(6, 182, 212, 0.7)',
                                        borderColor: 'rgb(6, 182, 212)',
                                        borderWidth: 1,
                                        yAxisID: 'y1'
                                    },
                                    {
                                        label: 'Kelembapan Tanah (%)',
                                        data: chartData.soilMoisture,
                                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                                        borderColor: 'rgb(34, 197, 94)',
                                        borderWidth: 1,
                                        yAxisID: 'y1'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    legend: { 
                                        position: 'top', 
                                        labels: { boxWidth: 12 }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                label += context.parsed.y.toFixed(1);
                                                if (context.dataset.label === 'Suhu (°C)') {
                                                    label += '°C';
                                                } else {
                                                    label += '%';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Hari'
                                        }
                                    },
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        title: {
                                            display: true,
                                            text: 'Suhu (°C)'
                                        },
                                        beginAtZero: false,
                                        min: 15,
                                        max: 35
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        title: {
                                            display: true,
                                            text: 'Kelembapan (%)'
                                        },
                                        beginAtZero: true,
                                        max: 100,
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                    }
                                }
                            }
                        });
                    },

                    async fetchHourlyData(metric) {
                        try {
                            const response = await fetch('/api/sensor-readings/hourly?hours=12');
                            const result = await response.json();
                            
                            if (result.success && result.data.length > 0) {
                                const labels = result.data.map(item => {
                                    const hour = item.hour.toString().padStart(2, '0');
                                    return `${hour}:00`;
                                });
                                let data;
                                
                                switch(metric) {
                                    case 'temperature':
                                        data = result.data.map(item => parseFloat(item.avg_temperature || 0));
                                        break;
                                    case 'humidity':
                                        data = result.data.map(item => parseFloat(item.avg_humidity || 0));
                                        break;
                                    case 'soil_moisture':
                                        data = result.data.map(item => parseFloat(item.avg_soil_moisture || 0));
                                        break;
                                    default:
                                        data = result.data.map(() => 0);
                                }
                                
                                return { labels, data };
                            }
                        } catch (error) {
                            console.error('Error fetching hourly data:', error);
                        }
                        
                        // Fallback to sample data with more realistic patterns
                        const now = new Date();
                        const hours = Array.from({length: 12}, (_, i) => {
                            const hourAgo = new Date(now.getTime() - ((11 - i) * 60 * 60 * 1000));
                            return hourAgo.getHours().toString().padStart(2, '0') + ':00';
                        });
                        
                        let sampleData;
                        
                        switch(metric) {
                            case 'temperature':
                                // Temperature varies based on time of day
                                sampleData = hours.map((hour, i) => {
                                    const baseTemp = 25;
                                    const timeVariation = Math.sin((i / 12) * Math.PI * 2) * 5;
                                    const randomVariation = (Math.random() - 0.5) * 3;
                                    return Math.max(15, Math.min(35, baseTemp + timeVariation + randomVariation));
                                });
                                break;
                            case 'humidity':
                                // Humidity tends to be higher at night
                                sampleData = hours.map((hour, i) => {
                                    const baseHumidity = 60;
                                    const timeVariation = Math.cos((i / 12) * Math.PI * 2) * 15;
                                    const randomVariation = (Math.random() - 0.5) * 10;
                                    return Math.max(30, Math.min(90, baseHumidity + timeVariation + randomVariation));
                                });
                                break;
                            case 'soil_moisture':
                                // Soil moisture changes more gradually
                                sampleData = hours.map((hour, i) => {
                                    const baseMoisture = 45;
                                    const gradualChange = i * 0.5; // Slight decrease over time
                                    const randomVariation = (Math.random() - 0.5) * 5;
                                    return Math.max(20, Math.min(80, baseMoisture - gradualChange + randomVariation));
                                });
                                break;
                            default:
                                sampleData = hours.map(() => Math.random() * 10);
                        }
                        
                        return { labels: hours, data: sampleData };
                    },

                    async fetchDailyData() {
                        try {
                            const response = await fetch('/api/sensor-readings/daily?days=7');
                            const result = await response.json();
                            
                            if (result.success && result.data.length > 0) {
                                const labels = result.data.map(item => {
                                    const date = new Date(item.date);
                                    return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
                                });
                                
                                const temperature = result.data.map(item => parseFloat(item.avg_temperature || 0));
                                const humidity = result.data.map(item => parseFloat(item.avg_humidity || 0));
                                const soilMoisture = result.data.map(item => parseFloat(item.avg_soil_moisture || 0));
                                
                                return { labels, temperature, humidity, soilMoisture };
                            }
                        } catch (error) {
                            console.error('Error fetching daily data:', error);
                        }
                        
                        // Fallback to sample data with realistic values
                        const days = ['Sen 21', 'Sel 22', 'Rab 23', 'Kam 24', 'Jum 25', 'Sab 26', 'Min 27'];
                        return {
                            labels: days,
                            temperature: [25.2, 26.8, 24.1, 27.3, 25.9, 23.4, 26.5],
                            humidity: [65.3, 58.7, 72.1, 55.9, 61.2, 68.5, 59.8],
                            soilMoisture: [45.2, 42.1, 48.7, 39.3, 51.2, 47.8, 44.5]
                        };
                    },

                    async loadDevicesData() {
                        try {
                            const response = await fetch('/api/sensor-readings/latest-per-device');
                            const data = await response.json();
                            
                            console.log('API Response:', data); // Debug log
                            
                            if (data.success && data.data && data.data.length > 0) {
                                // Map API status to frontend status
                                this.devices = data.data.map(device => ({
                                    ...device,
                                    status: this.mapApiStatus(device.status, device)
                                }));
                                console.log('Devices loaded:', this.devices); // Debug log
                            } else {
                                console.log('No API data, loading sample data'); // Debug log
                                // Sample devices data if no real data available
                                this.loadSampleDevicesData();
                            }
                        } catch (error) {
                            console.error('Error loading devices data:', error);
                            this.loadSampleDevicesData();
                        }
                    },

                    loadSampleDevicesData() {
                        this.devices = [
                            {
                                device_id: 1,
                                device_name: 'Node 1 - Greenhouse A',
                                location: 'Greenhouse A',
                                temperature: (Math.random() * 15 + 20).toFixed(1),
                                humidity: (Math.random() * 40 + 40).toFixed(1),
                                soil_moisture: (Math.random() * 50 + 25).toFixed(1),
                                water_flow: (Math.random() * 300 + 100).toFixed(1),
                                status: 'normal',
                                recorded_at: new Date().toISOString()
                            },
                            {
                                device_id: 2,
                                device_name: 'Node 2 - Greenhouse B',
                                location: 'Greenhouse B',
                                temperature: (Math.random() * 15 + 20).toFixed(1),
                                humidity: (Math.random() * 40 + 40).toFixed(1),
                                soil_moisture: (Math.random() * 50 + 25).toFixed(1),
                                water_flow: (Math.random() * 300 + 100).toFixed(1),
                                status: 'peringatan',
                                recorded_at: new Date(Date.now() - 60000).toISOString()
                            },
                            {
                                device_id: 3,
                                device_name: 'Node 3 - Outdoor Field',
                                location: 'Outdoor Field',
                                temperature: (Math.random() * 15 + 20).toFixed(1),
                                humidity: (Math.random() * 40 + 40).toFixed(1),
                                soil_moisture: (Math.random() * 50 + 25).toFixed(1),
                                water_flow: (Math.random() * 300 + 100).toFixed(1),
                                status: 'kritis',
                                recorded_at: new Date(Date.now() - 300000).toISOString()
                            }
                        ];
                        console.log('Sample devices loaded:', this.devices); // Debug log
                    },

                    // Map API status to frontend status
                    mapApiStatus(apiStatus, device) {
                        // If status is already provided, use it
                        if (apiStatus && ['normal', 'peringatan', 'kritis'].includes(apiStatus)) {
                            return apiStatus;
                        }
                        
                        // Calculate status based on sensor values
                        const temp = parseFloat(device.temperature || 0);
                        const humidity = parseFloat(device.humidity || 0);
                        const soilMoisture = parseFloat(device.soil_moisture || 0);
                        
                        // Critical conditions
                        if (temp < 15 || temp > 35 || humidity < 30 || humidity > 90 || soilMoisture < 20) {
                            return 'kritis';
                        }
                        
                        // Warning conditions
                        if (temp < 18 || temp > 32 || humidity < 40 || humidity > 80 || soilMoisture < 30) {
                            return 'peringatan';
                        }
                        
                        return 'normal';
                    },

                    getDeviceBorderColor(status) {
                        switch(status) {
                            case 'normal': return 'border-green-500';
                            case 'peringatan': 
                            case 'alert': return 'border-yellow-500';
                            case 'kritis': 
                            case 'critical': return 'border-red-500';
                            default: return 'border-gray-300';
                        }
                    },

                    getDeviceStatusClass(status) {
                        switch(status) {
                            case 'normal': return 'bg-green-100 text-green-800';
                            case 'peringatan': 
                            case 'alert': return 'bg-yellow-100 text-yellow-800';
                            case 'kritis': 
                            case 'critical': return 'bg-red-100 text-red-800';
                            default: return 'bg-gray-100 text-gray-800';
                        }
                    },

                    getDeviceStatusText(status) {
                        switch(status) {
                            case 'normal': return 'Normal';
                            case 'peringatan': 
                            case 'alert': return 'Peringatan';
                            case 'kritis': 
                            case 'critical': return 'Kritis';
                            default: return 'Tidak Diketahui';
                        }
                    },

                    // Water Storage Functions
                    async loadWaterStorageData() {
                        try {
                            const response = await fetch('/api/water-storage');
                            const data = await response.json();
                            
                            if (data.success && data.data) {
                                this.waterStorages = data.data;
                                this.calculateWaterStorageStats();
                                console.log('Water storages loaded:', this.waterStorages);
                            } else {
                                // Load sample data if API fails
                                this.loadSampleWaterStorageData();
                            }
                        } catch (error) {
                            console.error('Error loading water storage data:', error);
                            this.loadSampleWaterStorageData();
                        }
                    },

                    loadSampleWaterStorageData() {
                        this.waterStorages = [
                            {
                                id: 1,
                                tank_name: 'Main Water Tank A',
                                zone_name: 'Greenhouse A - Tomato Zone',
                                zone_description: 'Zona tanaman tomat dengan sistem hidroponik. 20 bed tanaman.',
                                device_name: 'Node 1 - Greenhouse A',
                                total_nodes: 2,
                                total_capacity: 1500,
                                current_volume: 1200,
                                percentage: 80,
                                max_daily_usage: 200,
                                status: 'normal',
                                notes: 'Tangki utama untuk zona tomat greenhouse A',
                                updated_at: new Date().toISOString()
                            },
                            {
                                id: 2,
                                tank_name: 'Backup Tank A',
                                zone_name: 'Greenhouse A - Tomato Zone',
                                zone_description: 'Tangki cadangan untuk zona tomat.',
                                device_name: null,
                                total_nodes: 0,
                                total_capacity: 800,
                                current_volume: 150,
                                percentage: 18.75,
                                max_daily_usage: 100,
                                status: 'low',
                                notes: 'Tangki cadangan - perlu diisi ulang',
                                updated_at: new Date(Date.now() - 300000).toISOString()
                            },
                            {
                                id: 3,
                                tank_name: 'Main Water Tank B',
                                zone_name: 'Greenhouse B - Leafy Greens',
                                zone_description: 'Zona sayuran berdaun hijau dengan sistem NFT. 15 channel tanaman.',
                                device_name: 'Node 2 - Greenhouse B',
                                total_nodes: 2,
                                total_capacity: 1000,
                                current_volume: 850,
                                percentage: 85,
                                max_daily_usage: 150,
                                status: 'normal',
                                notes: 'Tangki untuk zona sayuran berdaun',
                                updated_at: new Date(Date.now() - 120000).toISOString()
                            },
                            {
                                id: 4,
                                tank_name: 'Outdoor Field Tank',
                                zone_name: 'Outdoor Field - Mixed Vegetables',
                                zone_description: 'Lahan terbuka untuk tanaman campuran dengan sistem sprinkler.',
                                device_name: 'Node 3 - Outdoor Field',
                                total_nodes: 3,
                                total_capacity: 2000,
                                current_volume: 1800,
                                percentage: 90,
                                max_daily_usage: 300,
                                status: 'normal',
                                notes: 'Tangki besar untuk lahan outdoor',
                                updated_at: new Date(Date.now() - 60000).toISOString()
                            },
                            {
                                id: 5,
                                tank_name: 'Emergency Reserve Tank',
                                zone_name: 'Central Reserve - All Zones',
                                zone_description: 'Tangki cadangan darurat untuk semua zona.',
                                device_name: null,
                                total_nodes: 0,
                                total_capacity: 1200,
                                current_volume: 50,
                                percentage: 4.17,
                                max_daily_usage: 0,
                                status: 'empty',
                                notes: 'PERLU SEGERA DIISI!',
                                updated_at: new Date(Date.now() - 600000).toISOString()
                            }
                        ];
                        this.calculateWaterStorageStats();
                        console.log('Sample water storages loaded:', this.waterStorages);
                    },

                    calculateWaterStorageStats() {
                        this.waterStorageStats.totalTanks = this.waterStorages.length;
                        this.waterStorageStats.totalCapacity = this.waterStorages.reduce((sum, storage) => 
                            sum + parseFloat(storage.total_capacity || 0), 0).toFixed(1);
                        this.waterStorageStats.currentVolume = this.waterStorages.reduce((sum, storage) => 
                            sum + parseFloat(storage.current_volume || 0), 0).toFixed(1);
                        this.waterStorageStats.averagePercentage = this.waterStorageStats.totalCapacity > 0 ? 
                            ((this.waterStorageStats.currentVolume / this.waterStorageStats.totalCapacity) * 100).toFixed(1) : 0;
                    },

                    getWaterStorageBorderColor(status) {
                        switch(status) {
                            case 'full': return 'border-green-600';
                            case 'normal': return 'border-blue-500';
                            case 'low': return 'border-yellow-500';
                            case 'empty': return 'border-red-500';
                            default: return 'border-gray-300';
                        }
                    },

                    getWaterStorageStatusClass(status) {
                        switch(status) {
                            case 'full': return 'bg-green-100 text-green-800';
                            case 'normal': return 'bg-blue-100 text-blue-800';
                            case 'low': return 'bg-yellow-100 text-yellow-800';
                            case 'empty': return 'bg-red-100 text-red-800';
                            default: return 'bg-gray-100 text-gray-800';
                        }
                    },

                    getWaterStorageStatusText(status) {
                        switch(status) {
                            case 'full': return 'Penuh';
                            case 'normal': return 'Normal';
                            case 'low': return 'Rendah';
                            case 'empty': return 'Kosong';
                            default: return 'Tidak Diketahui';
                        }
                    },

                    getWaterLevelColor(percentage) {
                        const perc = parseFloat(percentage);
                        if (perc >= 80) return 'text-green-600';
                        if (perc >= 50) return 'text-blue-600';
                        if (perc >= 25) return 'text-yellow-600';
                        return 'text-red-600';
                    },

                    getWaterLevelBgColor(percentage) {
                        const perc = parseFloat(percentage);
                        if (perc >= 80) return 'bg-green-500';
                        if (perc >= 50) return 'bg-blue-500';
                        if (perc >= 25) return 'bg-yellow-500';
                        return 'bg-red-500';
                    },

                    formatTime(timestamp) {
                        if (!timestamp) return 'N/A';
                        const date = new Date(timestamp);
                        const now = new Date();
                        const diffMs = now - date;
                        const diffMins = Math.floor(diffMs / 60000);
                        
                        if (diffMins < 1) return 'Baru saja';
                        if (diffMins < 60) return `${diffMins} menit lalu`;
                        
                        const diffHours = Math.floor(diffMins / 60);
                        if (diffHours < 24) return `${diffHours} jam lalu`;
                        
                        return date.toLocaleDateString('id-ID');
                    },

                    // Irrigation Control Functions
                    async loadIrrigationData() {
                        try {
                            const [controlsResponse, statusResponse] = await Promise.all([
                                fetch('/api/irrigation-controls'),
                                fetch('/api/irrigation-controls/status')
                            ]);
                            
                            const controlsData = await controlsResponse.json();
                            const statusData = await statusResponse.json();
                            
                            if (controlsData.success && controlsData.data) {
                                this.irrigationControls = controlsData.data;
                            } else {
                                this.loadSampleIrrigationData();
                            }
                            
                            if (statusData.success && statusData.data) {
                                this.irrigationStatus = statusData.data;
                            }
                            
                            console.log('Irrigation data loaded:', {
                                controls: this.irrigationControls,
                                status: this.irrigationStatus
                            });
                        } catch (error) {
                            console.error('Error loading irrigation data:', error);
                            this.loadSampleIrrigationData();
                        }
                    },

                    loadSampleIrrigationData() {
                        this.irrigationControls = [
                            {
                                id: 1,
                                device_id: 1,
                                control_name: 'Main Pump',
                                control_type: 'pump',
                                gpio_pin: 18,
                                status: 'on',
                                mode: 'manual',
                                today_duration: 2.5,
                                device: { name: 'Node 1 - Greenhouse A' },
                                schedules_count: 3,
                                last_activated: new Date(Date.now() - 600000).toISOString()
                            },
                            {
                                id: 2,
                                device_id: 1,
                                control_name: 'Valve A1',
                                control_type: 'valve',
                                gpio_pin: 19,
                                status: 'off',
                                mode: 'automatic',
                                today_duration: 1.2,
                                device: { name: 'Node 1 - Greenhouse A' },
                                schedules_count: 2,
                                last_activated: new Date(Date.now() - 1800000).toISOString()
                            },
                            {
                                id: 3,
                                device_id: 2,
                                control_name: 'Motor B1',
                                control_type: 'motor',
                                gpio_pin: 20,
                                status: 'on',
                                mode: 'automatic',
                                today_duration: 0.8,
                                device: { name: 'Node 2 - Greenhouse B' },
                                schedules_count: 1,
                                last_activated: new Date(Date.now() - 300000).toISOString()
                            },
                            {
                                id: 4,
                                device_id: 2,
                                control_name: 'Valve B2',
                                control_type: 'valve',
                                gpio_pin: 21,
                                status: 'off',
                                mode: 'manual',
                                today_duration: 0,
                                device: { name: 'Node 2 - Greenhouse B' },
                                schedules_count: 0,
                                last_activated: null
                            }
                        ];

                        this.irrigationStatus = {
                            total_controls: 4,
                            active_controls: 2,
                            total_schedules: 6,
                            running_irrigation: [
                                { id: 1, control_name: 'Main Pump', duration: '10 menit' },
                                { id: 3, control_name: 'Motor B1', duration: '5 menit' }
                            ],
                            today_stats: {
                                total_runs: 12,
                                total_duration_minutes: 4.5,
                                total_water_used: 150.5
                            }
                        };
                    },

                    async toggleIrrigation(controlId) {
                        try {
                            const control = this.irrigationControls.find(c => c.id === controlId);
                            if (!control) return;

                            const isOn = control.status === 'on';
                            const endpoint = isOn ? 'stop' : 'start';
                            
                            const response = await fetch(`/api/irrigation-controls/${controlId}/${endpoint}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                }
                            });

                            const data = await response.json();
                            
                            if (data.success) {
                                // Update local state
                                control.status = isOn ? 'off' : 'on';
                                control.last_activated = new Date().toISOString();
                                
                                // Show success message
                                this.showNotification(
                                    `${control.control_name} berhasil ${isOn ? 'dimatikan' : 'dinyalakan'}`,
                                    'success'
                                );
                                
                                // Reload data to sync with server
                                await this.loadIrrigationData();
                            } else {
                                this.showNotification(data.message || 'Gagal mengubah status irigasi', 'error');
                            }
                        } catch (error) {
                            console.error('Error toggling irrigation:', error);
                            this.showNotification('Terjadi kesalahan saat mengubah status irigasi', 'error');
                        }
                    },

                    async toggleMode(controlId) {
                        try {
                            const control = this.irrigationControls.find(c => c.id === controlId);
                            if (!control) return;

                            const newMode = control.mode === 'manual' ? 'automatic' : 'manual';
                            
                            const response = await fetch(`/api/irrigation-controls/${controlId}/mode`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                },
                                body: JSON.stringify({ mode: newMode })
                            });

                            const data = await response.json();
                            
                            if (data.success) {
                                // Update local state
                                control.mode = newMode;
                                
                                // Show success message
                                this.showNotification(
                                    `${control.control_name} berhasil diubah ke mode ${newMode === 'manual' ? 'manual' : 'otomatis'}`,
                                    'success'
                                );
                            } else {
                                this.showNotification(data.message || 'Gagal mengubah mode irigasi', 'error');
                            }
                        } catch (error) {
                            console.error('Error toggling mode:', error);
                            this.showNotification('Terjadi kesalahan saat mengubah mode irigasi', 'error');
                        }
                    },

                    async emergencyStop() {
                        if (!confirm('Apakah Anda yakin ingin menghentikan semua irigasi? Tindakan ini akan menghentikan semua pompa dan valve yang sedang aktif.')) {
                            return;
                        }

                        try {
                            const response = await fetch('/api/irrigation-controls/emergency-stop', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                }
                            });

                            const data = await response.json();
                            
                            if (data.success) {
                                // Update all controls to off status
                                this.irrigationControls.forEach(control => {
                                    control.status = 'off';
                                });
                                
                                this.showNotification('Emergency stop berhasil! Semua irigasi telah dihentikan.', 'success');
                                
                                // Reload data to sync with server
                                await this.loadIrrigationData();
                            } else {
                                this.showNotification(data.message || 'Gagal melakukan emergency stop', 'error');
                            }
                        } catch (error) {
                            console.error('Error emergency stop:', error);
                            this.showNotification('Terjadi kesalahan saat melakukan emergency stop', 'error');
                        }
                    },

                    getControlStatusClass(status) {
                        return status === 'on' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700';
                    },

                    getControlStatusText(status) {
                        return status === 'on' ? 'Aktif' : 'Nonaktif';
                    },

                    getControlTypeIcon(type) {
                        switch(type) {
                            case 'pump': return '🔧';
                            case 'valve': return '🚰';
                            case 'motor': return '⚙️';
                            default: return '🔌';
                        }
                    },

                    getModeClass(mode) {
                        return mode === 'automatic' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800';
                    },

                    getModeText(mode) {
                        return mode === 'automatic' ? 'Otomatis' : 'Manual';
                    },

                    showNotification(message, type = 'info') {
                        // Simple notification - you can enhance this with a proper notification system
                        const className = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-error' : 'alert-info';
                        
                        // Create a simple alert for now - you can replace with toast notification
                        if (type === 'error') {
                            alert('Error: ' + message);
                        } else {
                            alert(message);
                        }
                    },

                    async updateCharts() {
                        // Update charts with real data if available
                        console.log('Memperbarui chart dengan data terbaru...');
                        
                        // Update temperature chart
                        if (this.charts.temperature) {
                            const tempData = await this.fetchHourlyData('temperature');
                            this.charts.temperature.data.labels = tempData.labels;
                            this.charts.temperature.data.datasets[0].data = tempData.data;
                            this.charts.temperature.update();
                        }
                        
                        // Update humidity chart
                        if (this.charts.humidity) {
                            const humidityData = await this.fetchHourlyData('humidity');
                            this.charts.humidity.data.labels = humidityData.labels;
                            this.charts.humidity.data.datasets[0].data = humidityData.data;
                            this.charts.humidity.update();
                        }
                        
                        // Update soil moisture chart
                        if (this.charts.soilMoisture) {
                            const soilData = await this.fetchHourlyData('soil_moisture');
                            this.charts.soilMoisture.data.labels = soilData.labels;
                            this.charts.soilMoisture.data.datasets[0].data = soilData.data;
                            this.charts.soilMoisture.update();
                        }
                        
                        // Update weekly chart
                        if (this.charts.weekly) {
                            const weeklyData = await this.fetchDailyData();
                            this.charts.weekly.data.labels = weeklyData.labels;
                            this.charts.weekly.data.datasets[0].data = weeklyData.temperature;
                            this.charts.weekly.data.datasets[1].data = weeklyData.humidity;
                            this.charts.weekly.data.datasets[2].data = weeklyData.soilMoisture;
                            this.charts.weekly.update();
                        }
                        
                        // Update status chart
                        if (this.charts.status) {
                            const statusData = await this.getSystemStatusData();
                            this.charts.status.data.datasets[0].data = [statusData.normal, statusData.peringatan, statusData.kritis];
                            this.charts.status.update();
                        }
                    }
                }
            }
        </script>
    </body>
</html>
