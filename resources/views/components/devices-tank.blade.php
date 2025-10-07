{{-- Devices & Water Tank Section --}}
<section class="grid lg:grid-cols-3 gap-6">
    <!-- Devices -->
    <div class="lg:col-span-2 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
            <h2 class="font-semibold text-lg text-gray-900" x-text="t('devices')">Pembacaan Perangkat Terbaru</h2>
            <button @click="loadAll()"
                class="text-xs px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white shadow-md hover:shadow-lg transition-all" x-text="t('refresh')">Refresh</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
            x-show="devices.length">
            <template x-for="d in devices" :key="d.device_id">
                <div class="card relative group cursor-pointer hover:shadow-lg transition"
                    @click="openDeviceModal(d)" title="Klik untuk detail device">
                    <!-- Status badges di kanan atas -->
                    <div class="absolute left-2 top-2 flex flex-col-3 gap-1 items-end z-10 max-w-[120px] mb-20">
                        <!-- Status sensor (kritis/normal) -->
                        <div class="text-[10px] px-2.5 py-1 rounded-full border font-medium shadow-sm whitespace-nowrap backdrop-blur-sm"
                            :class="statusShort(d.status) === 'kritis' ? 'bg-red-100/90 text-red-700 border-red-200' :
                                'bg-green-100/90 text-green-700 border-green-200'">
                            <span x-text="statusShort(d.status)"></span>
                        </div>
                        <!-- Connection state badge -->
                        <div class="text-[9px] px-2 py-0.5 rounded-full border font-medium shadow-sm whitespace-nowrap backdrop-blur-sm"
                            :class="d.connection_state === 'online' ? 'bg-blue-100/90 text-blue-700 border-blue-200' :
                                'bg-gray-100/90 text-gray-600 border-gray-200'">
                            <span x-text="d.connection_state === 'online' ? '● Online' : '○ Offline'"></span>
                        </div>
                        <!-- Valve state badge -->
                        <div class="text-[9px] px-2 py-0.5 rounded-full border font-medium shadow-sm whitespace-nowrap backdrop-blur-sm"
                            :class="d.valve_state === 'open' ? 'bg-green-100/90 text-green-700 border-green-200' :
                                'bg-gray-100/90 text-gray-600 border-gray-200'">
                            <span x-text="d.valve_state === 'open' ? '🟢 Open' : '⚪ Closed'"></span>
                        </div>
                    </div>
                    <div class="mb-5 mt-8 flex items-center justify-between">
                        <h3 class="font-semibold text-sm text-gray-900 truncate" x-text="d.device_name">
                        </h3>
                        <span class="text-[10px] text-gray-400" x-text="timeAgo(d.recorded_at)"></span>
                    </div>
                    <!-- Highlight Soil Moisture -->
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="h-12 w-12 rounded-full flex items-center justify-center bg-gradient-to-br from-green-500 to-green-600 text-white shadow-inner">
                            <!-- leaf / drop icon -->
                            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2C12 2 6 9 6 13a6 6 0 0 0 12 0c0-4-6-11-6-11z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Kelembapan
                                Tanah</div>
                            <div class="text-2xl font-bold leading-none"
                                x-text="fmt(d.soil_moisture_pct,' %')"></div>
                        </div>
                    </div>
                    <!-- Mini metrics row -->
                    <div class="grid grid-cols-3 gap-2 text-[11px] font-medium text-gray-600 mb-2">
                        <div class="flex flex-col items-center bg-gray-50 rounded-md py-1">
                            <span class="text-[10px] font-normal text-gray-500">Suhu</span>
                            <span x-text="fmt(d.temperature_c,'°C')"
                                class="font-semibold text-gray-800"></span>
                        </div>
                        <div class="flex flex-col items-center bg-gray-50 rounded-md py-1">
                            <span class="text-[10px] font-normal text-gray-500">Baterai</span>
                            <span x-text="batteryDisplayShort(d)" class="font-semibold text-gray-800"></span>
                        </div>
                        <div class="flex flex-col items-center bg-gray-50 rounded-md py-1">
                            <span class="text-[10px] font-normal text-gray-500">Air</span>
                            <span x-text="deviceUsageToday(d.device_id)"
                                class="font-semibold text-gray-800"></span>
                        </div>
                    </div>
                    <div class="text-[10px] text-gray-400" x-text="d.location || '-' "></div>
                </div>
            </template>
        </div>
        <div x-show="!devices.length && !loadingDevices" class="text-sm text-gray-600" x-text="t('noDevices')">Tidak ada data
            perangkat.
        </div>
        <div x-show="loadingDevices" class="flex gap-3">
            <template x-for="i in 3" :key="i">
                <div class="card w-full space-y-2">
                    <div class="skeleton h-4 w-24"></div>
                    <div class="skeleton h-3 w-16"></div>
                    <div class="space-y-1 pt-2">
                        <div class="skeleton h-3 w-full"></div>
                        <div class="skeleton h-3 w-5/6"></div>
                        <div class="skeleton h-3 w-3/4"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Tank -->
    <div class="space-y-4">
        <h2 class="font-semibold text-lg text-gray-900" x-text="t('waterTank')">Tangki Air</h2>
        <div class="card" x-show="tank.id">
            <h3 class="font-semibold text-gray-900" x-text="tank.tank_name"></h3>
            <div class="mt-3 flex gap-6 items-stretch">
                <!-- Visual Tangki -->
                <div class="relative" style="width:90px; height:170px;">
                    <div
                        class="absolute inset-0 rounded-xl border-2 border-gray-300 bg-gradient-to-b from-gray-50 to-gray-100 overflow-hidden flex flex-col">
                        <!-- Ticks -->
                        <template x-for="lvl in [100,80,60,40,20]" :key="lvl">
                            <div class="absolute left-0 right-0 flex items-center"
                                :style="`bottom: calc(${lvl}% - 1px);`">
                                <div class="w-full h-px bg-gray-300/60"></div>
                                <div class="text-[8px] -ml-1 -mt-2 bg-white/70 px-0.5 rounded"
                                    x-text="lvl+'%'"></div>
                            </div>
                        </template>
                        <!-- Isi -->
                        <div class="absolute left-0 right-0 bottom-0 transition-all duration-700 ease-out"
                            :style="`height:${tank.percentage || 0}%;`">
                            <div class="absolute inset-0" :style="tankFillStyle()"></div>
                            <!-- Wave overlay sederhana -->
                            <svg class="absolute inset-x-0 -top-4 h-6 w-full" viewBox="0 0 120 20"
                                preserveAspectRatio="none">
                                <path :fill="tankFillColor()" fill-opacity="0.55"
                                    d="M0 10 Q 10 0 20 10 T 40 10 T 60 10 T 80 10 T 100 10 T 120 10 V20 H0 Z">
                                </path>
                            </svg>
                            <!-- Label persentase di atas permukaan -->
                            <div class="absolute top-0 right-1 mt-0.5 text-[11px] font-semibold select-none px-1.5 py-0.5 rounded-md shadow-sm ring-1 ring-black/5"
                                :class="tankLabelClass()"
                                x-text="tank.percentage!=null? tank.percentage.toFixed(0)+'%' : ''"></div>
                        </div>
                    </div>
                </div>
                <!-- Info -->
                <div class="flex-1 flex flex-col text-xs text-gray-700">
                    <div class="flex justify-between mb-1 font-medium">
                        <span>Level</span>
                        <span x-text="tank.percentage!=null? tank.percentage.toFixed(1)+'%' : '-' "></span>
                    </div>
                    <div class="grid grid-cols-2 gap-y-1 gap-x-3 mt-1">
                        <div class="text-gray-500">Kapasitas</div>
                        <div x-text="tank.capacity_liters? tank.capacity_liters.toFixed(0)+' L':'-' "></div>
                        <div class="text-gray-500">Tersisa</div>
                        <div
                            x-text="tank.current_volume_liters? tank.current_volume_liters.toFixed(0)+' L':'-' ">
                        </div>
                        <div class="text-gray-500">Terpakai</div>
                        <div
                            x-text="tank.capacity_liters&&tank.current_volume_liters!=null? (tank.capacity_liters - tank.current_volume_liters).toFixed(0)+' L':'-' ">
                        </div>
                        <div class="text-gray-500">Status</div>
                        <div class="font-medium" :class="tankStatusClass()" x-text="tank.status || '-' ">
                        </div>
                    </div>
                    <div class="mt-auto pt-2 text-[10px] text-gray-400" x-show="tankUpdatedAt">
                        <span x-text="'Update: '+ tankUpdatedAt"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card" x-show="plan.sessions && plan.sessions.length">
            <h3 class="font-semibold mb-2 text-gray-900">Rencana Irigasi (3 Sesi)</h3>
            <ul class="divide-y text-xs">
                <template x-for="s in plan.sessions" :key="s.index">
                    <li class="py-1 flex justify-between items-center">
                        <span>Sesi <span x-text="s.index"></span> (<span x-text="s.time"></span>)</span>
                        <span class="font-medium" :class="sessionColor(s.status)"
                            x-text="s.actual_l ? s.actual_l+'L' : s.adjusted_l+'L'"></span>
                    </li>
                </template>
            </ul>
            <p class="mt-2 text-xs text-gray-600" x-text="plan.status ? 'Status: '+plan.status : ''"></p>
        </div>
    </div>
</section>
