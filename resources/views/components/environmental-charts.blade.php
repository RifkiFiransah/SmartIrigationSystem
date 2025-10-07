<section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Light Intensity Chart -->
    <div class="bg-white border-2 border-gray-300 rounded-2xl p-6 shadow-xl">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-gray-900 text-xl font-bold" x-text="t('lightIntensity')">Light Intensity</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700" 
                    x-text="lightIntensityData.labels.length > 0 ? (lightIntensityData.labels.length + ' points') : 'No data'">
                </span>
            </div>
            <div class="flex gap-6 text-sm">
                <div class="chart-legend-item flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-cyan-400"></div>
                    <span class="text-gray-700 font-medium">LI2</span>
                </div>
                <div class="chart-legend-item flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-red-500"></div>
                    <span class="text-gray-700 font-medium">LI1</span>
                </div>
            </div>
        </div>
        <div class="relative bg-white border border-gray-200 rounded-lg p-4" style="height: 320px;">
            <canvas id="lightIntensityChart"></canvas>
        </div>
        <div class="mt-3 text-center">
            <p class="text-xs text-gray-500">Data diperbarui otomatis setiap 60 detik</p>
        </div>
    </div>

    <!-- Water Level Chart -->
    <div class="bg-white border-2 border-gray-300 rounded-2xl p-6 shadow-xl">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-gray-900 text-xl font-bold" x-text="t('waterLevel')">Water Level</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700" 
                    x-text="waterLevelData.labels.length > 0 ? (waterLevelData.labels.length + ' points') : 'No data'">
                </span>
            </div>
            <div class="flex gap-6 text-sm">
                <div class="chart-legend-item flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-lime-500"></div>
                    <span class="text-gray-700 font-medium">WL</span>
                </div>
            </div>
        </div>
        <div class="relative bg-white border border-gray-200 rounded-lg p-4" style="height: 320px;">
            <canvas id="waterLevelChart"></canvas>
        </div>
        <div class="mt-3 text-center">
            <p class="text-xs text-gray-500">Data diperbarui otomatis setiap 60 detik</p>
        </div>
    </div>
</section>

<!-- Additional Environmental Charts -->
<section class="grid grid-cols-1 gap-6">
    <!-- Soil Moisture Chart -->
    <div class="bg-white border-2 border-gray-300 rounded-2xl p-6 shadow-xl">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-gray-900 text-xl font-bold" x-text="t('soilMoisture')">Soil Moisture</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700" 
                    x-text="soilMoistureData.labels.length > 0 ? (soilMoistureData.labels.length + ' points') : 'No data'">
                </span>
            </div>
            <div class="flex flex-wrap gap-3 text-xs">
                <template x-for="(sensor, idx) in soilMoistureSensors" :key="sensor.id">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded" :style="'background-color: ' + sensor.color"></div>
                        <span class="text-gray-700 font-medium" x-text="sensor.label"></span>
                    </div>
                </template>
            </div>
        </div>
        <div class="relative bg-white border border-gray-200 rounded-lg p-4" style="height: 320px;">
            <canvas id="soilMoistureChart"></canvas>
        </div>
        <div class="mt-3 text-center">
            <p class="text-xs text-gray-500">Kelembapan tanah dari berbagai sensor</p>
        </div>
    </div>

    <!-- Temperature and Humidity Charts (Side by Side) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Temperature Chart -->
        <div class="bg-white border-2 border-gray-300 rounded-2xl p-6 shadow-xl">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-gray-900 text-xl font-bold" x-text="t('temperature')">Temperature</h3>
                    <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700" 
                        x-text="temperatureData.labels.length > 0 ? (temperatureData.labels.length + ' points') : 'No data'">
                    </span>
                </div>
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-purple-500"></div>
                        <span class="text-gray-700 font-medium">T1</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-cyan-500"></div>
                        <span class="text-gray-700 font-medium">T2</span>
                    </div>
                </div>
            </div>
            <div class="relative bg-white border border-gray-200 rounded-lg p-4" style="height: 280px;">
                <canvas id="temperatureChart"></canvas>
            </div>
            <div class="mt-3 text-center">
                <p class="text-xs text-gray-500">Suhu dari sensor</p>
            </div>
        </div>

        <!-- Humidity Chart -->
        <div class="bg-white border-2 border-gray-300 rounded-2xl p-6 shadow-xl">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-gray-900 text-xl font-bold" x-text="t('airHumidity')">Humidity</h3>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700" 
                        x-text="humidityData.labels.length > 0 ? (humidityData.labels.length + ' points') : 'No data'">
                    </span>
                </div>
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-blue-500"></div>
                        <span class="text-gray-700 font-medium">H2</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-orange-500"></div>
                        <span class="text-gray-700 font-medium">H1</span>
                    </div>
                </div>
            </div>
            <div class="relative bg-white border border-gray-200 rounded-lg p-4" style="height: 280px;">
                <canvas id="humidityChart"></canvas>
            </div>
            <div class="mt-3 text-center">
                <p class="text-xs text-gray-500">Kelembapan dari sensor</p>
            </div>
        </div>
    </div>
</section>
