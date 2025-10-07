{{-- Location Maps Section --}}
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
