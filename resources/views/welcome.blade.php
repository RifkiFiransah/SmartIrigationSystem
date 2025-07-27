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
                    suhu, kelembapan, kelembapan tanah, dan laju aliran air.
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
                    charts: {},
                    
                    async init() {
                        await this.loadLatestData();
                        await this.loadDevicesData();
                        await this.initCharts();
                        // Update data every 30 seconds
                        setInterval(() => {
                            this.loadLatestData();
                            this.loadDevicesData();
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
