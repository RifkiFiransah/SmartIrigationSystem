<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="theme-color" :content="darkMode ? '#0f172a' : '#ffffff'" />
    <meta http-equiv="Permissions-Policy"
        content="accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()" />
    <title>Irigasi Pintar</title>
    <link rel="icon" type="image/png" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('AgrinexLogo.jpg') }}" />
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    @if (app()->environment('production'))
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}"></script>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Leaflet for interactive map (no API key needed) -->
    <!-- Correct Leaflet SRI hashes (official) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        :root {
            color-scheme: light dark;
        }

        .card {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.25rem;
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .stat-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #6b7280;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 1.5rem;
            line-height: 2rem;
            font-weight: bold;
            color: #1f2937;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
        }

        .btn-ghost {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-ghost:hover {
            background-color: #e5e7eb;
            border-color: #9ca3af;
        }

        .skeleton {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        @keyframes popIn {
            0% {
                transform: scale(.8);
                opacity: 0
            }

            100% {
                transform: scale(1);
                opacity: 1
            }
        }

        .animate-pop {
            animation: popIn .35s cubic-bezier(.4, 1.4, .4, 1) both
        }

        /* Metric icon base */
        .metric-icon svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .metric-icon--small svg {
            width: 16px;
            height: 16px;
            stroke-width: 2;
        }

        /* Enhanced card animations */
        .metric-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .metric-card:hover {
            transform: translateY(-2px);
        }

        /* Gauge animations */
        .gauge-progress {
            transition: stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom scrollbar for small screens */
        @media (max-width: 768px) {
            .metrics-container {
                overflow-x: auto;
                scrollbar-width: thin;
                scrollbar-color: #d1d5db transparent;
            }

            .metrics-container::-webkit-scrollbar {
                height: 4px;
            }

            .metrics-container::-webkit-scrollbar-track {
                background: transparent;
            }

            .metrics-container::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 2px;
            }
        }

        /* Rain animation keyframes */
        @keyframes rainDrop {
            0% {
                transform: translateY(-100%);
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                transform: translateY(100%);
                opacity: 0;
            }
        }

        .rain-drop {
            animation: rainDrop 2s infinite linear;
        }

        /* Battery charging animation */
        @keyframes batteryPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .battery-full {
            animation: batteryPulse 1.5s infinite ease-in-out;
        }

        /* Fix Leaflet z-index issues with modals */
        .leaflet-container {
            z-index: 1 !important;
        }

        .leaflet-control-container {
            z-index: 2 !important;
        }

        .leaflet-popup {
            z-index: 3 !important;
        }

        /* Ensure modal has higher z-index than leaflet */
        .modal-overlay {
            z-index: 9999 !important;
        }

        /* Leaflet tile layer should stay below everything */
        .leaflet-tile-pane {
            z-index: 1 !important;
        }

        .leaflet-overlay-pane {
            z-index: 2 !important;
        }

        .leaflet-marker-pane {
            z-index: 3 !important;
        }

        .leaflet-tooltip-pane {
            z-index: 4 !important;
        }

        .leaflet-shadow-pane {
            z-index: 1 !important;
        }

        /* Force all leaflet related elements to stay below modals */
        #leafletMap .leaflet-container,
        #leafletMapFull .leaflet-container {
            z-index: 1 !important;
            position: relative !important;
        }

        .gauge {
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, .12));
        }

        .gauge-inner {
            font-variant-numeric: tabular-nums;
        }

        .card-gradient-mask {
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .4), transparent 70%);
        }
    </style>
</head>

