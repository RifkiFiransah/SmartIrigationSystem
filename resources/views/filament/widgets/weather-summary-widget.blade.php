<x-filament-widgets::widget>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Card: Kondisi Cuaca -->
        <x-filament::section class="p-0 overflow-hidden">
            <div class="relative overflow-hidden bg-gradient-to-br from-white to-amber-50/40 p-5">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl"></div>
                <div class="flex items-start gap-4 relative">
                    <div class="shrink-0 flex items-center justify-center w-14 h-14 rounded-xl bg-amber-100 text-amber-600">
                        <x-filament::icon :icon="$this->getConditionIcon()" class="w-8 h-8" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-semibold tracking-tight">Kondisi Cuaca</h3>
                            <span class="px-2 py-0.5 text-[11px] rounded-full bg-amber-100 text-amber-700 font-medium">{{ strtoupper($weather['condition'] ?? 'CERAH') }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Sumber: <span class="font-medium text-gray-700">{{ $weather['source'] ?? '—' }}</span></p>

                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-5 text-sm">
                            <div class="space-y-0.5">
                                <div class="text-[10px] uppercase tracking-wide text-gray-500">Suhu</div>
                                <div class="text-lg font-semibold text-gray-800">{{ isset($weather['temperature']) ? number_format($weather['temperature'],1).'°C' : 'N/A' }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[10px] uppercase tracking-wide text-gray-500">Kelembapan</div>
                                <div class="text-lg font-semibold text-gray-800">{{ isset($weather['humidity']) ? $weather['humidity'].'%' : 'N/A' }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[10px] uppercase tracking-wide text-gray-500">Angin</div>
                                <div class="text-lg font-semibold text-gray-800">{{ isset($weather['wind_speed']) ? number_format($weather['wind_speed'],1).' m/s' : 'N/A' }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[10px] uppercase tracking-wide text-gray-500">Lux (estimasi)</div>
                                <div class="text-lg font-semibold text-gray-800">{{ isset($weather['light_lux']) ? number_format($weather['light_lux']) : 'N/A' }}</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[10px] uppercase tracking-wide text-gray-500">Diperbarui</div>
                                <div class="text-lg font-semibold text-gray-800">{{ isset($weather['updated_at']) ? \Carbon\Carbon::parse($weather['updated_at'])->setTimezone('Asia/Jakarta')->format('H:i') : '—' }} <span class="text-xs font-normal">WIB</span></div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Status: {{ ucfirst($weather['condition'] ?? 'cerah') }}</span>
                            </div>
                            <button wire:click="refreshWeather" class="px-3 py-1 rounded-md bg-amber-500/10 text-amber-700 hover:bg-amber-500/20 transition text-xs font-medium">Refresh</button>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

    </div>

        @if(isset($forecast['entries']) && count($forecast['entries']))
            <script>
                document.addEventListener('DOMContentLoaded', function(){
                    const ctx = document.getElementById('miniForecastChart');
                    if(!ctx || typeof Chart==='undefined') return;
                    const labels = @json($forecast['labels']);
                    const temps  = @json($forecast['temps']);
                    const emojis = @json(array_map(fn($e)=>$e['emoji'],$forecast['entries']));
                    const chart = new Chart(ctx.getContext('2d'), {
                        type:'line',
                        data:{ labels, datasets:[{ label:'Suhu °C', data:temps, borderColor:'#3b82f6', tension:0.35, fill:true, backgroundColor:'rgba(59,130,246,0.12)', pointRadius:3, pointHoverRadius:5 }]},
                        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:(it)=> it.parsed.y+'°C'} } }, scales:{ x:{ ticks:{ maxRotation:0, autoSkip:true, color:'#64748b', font:{size:10}}, grid:{display:false}}, y:{ ticks:{ color:'#64748b', font:{size:10}}, grid:{ color:'rgba(0,0,0,0.05)'}, suggestedMin:0 } } }
                    });
                    setTimeout(()=>{
                        const overlay = document.getElementById('miniForecastEmojis');
                        if(!overlay) return;
                        const meta = chart.getDatasetMeta(0);
                        overlay.innerHTML='';
                        meta.data.forEach((pt,i)=>{
                            if(!pt) return;
                            const span=document.createElement('span');
                            span.textContent=emojis[i]||'';
                            span.style.position='absolute';
                            span.style.left=(pt.x-7)+'px';
                            span.style.top=(pt.y-18)+'px';
                            span.style.fontSize='12px';
                            overlay.appendChild(span);
                        });
                    },500);
                });
            </script>
        @endif
</x-filament-widgets::widget>
