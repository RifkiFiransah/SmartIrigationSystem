<section class="bg-white border border-gray-200 rounded-xl px-6 py-5 shadow-sm" x-show="weatherSummary">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- Kolom 1: Waktu & Tanggal -->
        <div class="flex flex-col space-y-4">
            <!-- Waktu Sekarang -->
            <div class="flex flex-col">
                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold mb-2" x-text="t('currentTime')">Waktu Sekarang</div>
                <div class="flex items-end gap-2">
                    <div class="text-4xl font-bold text-gray-800 tabular-nums" x-text="clock.time"></div>
                    <div class="text-lg text-gray-500 font-medium pb-1" x-text="clock.seconds"></div>
                </div>
            </div>

            <!-- Tanggal Hari Ini -->
            <div class="flex flex-col">
                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold mb-2" x-text="t('currentDate')">Tanggal Hari Ini</div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="px-3 py-3 rounded-lg bg-gray-50 border hover:bg-gray-100 transition">
                        <div class="text-xs font-semibold text-gray-600 mb-1" x-text="t('day')">Tanggal</div>
                        <div class="text-2xl font-bold text-gray-800" x-text="clock.day"></div>
                    </div>
                    <div class="px-3 py-3 rounded-lg bg-gray-50 border hover:bg-gray-100 transition">
                        <div class="text-xs font-semibold text-gray-600 mb-1" x-text="t('month')">Bulan</div>
                        <div class="text-2xl font-bold text-gray-800" x-text="clock.month"></div>
                    </div>
                    <div class="px-3 py-3 rounded-lg bg-gray-50 border hover:bg-gray-100 transition">
                        <div class="text-xs font-semibold text-gray-600 mb-1" x-text="t('year')">Tahun</div>
                        <div class="text-2xl font-bold text-gray-800" x-text="clock.year"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom 2: Cuaca Saat Ini -->
        <div class="flex flex-col items-center text-center space-y-3" aria-live="polite">
            <template x-if="weatherSummary && weatherSummary.icon">
                <img :src="weatherSummary.icon" :alt="weatherSummary.label || 'Ikon Cuaca'" class="h-20 w-20" loading="lazy" />
            </template>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1" x-text="t('currentWeather')">Cuaca Saat Ini</div>
                <div class="text-5xl font-bold leading-none text-gray-800 tabular-nums mb-1"
                    x-text="weatherSummary ? (weatherSummary.temp+'°C') : '-' "></div>
                <div class="text-lg text-gray-600 mb-3" x-text="weatherSummary ? weatherSummary.label : '-' "></div>
            </div>

            <!-- Detail Cuaca -->
            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-gray-600">
                <div class="flex items-center gap-2"><span>💧</span><span
                        x-text="weatherSummary ? (weatherSummary.humidity+'%') : '-' "></span></div>
                <div class="flex items-center gap-2"><span>🌬️</span><span
                        x-text="weatherSummary ? (weatherSummary.wind_speed+' m/s') : '-' "></span></div>
                <div class="flex items-center gap-2" x-show="weatherSummary && weatherSummary.rain!=null">
                    <span>☔</span><span x-text="weatherSummary ? (weatherSummary.rain+' mm') : '-' "></span>
                </div>
                <div class="flex items-center gap-2" x-show="weatherSummary && weatherSummary.light_pct!=null">
                    <span>🔆</span><span x-text="weatherSummary ? (weatherSummary.light_pct+'% cahaya') : '-' "></span>
                </div>
            </div>
            <div class="text-xs text-gray-400" x-text="weatherSummary ? weatherSummary.time : ''"></div>
        </div>

        <!-- Kolom 3: Prakiraan -->
        {{-- <div class="flex flex-col space-y-4">
            <!-- Header Prakiraan -->
            <div class="flex items-center justify-between">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider" x-text="t('forecast')">Prakiraan</div>
                <div class="flex bg-gray-100 rounded-lg overflow-hidden text-xs">
                    <button type="button" class="px-4 py-2 focus:outline-none transition"
                        :class="forecastView === '24h' ? 'bg-green-600 text-white shadow' : 'text-gray-600 hover:bg-gray-200'"
                        @click="forecastView='24h'" x-text="t('next24h')">24 Jam</button>
                    <button type="button" class="px-4 py-2 focus:outline-none transition"
                        :class="forecastView === 'weekly' ? 'bg-green-600 text-white shadow' : 'text-gray-600 hover:bg-gray-200'"
                        @click="forecastView='weekly'" x-text="t('next7d')">Minggu</button>
                </div>
            </div>

            <!-- 24h Forecast -->
            <div x-show="forecastView==='24h'" class="grid grid-cols-2 sm:grid-cols-4 gap-3" x-cloak>
                <template x-for="f in forecast24h" :key="f.local_datetime">
                    <div class="bg-gray-50 border rounded-lg p-3 text-center hover:bg-gray-100 transition">
                        <div class="text-sm font-semibold text-gray-700 mb-1" x-text="f.hour"></div>
                        <template x-if="f.icon">
                            <img :src="f.icon" class="h-8 w-8 mx-auto mb-2" loading="lazy" :alt="f.label" />
                        </template>
                        <div class="text-lg font-bold text-gray-800 tabular-nums" x-text="f.temp+'°C'"></div>
                        <div class="text-xs text-gray-500 truncate" x-text="f.label"></div>
                    </div>
                </template>
            </div>

            <!-- Weekly Forecast -->
            <div x-show="forecastView==='weekly'" class="space-y-2 max-h-64 overflow-y-auto pr-2" x-cloak>
                <template x-for="d in forecastWeekly" :key="d.date">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-12 text-sm font-semibold text-gray-700" x-text="d.day"></div>
                            <template x-if="d.icon">
                                <img :src="d.icon" class="h-6 w-6" loading="lazy" :alt="d.label" />
                            </template>
                            <div class="text-sm text-gray-600" x-text="d.label"></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm font-medium tabular-nums" x-text="d.min+'° / '+d.max+'°'"></div>
                            <div class="flex items-center gap-1 text-xs text-blue-600" x-show="d.rain!=null">
                                <span>☔</span><span x-text="d.rain+'mm'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div> --}}
    </div>
</section>
