{{-- Water Usage Charts Section --}}
<section class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
        <h2 class="font-semibold text-lg text-gray-900" x-text="t('waterUsageHistory')">Riwayat Penggunaan Air</h2>
        <button @click="loadUsage(); loadUsageDaily()"
            class="text-xs px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white shadow-md hover:shadow-lg transition-all" x-text="t('refresh')">Refresh</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card Kiri: 30 Hari -->
        <div class="card flex flex-col">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-1" x-text="t('last30Days')">Penggunaan 30 Hari Terakhir</h3>
                <p class="text-xs text-gray-600" x-text="t('dailyData30')">Data harian dalam 30 hari terakhir</p>
            </div>
            <div class="flex-1 flex items-center justify-center" style="height: 140px;">
                <canvas id="usageChart30d" width="100%" height="140"></canvas>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <div class="text-xs text-gray-600">
                    <span class="font-semibold text-green-600"
                        x-text="usage.length ? 'Total ' + totalUsage() + ' L' : 'Belum ada data'"></span>
                    <span x-show="usage.length" x-text="' / ' + usage.length + ' hari'"></span>
                </div>
                <div class="text-xs text-gray-600">
                    <span class="font-semibold text-green-600"
                        x-text="'Rata-rata: ' + avgUsage() + ' L'"></span>
                    <span>/hari</span>
                </div>
            </div>
        </div>

        <!-- Card Kanan: 24 Jam -->
        <div class="card flex flex-col">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-1" x-text="t('last24Hours')">Penggunaan 24 Jam Terakhir</h3>
                <p class="text-xs text-gray-600" x-text="t('hourlyData24')">Data per jam dalam 24 jam terakhir</p>
            </div>
            <div class="flex-1 flex items-center justify-center" style="height: 140px;">
                <canvas id="usageChart24h" width="100%" height="140"></canvas>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <div class="text-xs text-gray-600">
                    <span class="font-semibold text-blue-600"
                        x-text="usage24h && usage24h.length ? 'Total ' + totalUsage24h() + ' L' : 'Belum ada data'"></span>
                    <span x-show="usage24h && usage24h.length" x-text="' / 24 jam'"></span>
                </div>
                <div class="text-xs text-gray-600">
                    <span class="font-semibold text-blue-600"
                        x-text="'Rata-rata: ' + avgUsage24h() + ' L'"></span>
                    <span>/jam</span>
                </div>
            </div>
        </div>
    </div>
</section>