<body x-data="dashboard()" x-init="applyPersistedTheme();
init();" class="h-full bg-gray-50 text-gray-800 min-h-full">
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('AgrinexLogo.jpg') }}" alt="Logo"
                    class="h-9 w-9 rounded-md object-cover border border-green-200 shadow-sm" loading="lazy">
                <div>
                    <h1 class="text-lg font-semibold text-green-700 leading-tight">Irigasi Pintar</h1>
                    <p class="text-xs text-gray-600 -mt-0.5">Monitoring & otomasi penyiraman</p>
                </div>
                <template x-if="fetchError">
                    <span
                        class="ml-2 text-[10px] px-2 py-0.5 rounded bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-300"
                        x-text="'OFFLINE'" title="Gagal mengambil data terakhir"></span>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <button @click="loadAll(true)" class="btn btn-ghost"
                    :class="loadingAll ? 'opacity-60 pointer-events-none' : ''">
                    <span x-show="!loadingAll">🔄</span>
                    <span x-show="loadingAll" class="animate-spin">⏳</span>
                    <span class="hidden sm:inline" x-text="loadingAll ? 'Memuat' : 'Refresh'"></span>
                </button>
                @auth
                    <a href="/admin" class="btn bg-green-600 hover:bg-green-700 text-white">Admin</a>
                @else
                    <a href="/admin/login" class="btn btn-ghost">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6 space-y-6 max-w-7xl">
        <!-- Waktu, Tanggal & Cuaca Terpadu -->
        <section class="bg-white border border-gray-200 rounded-xl px-6 py-5 shadow-sm" x-show="weatherSummary">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Kolom 1: Waktu & Tanggal -->
                <div class="flex flex-col space-y-4">
                    <!-- Waktu Sekarang -->
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold mb-2">Waktu Sekarang
                        </div>
                        <div class="flex items-end gap-2">
                            <div class="text-4xl font-bold text-gray-800 tabular-nums" x-text="clock.time"></div>
                            <div class="text-lg text-gray-500 font-medium pb-1" x-text="clock.seconds"></div>
                        </div>
                    </div>

                    <!-- Tanggal Hari Ini -->
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold mb-2">Tanggal Hari Ini
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="px-3 py-3 rounded-lg bg-gray-50 border hover:bg-gray-100 transition">
                                <div class="text-xs font-semibold text-gray-600 mb-1">Tanggal</div>
                                <div class="text-2xl font-bold text-gray-800" x-text="clock.day"></div>
                            </div>
                            <div class="px-3 py-3 rounded-lg bg-gray-50 border hover:bg-gray-100 transition">
                                <div class="text-xs font-semibold text-gray-600 mb-1">Bulan</div>
                                <div class="text-2xl font-bold text-gray-800" x-text="clock.month"></div>
                            </div>
                            <div class="px-3 py-3 rounded-lg bg-gray-50 border hover:bg-gray-100 transition">
                                <div class="text-xs font-semibold text-gray-600 mb-1">Tahun</div>
                                <div class="text-2xl font-bold text-gray-800" x-text="clock.year"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Cuaca Saat Ini -->
                <div class="flex flex-col items-center text-center space-y-3" aria-live="polite">
                    <template x-if="weatherSummary && weatherSummary.icon">
                        <img :src="weatherSummary.icon" :alt="weatherSummary.label || 'Ikon Cuaca'" class="h-20 w-20"
                            loading="lazy" />
                    </template>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Cuaca Saat Ini
                        </div>
                        <div class="text-5xl font-bold leading-none text-gray-800 tabular-nums mb-1"
                            x-text="weatherSummary ? (weatherSummary.temp+'°C') : '-' "></div>
                        <div class="text-lg text-gray-600 mb-3" x-text="weatherSummary ? weatherSummary.label : '-' ">
                        </div>
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
                            <span>🔆</span><span
                                x-text="weatherSummary ? (weatherSummary.light_pct+'% cahaya') : '-' "></span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400" x-text="weatherSummary ? weatherSummary.time : ''"></div>
                </div>

                <!-- Kolom 3: Prakiraan -->
                <div class="flex flex-col space-y-4">
                    <!-- Header Prakiraan -->
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Prakiraan</div>
                        <div class="flex bg-gray-100 rounded-lg overflow-hidden text-xs">
                            <button type="button" class="px-4 py-2 focus:outline-none transition"
                                :class="forecastView === '24h' ? 'bg-green-600 text-white shadow' :
                                    'text-gray-600 hover:bg-gray-200'"
                                @click="forecastView='24h'">24 Jam</button>
                            <button type="button" class="px-4 py-2 focus:outline-none transition"
                                :class="forecastView === 'weekly' ? 'bg-green-600 text-white shadow' :
                                    'text-gray-600 hover:bg-gray-200'"
                                @click="forecastView='weekly'">Minggu</button>
                        </div>
                    </div>

                    <!-- 24h Forecast -->
                    <div x-show="forecastView==='24h'" class="grid grid-cols-2 sm:grid-cols-4 gap-3" x-cloak>
                        <template x-for="f in forecast24h" :key="f.local_datetime">
                            <div class="bg-gray-50 border rounded-lg p-3 text-center hover:bg-gray-100 transition">
                                <div class="text-sm font-semibold text-gray-700 mb-1" x-text="f.hour"></div>
                                <template x-if="f.icon">
                                    <img :src="f.icon" class="h-8 w-8 mx-auto mb-2" loading="lazy"
                                        :alt="f.label" />
                                </template>
                                <div class="text-lg font-bold text-gray-800 tabular-nums" x-text="f.temp+'°C'"></div>
                                <div class="text-xs text-gray-500 truncate" x-text="f.label"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Weekly Forecast -->
                    <div x-show="forecastView==='weekly'" class="space-y-2 max-h-64 overflow-y-auto pr-2" x-cloak>
                        <template x-for="d in forecastWeekly" :key="d.date">
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 text-sm font-semibold text-gray-700" x-text="d.day"></div>
                                    <template x-if="d.icon">
                                        <img :src="d.icon" class="h-6 w-6" loading="lazy"
                                            :alt="d.label" />
                                    </template>
                                    <div class="text-sm text-gray-600" x-text="d.label"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-medium tabular-nums" x-text="d.min+'° / '+d.max+'°'">
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-blue-600" x-show="d.rain!=null">
                                        <span>☔</span><span x-text="d.rain+'mm'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- Weekly Tasks & Upcoming Week (Styled) -->
        <div class="mt-6 grid md:grid-cols-2 gap-6" x-show="weekViewDays.length">
            <!-- Current Tasks -->
            <div class="card flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Aktivitas / Peringatan</h3>
                    <button class="text-gray-400 hover:text-gray-600 text-xs" @click="refreshTasks()">↻</button>
                </div>
                <template x-if="!currentTasks.length">
                    <div class="text-xs text-gray-500">Tidak ada aktivitas.</div>
                </template>
                <div class="space-y-3">
                    <template x-for="t in currentTasks" :key="t.id">
                        <div class="flex gap-3 items-stretch">
                            <div
                                :class="['w-12 shrink-0 rounded-md flex flex-col items-center justify-center text-white text-[10px] font-semibold',
                                    t.color
                                ]">
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
                </div>
            </div>
            <!-- Upcoming Week -->
            <div class="card flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Minggu Ini</h3>
                    <div class="flex gap-1">
                        <button class="px-2 py-1 text-[10px] rounded bg-gray-100 hover:bg-gray-200"
                            @click="shiftWeek(-1)">◀</button>
                        <button class="px-2 py-1 text-[10px] rounded bg-gray-100 hover:bg-gray-200"
                            @click="shiftWeek(1)">▶</button>
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
                                d.categoryBg, d.active ?
                                'ring-4 ring-green-500 ring-offset-2 ring-offset-white shadow-lg scale-[1.05]' :
                                'hover:shadow'
                            ]">
                            <div class="text-[11px] font-semibold tracking-wide" x-text="d.day"></div>
                            <template x-if="d.icon"><img :src="d.icon" class="h-7 w-7"
                                    loading="lazy" /></template>
                            <div class="text-lg font-bold leading-none" x-text="d.temp"></div>
                            <div class="text-[10px] font-medium" x-text="d.label"></div>
                            <div
                                class="absolute inset-0 rounded-2xl pointer-events-none bg-white/5 opacity-0 hover:opacity-40 transition">
                            </div>
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
        </section>
        <!-- Top Metrics (Gauge / Linear Cards) -->
        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold tracking-wide text-gray-600 uppercase">Ringkasan Lingkungan</h2>
                <div class="text-[10px] text-gray-500"
                    x-text="lastUpdated ? ('Update: '+ lastUpdated.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})) : ''">
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <template x-for="m in topMetricCards" :key="m.key">
                    <div class="relative bg-white border border-gray-200 rounded-2xl p-4 flex flex-col overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group"
                        :class="getCardTheme(m.key)">
                        <!-- Background gradient overlay -->
                        <div class="absolute inset-0 opacity-5 group-hover:opacity-10 transition-opacity duration-300"
                            :style="getCardGradient(m.key)"></div>

                        <!-- Header with icon and title -->
                        <div class="relative z-10 flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="p-2 rounded-xl" :style="getIconBackground(m.key)">
                                    <div class="metric-icon text-white" x-html="metricIcon(m.key)"></div>
                                </div>
                                <div class="text-xs font-semibold text-gray-700" x-text="m.label"></div>
                            </div>
                            <div class="text-[9px] text-gray-400" x-text="m.desc"></div>
                        </div>

                        <!-- Gauge Type - Circular Design -->
                        <template x-if="m.type==='gauge'">
                            <div class="relative z-10 flex flex-col items-center">
                                <!-- Large circular gauge -->
                                <div class="relative w-20 h-20 mb-2">
                                    <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 80 80">
                                        <!-- Background circle -->
                                        <circle cx="40" cy="40" r="32" stroke="#e5e7eb"
                                            stroke-width="6" fill="none" />
                                        <!-- Progress circle -->
                                        <circle cx="40" cy="40" r="32" :stroke="getGaugeColor(m.key)"
                                            stroke-width="6" fill="none" stroke-linecap="round"
                                            :stroke-dasharray="`${2 * Math.PI * 32}`"
                                            :stroke-dashoffset="`${2 * Math.PI * 32 * (1 - m.pct / 100)}`"
                                            class="transition-all duration-500" />
                                    </svg>
                                    <!-- Center value -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-lg font-bold" x-text="m.display"
                                            :style="`color: ${getGaugeColor(m.key)}`"></span>
                                        <span class="text-[9px] text-gray-500" x-text="m.unit"></span>
                                    </div>
                                </div>
                                <!-- Range indicators -->
                                <div class="flex items-center justify-between w-full text-[9px] text-gray-500">
                                    <span x-text="m.min + m.unit"></span>
                                    <span class="font-semibold" x-text="Math.round(m.pct) + '%'"></span>
                                    <span x-text="m.max + m.unit"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Linear Type - Horizontal Bar Design -->
                        <template x-if="m.type==='linear'">
                            <div class="relative z-10 flex flex-col">
                                <!-- Value and unit -->
                                <div class="flex items-end justify-between mb-2">
                                    <div class="text-2xl font-bold" x-text="m.display"
                                        :style="`color: ${getGaugeColor(m.key)}`"></div>
                                    <div class="text-xs text-gray-500" x-text="m.unit"></div>
                                </div>

                                <!-- Horizontal progress bar -->
                                <div class="w-full h-6 bg-gray-100 rounded-full relative overflow-hidden mb-2">
                                    <!-- Background gradient -->
                                    <div class="absolute inset-0 opacity-20" :style="getLinearGradient(m.key)"></div>

                                    <!-- Progress fill with rounded shape -->
                                    <div class="absolute left-0 top-0 bottom-0 rounded-full transition-all duration-1000 flex items-center justify-end pr-2"
                                        :style="`width: ${Math.max(20, m.pct)}%; background: ${getGaugeColor(m.key)}`">
                                        <span class="text-white text-[10px] font-bold"
                                            x-text="Math.round(m.pct) + '%'"></span>
                                    </div>
                                </div>

                                <!-- Range indicators -->
                                <div class="flex items-center justify-between text-[9px] text-gray-400">
                                    <span x-text="m.min + m.unit"></span>
                                    <span x-text="m.desc"></span>
                                    <span x-text="m.max + m.unit"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Plain Type - Clean Design -->
                        <template x-if="m.type==='plain'">
                            <div class="relative z-10 flex flex-col items-center justify-center h-full">
                                <!-- Large value display -->
                                <div class="text-2xl font-bold mb-1 text-center" x-text="m.display"
                                    :style="`color: ${getGaugeColor(m.key)}`"></div>
                                <!-- Unit -->
                                <div class="text-xs text-gray-500 mb-2" x-text="m.unit"></div>
                                <!-- Status indicator -->
                                <div class="text-[10px] text-gray-400 px-2 py-1 rounded-full bg-gray-100"
                                    x-text="m.desc"></div>
                                <!-- Decorative animated drops for rain -->
                                <template x-if="m.key === 'rain'">
                                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                                        <div class="absolute top-2 left-3 w-1 h-1 bg-blue-300 rounded-full opacity-60 animate-bounce"
                                            style="animation-delay: 0s;"></div>
                                        <div class="absolute top-4 right-4 w-1 h-1 bg-blue-400 rounded-full opacity-40 animate-bounce"
                                            style="animation-delay: 0.5s;"></div>
                                        <div class="absolute bottom-6 left-1/2 w-1 h-1 bg-blue-300 rounded-full opacity-50 animate-bounce"
                                            style="animation-delay: 1s;"></div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Subtle background icon -->
                        <div class="absolute right-2 bottom-2 opacity-5 metric-icon" x-html="metricIcon(m.key)"
                            style="transform: scale(1.5);"></div>
                    </div>
                </template>
            </div>
        </section>

        <!-- Latest Devices & Tank -->
        <section class="grid lg:grid-cols-3 gap-6">
            <!-- Devices -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                    <h2 class="font-semibold text-lg text-gray-900">Pembacaan Perangkat Terbaru</h2>
                    <button @click="loadAll()"
                        class="text-xs px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white shadow-md hover:shadow-lg transition-all">Refresh</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                    x-show="devices.length">
                    <template x-for="d in devices" :key="d.device_id">
                        <div class="card relative group cursor-pointer hover:shadow-lg transition"
                            @click="openDeviceModal(d)" title="Klik untuk detail device">
                            <div class="absolute right-2 top-2 text-[10px] px-2 py-0.5 rounded-full border font-medium mb-5"
                                :class="statusShort(d.status) === 'kritis' ? 'bg-red-100 text-red-700 border-red-200' :
                                    'bg-green-100 text-green-700 border-green-200'">
                                <span x-text="statusShort(d.status)"></span>
                            </div>
                            <div class="mb-3 mt-5 flex items-center justify-between">
                                <h3 class="font-semibold text-sm text-gray-900 truncate pr-6" x-text="d.device_name">
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
                <div x-show="!devices.length && !loadingDevices" class="text-sm text-gray-600">Tidak ada data
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
                <h2 class="font-semibold text-lg text-gray-900">Tangki Air</h2>
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

        <!-- Usage Chart - 2 Cards Side by Side -->
        <section class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                <h2 class="font-semibold text-lg text-gray-900">Riwayat Penggunaan Air</h2>
                <button @click="loadUsage(); loadUsageDaily()"
                    class="text-xs px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white shadow-md hover:shadow-lg transition-all">Refresh</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Card Kiri: 30 Hari -->
                <div class="card flex flex-col">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-800 mb-1">Penggunaan 30 Hari Terakhir</h3>
                        <p class="text-xs text-gray-600">Data harian dalam 30 hari terakhir</p>
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
                        <h3 class="text-base font-semibold text-gray-800 mb-1">Penggunaan 24 Jam Terakhir</h3>
                        <p class="text-xs text-gray-600">Data per jam dalam 24 jam terakhir</p>
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

        <!-- Lokasi (2 Kolom: Street View & Denah) -->
        <section class="grid lg:grid-cols-2 gap-6">
            <!-- Street View Kiri -->
            <div class="card relative overflow-hidden flex flex-col">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-800">Street View Lahan</h2>
                    <span
                        class="text-[11px] px-2 py-0.5 rounded bg-green-100 text-green-700 border border-green-200">Live</span>
                </div>
                <div class="relative aspect-video w-full rounded-lg overflow-hidden border bg-gray-100">
                    <!-- Adjusted Street View (heading ~110°, pitch lowered to show ground) -->
                    <iframe class="w-full h-full" allowfullscreen loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps/embed?pb=!4v1726850000!6m8!1m7!1sqN2B4gU9-KNJvTDT55KJcA!2m2!1d-6.9863524!2d108.6008761!3f108.38!4f10!5f0.7820865974627469"></iframe>
                    <div class="absolute bottom-2 left-2 flex flex-wrap gap-2">
                        <template
                            x-for="m in topMetricCards.filter(x=>['temp','humidity','light','wind'].includes(x.key))"
                            :key="m.key">
                            <div class="backdrop-blur bg-white/55 border border-white/40 text-[10px] px-2 py-1 rounded flex items-center gap-1 shadow-sm cursor-help"
                                :data-metric-chip="m.key">
                                <span class="metric-icon metric-icon--small text-gray-600"
                                    x-html="metricIcon(m.key)"></span>
                                <span x-text="m.display"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-600 leading-relaxed">Tampilan Street View area lahan di desa Geresik
                    sebagai konteks lingkungan penempatan sensor. Arahkan kursor ke chip metric untuk melihat snapshot
                    waktu.</p>
            </div>
            <!-- Denah Desa Kanan -->
            <div class="card flex flex-col relative">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-800">Denah Desa (Interaktif)</h2>
                    <div class="flex gap-2">
                        <a :href="googleMapsLink" target="_blank" rel="noopener"
                            class="text-xs px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white border border-green-600">Buka
                            di Google Maps</a>
                    </div>
                </div>
                <div class="relative">
                    <div id="leafletMap" class="w-full rounded-lg overflow-hidden border bg-gray-100"
                        style="height:340px; min-height:300px; z-index: 1; position: relative;"></div>
                    <button @click="initLeaflet()"
                        class="absolute top-2 right-2 text-[10px] px-2 py-1 rounded bg-white/80 hover:bg-white shadow border"
                        x-show="!leafletInited">Muat Ulang</button>
                </div>
                <p class="mt-3 text-xs text-gray-600">Batas poligon desa Geresik dan marker lokasi pusat (estimasi).
                    Interaktif tanpa API key.</p>
                <p class="mt-1 text-[10px] text-gray-400">Sumber data: OpenStreetMap & inisialisasi manual.</p>
            </div>
        </section>

    </main>

    <footer class="text-center py-6 text-xs text-gray-500">&copy; {{ date('Y') }} Smart Irrigation</footer>

    <!-- Device Detail Modal -->
    <div x-cloak x-show="showDeviceModal"
        class="fixed inset-0 z-50 modal-overlay flex items-start md:items-center justify-center p-4 md:p-8 bg-black/40 backdrop-blur-sm"
        @keydown.escape.window="closeDeviceModal()" style="z-index: 9999 !important;">
        <div x-show="showDeviceModal" x-transition.opacity x-transition.scale.origin.top
            class="bg-white w-full max-w-3xl rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col max-h-[92vh] relative"
            style="z-index: 10000 !important;">
            <div class="flex items-start justify-between px-5 py-4 border-b bg-gray-50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800" x-text="selectedDevice?.device_name || 'Device'">
                    </h3>
                    <p class="text-xs text-gray-500" x-text="selectedDevice ? ('ID: '+selectedDevice.device_id) : ''">
                    </p>
                </div>
                <button class="text-gray-500 hover:text-gray-700" @click="closeDeviceModal()">✕</button>
            </div>
            <div class="px-5 pt-5 pb-6 overflow-y-auto space-y-8">
                <!-- Quick stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-[10px] font-semibold text-gray-500">Suhu</div>
                        <div class="font-medium" x-text="fmt(selectedDevice?.temperature_c,'°C')"></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold text-gray-500">Tanah</div>
                        <div class="font-medium" x-text="fmt(selectedDevice?.soil_moisture_pct,'%')"></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold text-gray-500">Baterai</div>
                        <div class="font-medium" x-text="batteryDisplay(selectedDevice)"></div>
                    </div>
                    {{-- <div>
                        <div class="text-[10px] font-semibold text-gray-500">Cahaya</div>
                        <div class="font-medium" x-text="fmt(selectedDevice?.light_lux,' lx')"></div>
                    </div> --}}
                </div>

                <!-- Sessions table -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-2">🚿 Penggunaan Air per
                        Sesi
                        <template x-if="loadingDeviceDetail"><span
                                class="text-xs text-gray-500">(memuat...)</span></template>
                    </h4>
                    <template x-if="deviceSessionsSummary">
                        <div class="text-[11px] text-gray-600 mb-2">
                            <span x-text="'Total Rencana: ' + fmt(deviceSessionsSummary.total_planned_l,' L')"></span>
                            <span class="mx-2">|</span>
                            <span x-text="'Total Aktual: ' + fmt(deviceSessionsSummary.total_actual_l,' L')"></span>
                            <span class="mx-2">|</span>
                            <span
                                x-text="'Efisiensi: ' + (deviceSessionsSummary.efficiency_pct!=null? deviceSessionsSummary.efficiency_pct+'%':'-')"></span>
                        </div>
                    </template>
                    <template x-if="!loadingDeviceDetail && !deviceSessions.length">
                        <div class="text-xs text-gray-500">Belum ada data sesi untuk device ini.</div>
                    </template>
                    <template x-if="deviceSessions.length">
                        <div class="overflow-x-auto border rounded-md">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-100 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium">Sesi</th>
                                        <th class="px-3 py-2 text-left font-medium">Waktu</th>
                                        <th class="px-3 py-2 text-right font-medium">Rencana (L)</th>
                                        <th class="px-3 py-2 text-right font-medium">Aktual (L)</th>
                                        <th class="px-3 py-2 text-right font-medium">Efisiensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="s in deviceSessions" :key="s.id || s.index">
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-3 py-1" x-text="s.index || s.session || '-' "></td>
                                            <td class="px-3 py-1" x-text="s.time || s.start_time || '-' "></td>
                                            <td class="px-3 py-1 text-right"
                                                x-text="s.planned_l ? s.planned_l.toFixed(1) : (s.planned_volume_l?.toFixed(1) || '-')">
                                            </td>
                                            <td class="px-3 py-1 text-right"
                                                x-text="s.actual_l ? s.actual_l.toFixed(1) : (s.actual_volume_l?.toFixed(1) || '-')">
                                            </td>
                                            <td class="px-3 py-1 text-right"
                                                x-text="(s.actual_l && s.planned_l) ? ((s.actual_l / (s.planned_l||1))*100).toFixed(0)+'%' : (s.efficiency_pct ? s.efficiency_pct+'%' : '-')">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>

                <!-- Usage history table -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-2">📜 Riwayat Penggunaan Air
                        <template x-if="loadingDeviceDetail"><span
                                class="text-xs text-gray-500">(memuat...)</span></template>
                    </h4>
                    <template x-if="!loadingDeviceDetail && !deviceUsageHistory.length">
                        <div class="text-xs text-gray-500">Belum ada data penggunaan sebelumnya.</div>
                    </template>
                    <template x-if="deviceUsageHistory.length">
                        <div class="overflow-x-auto border rounded-md">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-100 text-gray-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium">Tanggal</th>
                                        <th class="px-3 py-2 text-right font-medium">Total (L)</th>
                                        <th class="px-3 py-2 text-right font-medium">Sesi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="h in deviceUsageHistory" :key="h.date || h.id">
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-3 py-1" x-text="h.date || h.day || '-' "></td>
                                            <td class="px-3 py-1 text-right"
                                                x-text="h.total_l ? h.total_l.toFixed(1) : (h.volume_l?.toFixed(1) || '-')">
                                            </td>
                                            <td class="px-3 py-1 text-right"
                                                x-text="h.sessions || h.session_count || '-' "></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t flex justify-end gap-2">
                <button @click="closeDeviceModal()" class="btn btn-ghost text-xs">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function dashboard() {
            return {
                darkMode: localStorage.getItem('sis_dark') === '1',
                loadingAll: false,
                loadingDevices: false,
                fetchError: false,
                lastUpdated: null,
                devices: [],
                weatherSummary: {},
                forecastEntries: [],
                forecast24h: [],
                forecastWeekly: [],
                forecastView: '24h',
                calendarBase: new Date(),
                calendarDays: [],
                calendarMonthLabel: '',
                selectedDate: null,
                calendarDetails: null,
                clock: {
                    time: '--:--',
                    seconds: '',
                    dateLong: '',
                    dateShort: '',
                    day: '',
                    month: '',
                    year: ''
                },
                // Weekly + tasks view
                weekOffset: 0,
                weekViewDays: [],
                currentTasks: [],
                weekLegend: [{
                        key: 'plowing',
                        label: 'Olah Lahan',
                        bg: 'bg-amber-600'
                    },
                    {
                        key: 'fert',
                        label: 'Pemupukan',
                        bg: 'bg-green-600'
                    },
                    {
                        key: 'ship',
                        label: 'Pengiriman',
                        bg: 'bg-yellow-400'
                    },
                    {
                        key: 'idle',
                        label: 'Tidak ada',
                        bg: 'bg-gray-200'
                    }
                ],
                categoryConfig: {
                    plowing: {
                        maxRain: 2,
                        maxTemp: 30
                    },
                    fertilization: {
                        maxRain: 2,
                        minTemp: 30
                    },
                    shipment: {
                        minRain: 5
                    },
                },
                categoryStyles: {
                    plowing: {
                        bg: 'bg-gradient-to-b from-amber-500 to-amber-600 text-white',
                        icon: '🚜'
                    },
                    fert: {
                        bg: 'bg-gradient-to-b from-green-500 to-green-700 text-white',
                        icon: '🧪'
                    },
                    ship: {
                        bg: 'bg-gradient-to-b from-yellow-300 to-yellow-500 text-gray-800',
                        icon: '🚚'
                    },
                    idle: {
                        bg: 'bg-gradient-to-b from-gray-200 to-gray-300 text-gray-700',
                        icon: '➖'
                    },
                },
                showDeviceModal: false,
                selectedDevice: null,
                deviceSessions: [],
                deviceSessionsSummary: null,
                deviceUsageHistory: [],
                loadingDeviceDetail: false,
                tank: {},
                tankUpdatedAt: null,
                plan: {},
                usage: [],
                usage24h: [],
                usageChart: null,
                usageChart24h: null,
                // Legacy topStats removed in favor of topMetricCards
                topMetricCards: [{
                        key: 'temp',
                        label: 'SUHU',
                        type: 'gauge',
                        min: 10,
                        max: 45,
                        unit: '°C',
                        value: null,
                        display: '-',
                        pct: 0,
                        icon: '🌡️',
                        desc: '',
                        color: '#16a34a'
                    },
                    {
                        key: 'humidity',
                        label: 'KELEMBAPAN',
                        type: 'gauge',
                        min: 0,
                        max: 100,
                        unit: '%',
                        value: null,
                        display: '-',
                        pct: 0,
                        icon: '💧',
                        desc: '',
                        color: '#16a34a'
                    },
                    {
                        key: 'light',
                        label: 'CAHAYA',
                        type: 'gauge',
                        min: 0,
                        max: 100,
                        unit: '%',
                        value: null,
                        display: '-',
                        pct: 0,
                        icon: '🔆',
                        desc: '',
                        color: '#16a34a'
                    },
                    {
                        key: 'wind',
                        label: 'ANGIN',
                        type: 'gauge',
                        min: 0,
                        max: 15,
                        unit: 'm/s',
                        value: null,
                        display: '-',
                        pct: 0,
                        icon: '🌬️',
                        desc: '',
                        color: '#16a34a'
                    },
                    {
                        key: 'rain',
                        label: 'HUJAN',
                        type: 'plain',
                        min: 0,
                        max: 50,
                        unit: 'mm',
                        value: null,
                        display: '0.0mm',
                        pct: 0,
                        icon: '☔',
                        desc: 'current',
                        color: '#6366f1'
                    },
                    // {
                    //     key: 'tank',
                    //     label: 'TANGKI',
                    //     type: 'linear',
                    //     min: 0,
                    //     max: 100,
                    //     unit: '%',
                    //     value: null,
                    //     display: '-',
                    //     pct: 0,
                    //     icon: '🛢️',
                    //     desc: '',
                    //     color: '#16a34a'
                    // },
                    {
                        key: 'battery',
                        label: 'BATERAI',
                        type: 'linear',
                        min: 0,
                        max: 100,
                        unit: '%',
                        value: null,
                        display: '-',
                        pct: 0,
                        icon: '🔋',
                        desc: '',
                        color: '#16a34a'
                    },
                    // {
                    //     key: 'devices',
                    //     label: 'DEVICE',
                    //     type: 'plain',
                    //     min: 0,
                    //     max: 50,
                    //     unit: '',
                    //     value: null,
                    //     display: '-',
                    //     pct: 0,
                    //     icon: '📡',
                    //     desc: 'online',
                    //     color: '#16a34a'
                    // },
                ],
                applyPersistedTheme() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },
                // Location section (no dynamic state needed after refactor)
                showFullMap: false,
                leafletInited: false,
                leafletFullInited: false,
                googleMapsLink: 'https://maps.google.com/?q=-6.9891469,108.6086561',
                villageCenter: {
                    lat: -6.9891469,
                    lng: 108.6086561
                },
                villagePolygon: [
                    [-6.9869, 108.6029],
                    [-6.9878, 108.6065],
                    [-6.9889, 108.6094],
                    [-6.9903, 108.6110],
                    [-6.9920, 108.6100],
                    [-6.9910, 108.6068],
                    [-6.9898, 108.6035]
                ],
                metricSnapshots: {},
                persistDark() {
                    localStorage.setItem('sis_dark', this.darkMode ? '1' : '0');
                    this.applyPersistedTheme();
                },
                toggleDark() {
                    this.darkMode = !this.darkMode;
                    this.persistDark();
                },
                metricBy(k) {
                    return this.topMetricCards.find(m => m.key === k);
                },
                updateMetric(key, val, desc = '') {
                    const m = this.metricBy(key);
                    if (!m) return;
                    if (val == null || isNaN(parseFloat(val))) return; // ignore invalid
                    m.value = parseFloat(val);
                    if (m.type === 'plain') {
                        m.display = m.value.toFixed(0); // just integer count
                    } else {
                        m.display = (m.type === 'gauge' && m.unit === '%') ? Math.round(m.value) + m.unit : (m.value
                            .toFixed ? m.value.toFixed((m.unit === '%' || m.max <= 20) ? 0 : 1) : m.value) + m.unit;
                    }
                    m.desc = desc;
                    m.pct = this.normalizePct(m.value, m.min, m.max);
                    m.color = this.colorFor(m.pct);
                    // snapshot for tooltip (store first capture per minute)
                    this.metricSnapshots[m.key] = {
                        value: m.display,
                        ts: new Date()
                    };
                },
                normalizePct(v, min, max) {
                    if (v == null) return 0;
                    const clamped = Math.max(min, Math.min(max, v));
                    return ((clamped - min) / (max - min)) * 100;
                },
                colorFor(pct) {
                    // 0 red -> 50 orange -> 100 green
                    const h = (pct * 120) / 100; // 0=red 120=green
                    return `hsl(${h}, 70%, 45%)`;
                },
                gaugeStyle(m) {
                    return `background: conic-gradient(${m.color} 0% ${m.pct}%, #e5e7eb ${m.pct}% 100%);`;
                },
                metricIcon(key) {
                    const base = 'stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"';
                    const icons = {
                        temp: `<svg viewBox='0 0 24 24'><path ${base} d='M10 13.5V5a2 2 0 1 1 4 0v8.5a4 4 0 1 1-4 0Z'/><path ${base} d='M10 10h4'/></svg>`,
                        humidity: `<svg viewBox='0 0 24 24'><path ${base} d='M12 3.5c0 .5-5 6-5 9.5a5 5 0 0 0 10 0c0-3.5-5-9-5-9.5Z'/></svg>`,
                        light: `<svg viewBox='0 0 24 24'><circle ${base} cx='12' cy='12' r='4'/><path ${base} d='M12 2v2M12 20v2M4 12H2M22 12h-2M5.6 5.6 4.2 4.2M19.8 19.8l-1.4-1.4M18.4 5.6l1.4-1.4M4.2 19.8l1.4-1.4'/></svg>`,
                        wind: `<svg viewBox='0 0 24 24'><path ${base} d='M4 12h11a3 3 0 1 0-3-3'/><path ${base} d='M2 16h13a4 4 0 1 1-4 4'/></svg>`,
                        rain: `<svg viewBox='0 0 24 24'><path ${base} d='M7 18c1.5-2 3-4.667 5-9 2 4.333 3.5 7 5 9a5 5 0 0 1-10 0Z'/></svg>`,
                        tank: `<svg viewBox='0 0 24 24'><rect ${base} x='6' y='3' width='12' height='18' rx='2'/><path ${base} d='M6 8h12'/><path ${base} d='M10 13h4'/></svg>`,
                        battery: `<svg viewBox='0 0 24 24'><rect ${base} x='3' y='8' width='16' height='8' rx='2'/><path ${base} d='M21 10v4'/><path ${base} d='M6 12h4'/></svg>`,
                        devices: `<svg viewBox='0 0 24 24'><rect ${base} x='3' y='4' width='13' height='14' rx='2'/><path ${base} d='M8 20h12V8'/><path ${base} d='M12 16h.01'/></svg>`
                    };
                    return icons[key] || icons.temp;
                },
                getCardTheme(key) {
                    const themes = {
                        temp: 'hover:border-red-200',
                        humidity: 'hover:border-blue-200',
                        light: 'hover:border-yellow-200',
                        wind: 'hover:border-cyan-200',
                        rain: 'hover:border-indigo-200',
                        tank: 'hover:border-green-200',
                        battery: 'hover:border-orange-200',
                        devices: 'hover:border-purple-200'
                    };
                    return themes[key] || themes.temp;
                },
                getCardGradient(key) {
                    const gradients = {
                        temp: 'background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)',
                        humidity: 'background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)',
                        light: 'background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%)',
                        wind: 'background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%)',
                        rain: 'background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%)',
                        tank: 'background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)',
                        battery: 'background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%)',
                        devices: 'background: linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%)'
                    };
                    return gradients[key] || gradients.temp;
                },
                getIconBackground(key) {
                    const backgrounds = {
                        temp: 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                        humidity: 'background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
                        light: 'background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                        wind: 'background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
                        rain: 'background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
                        tank: 'background: linear-gradient(135deg, #10b981 0%, #059669 100%)',
                        battery: 'background: linear-gradient(135deg, #f97316 0%, #ea580c 100%)',
                        devices: 'background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)'
                    };
                    return backgrounds[key] || backgrounds.temp;
                },
                getGaugeColor(key) {
                    const colors = {
                        temp: '#ef4444',
                        humidity: '#3b82f6',
                        light: '#f59e0b',
                        wind: '#06b6d4',
                        rain: '#6366f1',
                        tank: '#10b981',
                        battery: '#f97316',
                        devices: '#8b5cf6'
                    };
                    return colors[key] || colors.temp;
                },
                getLinearGradient(key) {
                    const gradients = {
                        temp: 'background: linear-gradient(0deg, #fecaca 0%, #fee2e2 100%)',
                        humidity: 'background: linear-gradient(0deg, #bfdbfe 0%, #dbeafe 100%)',
                        light: 'background: linear-gradient(0deg, #fed7aa 0%, #fef3c7 100%)',
                        wind: 'background: linear-gradient(0deg, #a5f3fc 0%, #cffafe 100%)',
                        rain: 'background: linear-gradient(0deg, #c7d2fe 0%, #e0e7ff 100%)',
                        tank: 'background: linear-gradient(0deg, #a7f3d0 0%, #d1fae5 100%)',
                        battery: 'background: linear-gradient(0deg, #fdba74 0%, #fed7aa 100%)',
                        devices: 'background: linear-gradient(0deg, #ddd6fe 0%, #e9d5ff 100%)'
                    };
                    return gradients[key] || gradients.temp;
                },
                computeTopMetrics() {
                    // Temperature
                    let temp = this.weatherSummary?.temp;
                    if ((temp == null || temp === '-') && this.devices.length) {
                        const tVals = this.devices.map(d => d.temperature_c).filter(v => v != null);
                        if (tVals.length) temp = tVals.reduce((a, b) => a + b, 0) / tVals.length;
                    }
                    if (temp != null && temp !== '-') this.updateMetric('temp', parseFloat(temp), 'now');
                    // Humidity
                    const hum = this.weatherSummary?.humidity;
                    if (hum != null && hum !== '-') this.updateMetric('humidity', parseFloat(hum), 'BMKG');
                    // Light
                    const light = this.weatherSummary?.light_pct;
                    if (light != null) this.updateMetric('light', parseFloat(light), 'estimasi');
                    // Wind
                    const ws = this.weatherSummary?.wind_speed;
                    if (ws != null && ws !== '-') this.updateMetric('wind', parseFloat(ws), this.weatherSummary?.wind_dir ||
                        '');
                    // Rain
                    if (this.weatherSummary?.rain != null) this.updateMetric('rain', parseFloat(this.weatherSummary.rain),
                        'current');
                    // Tank
                    if (this.tank?.percentage != null) this.updateMetric('tank', parseFloat(this.tank.percentage), 'level');
                    // Battery average
                    if (this.devices.length) {
                        const pcts = this.devices.map(d => {
                            if (d.battery_voltage_v == null) return null;
                            const v = parseFloat(d.battery_voltage_v);
                            if (isNaN(v)) return null;
                            return Math.max(0, Math.min(100, ((v - 3.3) / (4.2 - 3.3)) * 100));
                        }).filter(v => v != null);
                        if (pcts.length) {
                            const avg = pcts.reduce((a, b) => a + b, 0) / pcts.length;
                            this.updateMetric('battery', avg, pcts.length + ' node');
                        }
                    }
                    // Devices count
                    this.updateMetric('devices', this.devices.length, 'online');
                    // After metrics update ensure tooltips dataset refreshed
                    this.refreshMetricTooltips();
                },
                refreshMetricTooltips() {
                    // Attach title attribute dynamically to overlay chips (executed after DOM paint)
                    this.$nextTick(() => {
                        document.querySelectorAll('[data-metric-chip]').forEach(el => {
                            const k = el.getAttribute('data-metric-chip');
                            const snap = this.metricSnapshots[k];
                            if (snap) {
                                el.title =
                                    `${snap.value} • ${snap.ts.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}`;
                            }
                        });
                    });
                },
                openFullMap() {
                    this.showFullMap = true;
                    this.$nextTick(() => this.initLeafletFull());
                },
                closeFullMap() {
                    this.showFullMap = false;
                },
                initLeaflet() {
                    if (this.leafletInited || !window.L) return;
                    const map = L.map('leafletMap', {
                        zoomControl: true,
                        attributionControl: false
                    }).setView([this.villageCenter.lat, this.villageCenter.lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(map);
                    // polygon
                    const poly = L.polygon(this.villagePolygon, {
                        color: '#16a34a',
                        weight: 2,
                        fillOpacity: 0.08
                    }).addTo(map);
                    L.marker([this.villageCenter.lat, this.villageCenter.lng], {
                        title: 'Lokasi'
                    }).addTo(map);
                    map.fitBounds(poly.getBounds(), {
                        padding: [20, 20]
                    });

                    // Force low z-index for leaflet container to prevent modal overlap
                    setTimeout(() => {
                        const container = document.getElementById('leafletMap');
                        if (container) {
                            const leafletContainer = container.querySelector('.leaflet-container');
                            if (leafletContainer) {
                                leafletContainer.style.zIndex = '1';
                            }
                        }
                    }, 100);

                    this.leafletInited = true;
                },
                initLeafletFull() {
                    if (this.leafletFullInited || !window.L) return;
                    const map = L.map('leafletMapFull', {
                        zoomControl: true
                    }).setView([this.villageCenter.lat, this.villageCenter.lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(map);
                    const poly = L.polygon(this.villagePolygon, {
                        color: '#15803d',
                        weight: 2,
                        fillOpacity: 0.1
                    }).addTo(map);
                    L.marker([this.villageCenter.lat, this.villageCenter.lng], {
                        title: 'Lokasi'
                    }).addTo(map);
                    map.fitBounds(poly.getBounds(), {
                        padding: [40, 40]
                    });
                    this.leafletFullInited = true;
                },
                async loadDevices() {
                    this.loadingDevices = true;
                    try {
                        const r = await fetch('/api/sensor-readings/latest-per-device');
                        const j = await r.json();
                        if (!r.ok) throw new Error(j.message || 'err');
                        this.devices = (j.data || []).map(x => ({
                            id: x.device_id, // backend returns device_id as numeric id field name (DB id)
                            device_id: x.device_id,
                            device_name: x.device_name || x.device_id,
                            temperature_c: x.temperature_c ?? x.temperature,
                            soil_moisture_pct: x.soil_moisture_pct ?? x.soil_moisture,
                            battery_voltage_v: x.battery_voltage_v,
                            light_lux: x.light_lux,
                            recorded_at: x.recorded_at,
                            status: x.status || 'normal',
                            water_usage_today_l: x.water_usage_today_l ? parseFloat(x.water_usage_today_l) :
                                null
                        }));
                        this.computeTopMetrics();
                    } catch (e) {
                        console.error('Device fetch error', e);
                        this.fetchError = true;
                    } finally {
                        this.loadingDevices = false;
                    }
                },
                async loadDeviceDetail(deviceId) {
                    this.loadingDeviceDetail = true;
                    this.deviceSessions = [];
                    this.deviceUsageHistory = [];
                    try {
                        const [sessionsResp, historyResp] = await Promise.all([
                            fetch(`/api/devices/${deviceId}/irrigation/sessions`),
                            fetch(`/api/devices/${deviceId}/usage-history`)
                        ]);
                        if (sessionsResp.ok) {
                            const js = await sessionsResp.json();
                            // Backend returns { sessions: [...], summary: {...} }
                            this.deviceSessions = js.sessions || [];
                            this.deviceSessionsSummary = js.summary || null;
                            this.buildTasks();
                        }
                        if (historyResp.ok) {
                            const jh = await historyResp.json();
                            // Backend returns { history: [...] }
                            this.deviceUsageHistory = jh.history || [];
                        }
                    } catch (e) {
                        console.error('Device detail error', e);
                    } finally {
                        this.loadingDeviceDetail = false;
                    }
                },
                openDeviceModal(d) {
                    this.selectedDevice = d;
                    this.showDeviceModal = true;
                    // Use numeric id if available for route model binding
                    const key = d.id || d.device_id;
                    this.loadDeviceDetail(key);
                },
                closeDeviceModal() {
                    this.showDeviceModal = false;
                    this.selectedDevice = null;
                    this.deviceSessions = [];
                    this.deviceUsageHistory = [];
                },
                async loadTank() {
                    try {
                        const r = await fetch('/api/water-storage');
                        const j = await r.json();
                        if (!r.ok) throw new Error();
                        const t = (j.data || [])[0];
                        if (t) {
                            this.tank = {
                                id: t.id,
                                tank_name: t.tank_name,
                                current_volume_liters: parseFloat(t.current_volume),
                                capacity_liters: parseFloat(t.total_capacity),
                                percentage: parseFloat(t.percentage),
                                status: t.status
                            };
                            this.tankUpdatedAt = new Date().toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            this.computeTopMetrics();
                        }
                    } catch (e) {
                        console.error('Tank fetch error', e);
                        this.fetchError = true;
                    }
                },
                async loadPlan() {
                    try {
                        const r = await fetch('/api/irrigation/today-plan');
                        const j = await r.json();
                        if (!r.ok) throw new Error();
                        if (j.data) {
                            this.plan = j.data;
                            // Plan currently not represented as metric gauge; could be added later
                            this.buildTasks();
                        }
                    } catch (e) {
                        console.error('Plan fetch error', e);
                        this.fetchError = true;
                    }
                },
                async loadUsage() {
                    try {
                        const r = await fetch('/api/water-storage/daily-usage');
                        const j = await r.json();
                        if (!r.ok) throw new Error();
                        this.usage = j.data || [];
                        this.renderUsageChart30d();
                    } catch (e) {
                        console.error('Usage fetch error', e);
                        // Fallback with mock data for demo
                        this.usage = this.generateMock30dData();
                        this.renderUsageChart30d();
                    }
                },
                async loadUsageDaily() {
                    try {
                        const r = await fetch('/api/water-storage/hourly-usage');
                        const j = await r.json();
                        if (!r.ok) throw new Error();
                        this.usage24h = j.data || [];
                        this.renderUsageChart24h();
                    } catch (e) {
                        console.error('24h Usage fetch error', e);
                        // Fallback with mock data for demo
                        this.usage24h = this.generateMock24hData();
                        this.renderUsageChart24h();
                    }
                },
                generateMock24hData() {
                    const data = [];
                    for (let i = 0; i < 24; i++) {
                        const hour = i.toString().padStart(2, '0');
                        let usage = 0;
                        // Simulate higher usage during day hours
                        if (i >= 6 && i <= 18) {
                            usage = Math.random() * 15 + 5; // 5-20L
                        } else {
                            usage = Math.random() * 5; // 0-5L
                        }
                        data.push({
                            hour: hour,
                            total_l: Math.round(usage * 10) / 10,
                            datetime: `2025-09-22 ${hour}:00:00`
                        });
                    }
                    return data;
                },
                generateMock30dData() {
                    const data = [];
                    const today = new Date();
                    for (let i = 29; i >= 0; i--) {
                        const date = new Date(today);
                        date.setDate(today.getDate() - i);
                        const dateStr = date.toISOString().split('T')[0];

                        // Simulate daily water usage (50-200L per day)
                        const baseUsage = 100 + Math.sin(i * 0.1) * 30; // Wave pattern
                        const randomVariation = (Math.random() - 0.5) * 40;
                        const usage = Math.max(50, baseUsage + randomVariation);

                        data.push({
                            date: dateStr,
                            usage_date: dateStr,
                            total_l: parseFloat(usage.toFixed(1))
                        });
                    }
                    return data;
                },
                async loadAll(force = false) {
                    if (this.loadingAll && !force) return;
                    this.loadingAll = true;
                    this.fetchError = false;
                    await Promise.all([this.loadDevices(), this.loadTank(), this.loadPlan(), this.loadUsage(), this
                        .loadUsageDaily()
                    ]);
                    // After core data loaded, derive light & wind and fetch weather
                    this.computeLightWindFromDevices();
                    this.loadEnvStats();
                    this.lastUpdated = new Date();
                    this.computeTopMetrics();
                    this.loadingAll = false;
                },
                computeLightWindFromDevices() {
                    if (!this.devices.length) return;
                    const luxVals = this.devices.map(d => d.light_lux).filter(v => v != null);
                    const windVals = this.devices.map(d => d.wind_speed_ms).filter(v => v != null);
                    if (luxVals.length) {
                        const avgLux = Math.round(luxVals.reduce((a, b) => a + b, 0) / luxVals.length);
                        this.updateMetric('light', avgLux, `avg ${luxVals.length}`);
                    }
                    if (windVals.length) {
                        const maxWind = Math.max(...windVals);
                        this.updateMetric('wind', (Math.round(maxWind * 10) / 10), 'max');
                    }
                },
                loadEnvStats() {
                    // Attempt backend proxy (recommended to implement) else fallback direct
                    fetch('/api/bmkg/forecast')
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(data => {
                            let first = null;
                            let entries = [];
                            if (Array.isArray(data) && data.length) entries = data;
                            else if (data && Array.isArray(data.entries)) entries = data.entries;
                            if (entries.length) {
                                this.processForecast(entries);
                                first = entries[0];
                                if (first) this.applyWeatherEntry(first);
                            }
                        })
                        .catch(() => {
                            fetch('https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=32.08.10.2001')
                                .then(r => r.json())
                                .then(raw => {
                                    try {
                                        const blocks = raw?.data?.[0]?.cuaca;
                                        if (Array.isArray(blocks)) {
                                            const flat = [];
                                            blocks.forEach(b => Array.isArray(b) && b.forEach(e => flat.push(e)));
                                            flat.sort((a, b) => new Date(a.local_datetime) - new Date(b
                                                .local_datetime));
                                            if (flat.length) {
                                                this.processForecast(flat);
                                                this.applyWeatherEntry(flat[0]);
                                            }
                                        }
                                    } catch (e) {
                                        console.warn('weather parse', e);
                                    }
                                });
                        });
                },
                processForecast(list) {
                    // Normalize & store
                    this.forecastEntries = list.map(e => ({
                        local_datetime: e.local_datetime || e.datetime || null,
                        temp: e.t ?? e.temperature_c,
                        humidity: e.humidity ?? e.hu ?? e.h,
                        rain: e.rain ?? e.tp ?? null,
                        label: this.translateWeather(e.weather_desc || e.weather_desc_en || e.weather_desc_id ||
                            e.weather),
                        icon: e.weather_icon || e.image || null,
                        wind_speed: e.wind_speed_ms ?? e.ws ?? null,
                        wind_dir: e.wind_dir_cardinal || e.wd || null,
                        tcc: e.tcc ?? null
                    })).filter(e => e.local_datetime);
                    // 24h slice
                    const now = Date.now();
                    this.forecast24h = this.forecastEntries.filter(e => new Date(e.local_datetime) - now < 24 * 3600 * 1000)
                        .slice(0, 12).map(e => ({
                            ...e,
                            hour: new Date(e.local_datetime).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            })
                        }));
                    // Weekly group (by date)
                    const map = {};
                    this.forecastEntries.forEach(e => {
                        const d = new Date(e.local_datetime);
                        const key = d.toISOString().substring(0, 10);
                        if (!map[key]) map[key] = {
                            temps: [],
                            rains: [],
                            icons: [],
                            labels: [],
                            date: key
                        };
                        map[key].temps.push(e.temp);
                        if (e.rain != null) map[key].rains.push(e.rain);
                        if (e.icon) map[key].icons.push(e.icon);
                        if (e.label) map[key].labels.push(e.label);
                    });
                    this.forecastWeekly = Object.values(map).slice(0, 7).map(g => {
                        const dt = new Date(g.date + 'T00:00:00');
                        return {
                            date: g.date,
                            day: dt.toLocaleDateString('id-ID', {
                                weekday: 'long'
                            }),
                            min: Math.min(...g.temps),
                            max: Math.max(...g.temps),
                            rain: g.rains.length ? (Math.round((g.rains.reduce((a, b) => a + b, 0)) * 10) / 10) :
                                null,
                            icon: g.icons[0] || null,
                            label: g.labels[0] || ''
                        };
                    });
                    // Build summary for today
                    if (this.forecastEntries.length) {
                        const today = new Date().toISOString().substring(0, 10);
                        const todayEntries = this.forecastEntries.filter(e => e.local_datetime.startsWith(today));
                        const temps = todayEntries.map(e => e.temp).filter(v => v != null);
                        const first = this.forecastEntries[0];
                        this.weatherSummary = {
                            temp: first?.temp ?? '-',
                            label: first?.label || '-',
                            humidity: first?.humidity ?? '-',
                            wind_speed: first?.wind_speed ?? '-',
                            wind_dir: first?.wind_dir ?? '',
                            rain: first?.rain ?? null,
                            light_pct: first?.tcc != null ? Math.max(0, Math.min(100, 100 - first.tcc)) : null,
                            icon: first?.icon || null,
                            time: first?.local_datetime ? new Date(first.local_datetime).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : '',
                            temp_min: temps.length ? Math.min(...temps) : null,
                            temp_max: temps.length ? Math.max(...temps) : null
                        };
                    }
                    this.buildCalendar();
                    this.buildWeekView();
                    this.buildTasks();
                },
                buildCalendar() {
                    const year = this.calendarBase.getFullYear();
                    const month = this.calendarBase.getMonth();
                    const firstDay = new Date(year, month, 1);
                    const startWeekDay = (firstDay.getDay() + 6) % 7; // make Monday index 0
                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const prevMonthDays = startWeekDay;
                    const totalCells = Math.ceil((prevMonthDays + daysInMonth) / 7) * 7;
                    const result = [];
                    for (let i = 0; i < totalCells; i++) {
                        const dayNum = i - prevMonthDays + 1;
                        const d = new Date(year, month, dayNum);
                        const isCurrentMonth = dayNum >= 1 && dayNum <= daysInMonth;
                        const iso = d.toISOString().substring(0, 10);
                        const fEntries = this.forecastEntries.filter(e => e.local_datetime.startsWith(iso));
                        const temps = fEntries.map(e => e.temp).filter(v => v != null);
                        const rainVals = fEntries.map(e => e.rain).filter(v => v != null);
                        const rainSum = rainVals.length ? Math.round(rainVals.reduce((a, b) => a + b, 0) * 10) / 10 : null;
                        const icon = fEntries.find(e => e.icon)?.icon || null;
                        const label = fEntries.find(e => e.label)?.label || '';
                        const usageForDay = this.usage.find(u => u.date === iso || u.day === iso);
                        result.push({
                            key: iso,
                            date: iso,
                            day: d.getDate(),
                            isCurrentMonth,
                            icon,
                            label,
                            tempRange: temps.length ? (Math.min(...temps) + '/' + Math.max(...temps)) : '',
                            rain: rainSum,
                            usage_l: usageForDay ? parseFloat(usageForDay.total_l || usageForDay.volume_l) : null,
                            entries: fEntries.length
                        });
                    }
                    this.calendarDays = result;
                    this.calendarMonthLabel = firstDay.toLocaleDateString('id-ID', {
                        month: 'long',
                        year: 'numeric'
                    });
                },
                buildWeekView() {
                    const start = new Date();
                    const monday = new Date(start.setDate(start.getDate() - ((start.getDay() + 6) % 7) + this.weekOffset *
                        7));
                    const days = [];
                    for (let i = 0; i < 7; i++) {
                        const d = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + i);
                        const iso = d.toISOString().substring(0, 10);
                        const fEntries = this.forecastEntries.filter(e => e.local_datetime.startsWith(iso));
                        let avgTemp = '-';
                        let forecastIcon = null;
                        let forecastLabel = '';
                        let category = 'idle';
                        let style = this.categoryStyles['idle'];
                        if (fEntries.length) {
                            const temps = fEntries.map(e => e.temp).filter(v => v != null);
                            if (temps.length) avgTemp = Math.round(temps.reduce((a, b) => a + b, 0) / temps.length);
                            // Pilih entri mendekati tengah hari (12:00) sebagai ikon; fallback 11/13; lalu pertama.
                            const midday = fEntries.find(e => /T12:00:00/.test(e.local_datetime)) || fEntries.find(e =>
                                /T11:00:00|T13:00:00/.test(e.local_datetime)) || fEntries[0];
                            forecastIcon = midday?.icon || midday?.weather_icon || null;
                            forecastLabel = midday?.label || this.translateWeather(midday?.weather_desc) || '';
                            // Hitung curah hujan total untuk kategorisasi.
                            const rainVals = fEntries.map(e => e.rain).filter(v => v != null);
                            const rainSum = rainVals.length ? rainVals.reduce((a, b) => a + b, 0) : 0;
                            const cfg = this.categoryConfig;
                            if (rainSum >= (cfg.shipment?.minRain ?? 5)) category = 'ship';
                            else if (avgTemp !== '-' && rainSum <= (cfg.fertilization?.maxRain ?? 2) && avgTemp >= (cfg
                                    .fertilization?.minTemp ?? 30)) category = 'fert';
                            else if (avgTemp !== '-' && rainSum <= (cfg.plowing?.maxRain ?? 2) && avgTemp < (cfg.plowing
                                    ?.maxTemp ?? 30)) category = 'plowing';
                            style = this.categoryStyles[category] || this.categoryStyles['idle'];
                        }
                        const obj = {
                            date: iso,
                            day: d.getDate(),
                            temp: avgTemp === '-' ? '-' : avgTemp + '°',
                            weekdayShort: d.toLocaleDateString('id-ID', {
                                weekday: 'short'
                            }),
                            category,
                            categoryBg: style.bg,
                            icon: forecastIcon, // only show real BMKG icon if available
                            label: forecastLabel || (avgTemp === '-' ? '' : forecastLabel),
                            active: false
                        };
                        const todayIso = new Date().toISOString().substring(0, 10);
                        if (iso === todayIso) obj.active = true;
                        days.push(obj);
                    }
                    // Fallback untuk hari minggu ini yang tidak punya data BMKG: gunakan hari terdekat yang punya data
                    // (utamakan mundur ke belakang, jika tidak ada ambil yang di depan). Tandai dengan estimated flag.
                    const todayIso = new Date().toISOString().substring(0, 10);
                    for (let i = 0; i < days.length; i++) {
                        const d = days[i];
                        if (d.temp === '-' && d.date <= todayIso) {
                            let src = null;
                            for (let b = i - 1; b >= 0; b--) {
                                if (days[b].temp !== '-') {
                                    src = days[b];
                                    break;
                                }
                            }
                            if (!src) {
                                for (let f = i + 1; f < days.length; f++) {
                                    if (days[f].temp !== '-') {
                                        src = days[f];
                                        break;
                                    }
                                }
                            }
                            if (!src && this.weatherSummary && this.weatherSummary.temp) {
                                src = {
                                    temp: Math.round(this.weatherSummary.temp) + '°',
                                    icon: this.weatherSummary.icon,
                                    label: this.weatherSummary.label,
                                    category: 'idle',
                                    categoryBg: this.categoryStyles['idle'].bg
                                };
                            }
                            if (src) {
                                d.temp = src.temp;
                                d.icon = d.icon || src.icon;
                                d.label = d.label || src.label;
                                d.categoryBg = d.categoryBg == this.categoryStyles['idle'].bg ? src.categoryBg || d
                                    .categoryBg : d.categoryBg;
                                d.estimated = true;
                            }
                        }
                    }
                    this.weekViewDays = days;
                },
                shiftWeek(delta) {
                    this.weekOffset += delta;
                    this.buildWeekView();
                },
                selectWeekDay(day) {
                    this.weekViewDays.forEach(d => d.active = d.date === day.date);
                },
                refreshTasks() {
                    this.buildTasks();
                },
                buildTasks() {
                    // Placeholder task derivation from irrigation plan & usage summary
                    const tasks = [];
                    if (this.plan && this.plan.adjusted_total_l) {
                        const diff = this.plan.adjusted_total_l - (this.deviceSessionsSummary?.total_actual_l || 0);
                        if (diff > 0) {
                            tasks.push({
                                id: 'water-deficit',
                                title: 'Penjadwalan Penyiraman',
                                desc: `Masih kurang <b>${Math.round(diff)} L</b> dari target hari ini`,
                                badgeValue: 'Kini',
                                badgeLabel: 'butuh',
                                color: 'bg-red-500',
                                tag: 'Irigasi',
                                tagColor: 'bg-red-100 text-red-700'
                            });
                        }
                    }
                    if (this.weatherSummary && this.weatherSummary.rain != null && this.weatherSummary.rain > 5) {
                        tasks.push({
                            id: 'rain-adjust',
                            title: 'Curah Hujan Tinggi',
                            desc: 'Pertimbangkan pengurangan sesi irigasi.',
                            badgeValue: '6j',
                            badgeLabel: 'ke depan',
                            color: 'bg-green-600',
                            tag: 'Cuaca',
                            tagColor: 'bg-green-100 text-green-700'
                        });
                    }
                    this.currentTasks = tasks;
                },
                prevMonth() {
                    this.calendarBase = new Date(this.calendarBase.getFullYear(), this.calendarBase.getMonth() - 1, 1);
                    this.buildCalendar();
                },
                nextMonth() {
                    this.calendarBase = new Date(this.calendarBase.getFullYear(), this.calendarBase.getMonth() + 1, 1);
                    this.buildCalendar();
                },
                selectDay(d) {
                    this.selectedDate = d.date;
                    this.calendarDetails = {
                        date: d.date,
                        dateHuman: new Date(d.date + 'T00:00:00').toLocaleDateString('id-ID', {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'long'
                        }),
                        min: d.tempRange ? d.tempRange.split('/')[0] : '-',
                        max: d.tempRange ? d.tempRange.split('/')[1] : '-',
                        rain: d.rain,
                        usage_l: d.usage_l != null ? d.usage_l.toFixed(1) : null,
                        entries: d.entries
                    };
                },
                applyWeatherEntry(entry) {
                    if (!entry) return;
                    const desc = entry.weather_desc || entry.weather_desc_id || entry.weather || '';
                    const temp = entry.t;
                    const hum = entry.humidity ?? entry.hu ?? entry.h;
                    // If only numeric code present, map it
                    const code = entry.weather_code ?? entry.weather;
                    const codeMap = {
                        0: 'Cerah',
                        1: 'Cerah',
                        2: 'Cerah Berawan',
                        3: 'Berawan',
                        4: 'Berawan',
                        5: 'Udara Kabur',
                        10: 'Asap',
                        45: 'Kabut',
                        60: 'Hujan Ringan',
                        61: 'Hujan',
                        63: 'Hujan Lebat',
                        80: 'Hujan Lokal',
                        95: 'Badai Petir'
                    };
                    let label = this.translateWeather(desc);
                    if ((!label || label === '-') && typeof code === 'number' && codeMap[code]) label = codeMap[code];
                    if ((!label || label === '-') && entry.weather_desc_en) label = this.translateWeather(entry
                        .weather_desc_en);
                    if (!label || label === '-') {
                        console.warn('Weather description missing/raw entry:', entry);
                    }
                    // update metrics directly
                    if (temp != null) this.updateMetric('temp', parseFloat(temp), 'now');
                    if (hum != null) this.updateMetric('humidity', parseFloat(hum), 'BMKG');
                    // Keep icon reference (for future use)
                    this.weatherIcon = entry.weather_icon || entry.image || null;
                    // Wind
                    const ws = entry.wind_speed_ms ?? entry.ws;
                    if (ws != null) {
                        const wsNum = parseFloat(ws);
                        if (!isNaN(wsNum)) this.updateMetric('wind', (Math.round(wsNum * 10) / 10), entry
                            .wind_dir_cardinal || entry.wd || '');
                    }
                    // Light estimation: tcc already 0-100 (cloudiness). Light% = 100 - tcc.
                    if (entry.tcc != null) {
                        const tcc = parseFloat(entry.tcc);
                        if (!isNaN(tcc)) {
                            const lightPct = Math.max(0, Math.min(100, 100 - tcc));
                            this.updateMetric('light', Math.round(lightPct), 'estimasi');
                        }
                    }
                    this.computeTopMetrics();
                },
                translateWeather(code) {
                    const c = (code || '').toString().toLowerCase();
                    if (c.includes('cerah') || c.includes('sun')) return 'Cerah';
                    if (c.includes('berawan') || c.includes('cloud')) return 'Berawan';
                    if (c.includes('mendung') || c.includes('overcast')) return 'Mendung';
                    if (c.includes('hujan') || c.includes('rain')) return 'Hujan';
                    if (c.includes('malam') || c.includes('night')) return 'Malam';
                    return code || '-';
                },

                renderUsageChart24h() {
                    const el = document.getElementById('usageChart24h');
                    if (!el) return;
                    const labels = this.usage24h.map(r => r.hour + ':00');
                    const data = this.usage24h.map(r => r.total_l);
                    if (this.usageChart24h) {
                        this.usageChart24h.data.labels = labels;
                        this.usageChart24h.data.datasets[0].data = data;
                        this.usageChart24h.update();
                        return;
                    }
                    const watermark = {
                        id: 'sisWatermark24h',
                        afterDraw(chart, args, opts) {
                            const {
                                ctx,
                                chartArea: {
                                    left,
                                    top,
                                    width,
                                    height
                                }
                            } = chart;
                            ctx.save();
                            ctx.globalAlpha = 0.06;
                            ctx.translate(left + width / 2, top + height / 2);
                            ctx.scale(3, 3);
                            ctx.strokeStyle = '#3b82f6';
                            ctx.lineWidth = 0.8;
                            ctx.lineCap = 'round';
                            ctx.beginPath();
                            // clock-like shape
                            ctx.arc(0, 0, 3, 0, 2 * Math.PI);
                            ctx.moveTo(0, 0);
                            ctx.lineTo(0, -2);
                            ctx.moveTo(0, 0);
                            ctx.lineTo(1.5, 0);
                            ctx.stroke();
                            ctx.restore();
                        }
                    };
                    this.usageChart24h = new Chart(el.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Liter/Jam',
                                data,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.2)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                }
                            }
                        },
                        plugins: [watermark]
                    });
                },
                renderUsageChart30d() {
                    const el = document.getElementById('usageChart30d');
                    if (!el) {
                        console.log('Element usageChart30d not found');
                        return;
                    }
                    console.log('30d data:', this.usage);
                    const labels = this.usage.map(r => {
                        // Format tanggal menjadi DD-MM-YYYY
                        const date = new Date(r.date);
                        if (isNaN(date)) return r.date; // fallback jika parsing gagal
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        return `${day}-${month}-${year}`;
                    });
                    const data = this.usage.map(r => r.total_l);
                    console.log('30d labels:', labels, 'data:', data);
                    if (this.usageChart) {
                        this.usageChart.data.labels = labels;
                        this.usageChart.data.datasets[0].data = data;
                        this.usageChart.update();
                        return;
                    }
                    const watermark = {
                        id: 'sisWatermark30d',
                        afterDraw(chart, args, opts) {
                            const {
                                ctx,
                                chartArea: {
                                    left,
                                    top,
                                    width,
                                    height
                                }
                            } = chart;
                            ctx.save();
                            ctx.globalAlpha = 0.06;
                            ctx.translate(left + width / 2, top + height / 2);
                            ctx.scale(4, 4);
                            ctx.strokeStyle = '#16a34a';
                            ctx.lineWidth = 0.8;
                            ctx.lineCap = 'round';
                            ctx.beginPath();
                            // simple leaf-like shape
                            ctx.moveTo(0, 3);
                            ctx.quadraticCurveTo(4, 2, 5, -2);
                            ctx.quadraticCurveTo(1, -3, 0, -6);
                            ctx.quadraticCurveTo(-1, -3, -5, -2);
                            ctx.quadraticCurveTo(-4, 2, 0, 3);
                            ctx.stroke();
                            ctx.restore();
                        }
                    };
                    this.usageChart = new Chart(el.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Liter',
                                data,
                                tension: .3,
                                fill: true,
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22,163,74,0.15)',
                                pointRadius: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        display: true,
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        },
                        plugins: [watermark]
                    });
                },
                totalUsage() {
                    return this.usage.reduce((a, b) => a + b.total_l, 0).toFixed(1);
                },
                totalUsage24h() {
                    return this.usage24h.reduce((a, b) => a + b.total_l, 0).toFixed(1);
                },
                avgUsage() {
                    if (!this.usage.length) return '0.0';
                    return (this.totalUsage() / this.usage.length).toFixed(1);
                },
                avgUsage24h() {
                    if (!this.usage24h.length) return '0.0';
                    return (this.totalUsage24h() / this.usage24h.length).toFixed(1);
                },
                peakDay() {
                    if (!this.usage.length) return '-';
                    const peak = this.usage.reduce((max, curr) => curr.total_l > max.total_l ? curr : max);
                    return `${peak.usage_date} (${peak.total_l}L)`;
                },
                lowDay() {
                    if (!this.usage.length) return '-';
                    const low = this.usage.reduce((min, curr) => curr.total_l < min.total_l ? curr : min);
                    return `${low.usage_date} (${low.total_l}L)`;
                },
                peakHour24h() {
                    if (!this.usage24h.length) return '-';
                    const peak = this.usage24h.reduce((max, curr) => curr.total_l > max.total_l ? curr : max);
                    return `${peak.hour}:00 (${peak.total_l}L)`;
                },
                lowHour24h() {
                    if (!this.usage24h.length) return '-';
                    const low = this.usage24h.reduce((min, curr) => curr.total_l < min.total_l ? curr : min);
                    return `${low.hour}:00 (${low.total_l}L)`;
                },
                fmt(v, suf = '') {
                    if (v == null) return '-';
                    const n = parseFloat(v);
                    return isNaN(n) ? '-' : n.toFixed(1) + suf;
                },
                batteryDisplay(d) {
                    if (!d || d.battery_voltage_v == null) return '-';
                    const v = parseFloat(d.battery_voltage_v);
                    if (isNaN(v) || v <= 0) return '-';
                    // Assume Li-Ion 1S range 3.3V (0%) - 4.2V (100%)
                    const pct = Math.max(0, Math.min(100, ((v - 3.3) / (4.2 - 3.3)) * 100));
                    return v.toFixed(2) + 'V (' + pct.toFixed(0) + '%)';
                },
                batteryDisplayShort(d) {
                    if (!d || d.battery_voltage_v == null) return '-';
                    const v = parseFloat(d.battery_voltage_v);
                    if (isNaN(v) || v <= 0) return '-';
                    const pct = Math.max(0, Math.min(100, ((v - 3.3) / (4.2 - 3.3)) * 100));
                    return pct.toFixed(0) + '%';
                },
                tankFillColor() {
                    const p = this.tank?.percentage || 0;
                    if (p < 25) return '#dc2626';
                    if (p < 50) return '#f59e0b';
                    if (p < 75) return '#3b82f6';
                    return '#16a34a';
                },
                tankFillStyle() {
                    const col = this.tankFillColor();
                    return `background: linear-gradient(180deg, ${col}cc 0%, ${col}ee 60%, ${col} 100%); box-shadow: inset 0 2px 4px rgba(0,0,0,0.25);`;
                },
                tankStatusClass() {
                    const s = (this.tank?.status || '').toLowerCase();
                    if (s.includes('krit') || s === 'low') return 'text-red-600';
                    if (s.includes('warning') || s.includes('wasp')) return 'text-amber-600';
                    return 'text-green-600';
                },
                tankLabelClass() {
                    const p = this.tank?.percentage || 0;
                    if (p < 25) return 'bg-red-600/70 text-white';
                    if (p < 50) return 'bg-amber-500/70 text-white';
                    if (p < 75) return 'bg-blue-600/70 text-white';
                    return 'bg-green-600/70 text-white';
                },
                deviceUsageToday(deviceId) {
                    const d = this.devices.find(d => d.device_id === deviceId || d.id === deviceId);
                    if (!d || d.water_usage_today_l == null) return '-';
                    return d.water_usage_today_l.toFixed(0) + 'L';
                },
                timeAgo(ts) {
                    if (!ts) return '-';
                    const d = new Date(ts);
                    const diff = (Date.now() - d) / 60000;
                    if (diff < 1) return 'baru';
                    if (diff < 60) return Math.round(diff) + 'm';
                    const h = diff / 60;
                    if (h < 24) return h.toFixed(1) + 'j';
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    });
                },
                deviceBadgeClass() {
                    return 'bg-gray-100 text-gray-600';
                },
                statusShort(s) {
                    return s?.substring(0, 6) || 'ok';
                },
                sessionColor(st) {
                    return st === 'completed' ? 'text-green-600' : st === 'pending' ? 'text-gray-500' : 'text-yellow-600';
                },
                init() {
                    this.loadAll();
                    setInterval(() => this.loadAll(), 60000);
                    this.tickClock();
                    setInterval(() => this.tickClock(), 1000);
                    // initialize leaflet after slight delay for layout stability
                    setTimeout(() => this.initLeaflet(), 800);
                },
                tickClock() {
                    const now = new Date();
                    const pad = n => n.toString().padStart(2, '0');
                    this.clock.time = pad(now.getHours()) + ':' + pad(now.getMinutes());
                    this.clock.seconds = ':' + pad(now.getSeconds());
                    this.clock.dateLong = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    this.clock.dateShort = now.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    this.clock.day = now.getDate();
                    this.clock.month = now.toLocaleDateString('id-ID', {
                        month: 'short'
                    });
                    this.clock.year = now.getFullYear();
                }
            }
        }
    </script>
</body>

</html>
