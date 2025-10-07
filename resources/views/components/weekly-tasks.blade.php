<div class="mt-6 grid md:grid-cols-2 gap-6" x-show="weekViewDays.length">
    <!-- Current Tasks -->
    <div class="card flex flex-col gap-4">
        {{-- <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-800" x-text="t('activities')">Aktivitas / Peringatan</h3>
            <button class="text-gray-400 hover:text-gray-600 text-xs" @click="refreshTasks()">↻</button>
        </div>
        <template x-if="!currentTasks.length">
            <div class="text-xs text-gray-500" x-text="t('noTasks')">Tidak ada aktivitas.</div>
        </template>
        <div class="space-y-3">
            <template x-for="t in currentTasks" :key="t.id">
                <div class="flex gap-3 items-stretch">
                    <div :class="['w-12 shrink-0 rounded-md flex flex-col items-center justify-center text-white text-[10px] font-semibold', t.color]">
                        <span x-text="t.badgeValue"></span><span x-text="t.badgeLabel"></span>
                    </div>
                    <div class="flex-1 bg-white border border-gray-200 rounded-md px-3 py-2 shadow-sm">
                        <div class="text-xs font-medium" x-text="t.title"></div>
                        <div class="text-[11px] mt-1" x-html="t.desc"></div>
                        <div class="mt-1 text-[10px] font-medium px-2 py-0.5 rounded inline-block"
                            :class="t.tagColor" x-text="t.tag"></div>
                    </div>
                </div>
            </template>
        </div> --}}
        <div class="flex flex-col space-y-4">
            <!-- Header Prakiraan -->
            <div class="flex items-center justify-between">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider" x-text="t('forecast')">Prakiraan</div>
                <div class="flex bg-gray-100 rounded-lg overflow-hidden text-xs">
                    <button type="button" class="px-4 py-2 focus:outline-none transition"
                        :class="forecastView === '24h' ? 'bg-green-600 text-white shadow' : 'text-gray-600 hover:bg-gray-200'"
                        @click="forecastView='24h'" x-text="t('next24h')" disabled>24 Jam</button>
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

        </div>
    </div>
    
    <!-- Upcoming Week -->
    <div class="card flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-800" x-text="t('upcomingWeek')">Minggu Ini</h3>
            <div class="flex gap-1">
                <button class="px-2 py-1 text-[10px] rounded bg-gray-100 hover:bg-gray-200"
                    @click="shiftWeek(-1)" x-text="t('prevWeek')">◀</button>
                <button class="px-2 py-1 text-[10px] rounded bg-gray-100 hover:bg-gray-200"
                    @click="shiftWeek(1)" x-text="t('nextWeek')">▶</button>
            </div>
        </div>
        <div class="flex justify-between text-[11px] font-semibold text-green-800 px-1">
            <template x-for="d in weekViewDays" :key="d.date">
                <div class="flex-1 text-center" x-text="d.weekdayShort"></div>
            </template>
        </div>
        <div class="flex justify-between gap-2">
            <template x-for="d in weekViewDays" :key="d.date">
                <div @click="selectWeekDay(d)"
                    :class="['flex-1 relative rounded-2xl py-3 flex flex-col items-center gap-1 cursor-pointer transition-all duration-200 animate-pop',
                        d.categoryBg, d.active ? 'ring-4 ring-green-500 ring-offset-2 ring-offset-white shadow-lg scale-[1.05]' : 'hover:shadow']">
                    <div class="text-[11px] font-semibold tracking-wide" x-text="d.day"></div>
                    <template x-if="d.icon"><img :src="d.icon" class="h-7 w-7" loading="lazy" /></template>
                    <div class="text-lg font-bold leading-none" x-text="d.temp"></div>
                    <div class="text-[10px] font-medium" x-text="d.label"></div>
                    <div class="absolute inset-0 rounded-2xl pointer-events-none bg-white/5 opacity-0 hover:opacity-40 transition"></div>
                </div>
            </template>
        </div>
        <div class="flex flex-wrap gap-4 mt-2 text-[9px]">
            <template x-for="l in weekLegend" :key="l.key">
                <div class="flex items-center gap-1">
                    <span :class="['inline-block w-3 h-3 rounded-full', l.bg]"></span>
                    <span x-text="l.label"></span>
                </div>
            </template>
        </div>
    </div>
</div>
