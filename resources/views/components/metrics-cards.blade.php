{{-- Metrics Gauge Cards Section --}}
<section>
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold tracking-wide text-gray-600 uppercase" x-text="t('environmentSummary')">Ringkasan Lingkungan</h2>
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
