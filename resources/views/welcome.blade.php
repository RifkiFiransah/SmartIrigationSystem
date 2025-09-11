<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="theme-color" :content="darkMode ? '#0f172a' : '#ffffff'" />
    <title>Irigasi Pintar</title>
    <link rel="icon" type="image/png" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('AgrinexLogo.jpg') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root { color-scheme: light dark; }
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
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body x-data="dashboard()" x-init="applyPersistedTheme()" class="h-full bg-gray-50 text-gray-800 min-h-full">
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('AgrinexLogo.jpg') }}" alt="Logo" class="h-9 w-9 rounded-md object-cover border border-green-200 shadow-sm" loading="lazy">
                <div>
                    <h1 class="text-lg font-semibold text-green-700 leading-tight">Irigasi Pintar</h1>
                    <p class="text-xs text-gray-600 -mt-0.5">Monitoring & otomasi penyiraman</p>
                </div>
                <template x-if="fetchError">
                    <span class="ml-2 text-[10px] px-2 py-0.5 rounded bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-300" x-text="'OFFLINE'" title="Gagal mengambil data terakhir"></span>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <button @click="loadAll(true)" class="btn btn-ghost" :class="loadingAll ? 'opacity-60 pointer-events-none' : ''">
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
                <!-- Top Stats -->
                <section>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                        <template x-for="s in topStats" :key="s.key">
                            <div class="card relative overflow-hidden">
                                <div class="stat-label" x-text="s.label"></div>
                                <div class="stat-value min-h-[1.9rem]" x-show="!s.loading" x-text="s.value"></div>
                                <div x-show="s.loading" class="skeleton h-7 w-20 mt-1"></div>
                                <div class="text-xs text-gray-600 mt-1 font-medium" x-text="s.sub"></div>
                                <div class="absolute right-3 top-3 text-lg opacity-15 select-none" x-text="s.icon"></div>
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
                            <button @click="loadAll()" class="text-xs px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white shadow-md hover:shadow-lg transition-all">Refresh</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" x-show="devices.length">
                            <template x-for="d in devices" :key="d.device_id">
                                <div class="card relative">
                                    <div class="absolute right-2 top-2 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">
                                        <span x-text="statusShort(d.status)"></span>
                                    </div>
                                    <h3 class="font-semibold text-sm text-gray-900" x-text="d.device_name"></h3>
                                    <p class="text-xs text-gray-600 mb-2" x-text="d.location || '-' "></p>
                                    <dl class="text-xs space-y-1 text-gray-700">
                                        <div class="flex justify-between"><dt>Suhu</dt><dd x-text="fmt(d.temperature_c,'°C')"></dd></div>
                                        <div class="flex justify-between"><dt>Tanah</dt><dd x-text="fmt(d.soil_moisture_pct,' %')"></dd></div>
                                        <div class="flex justify-between"><dt>Ketinggian Air</dt><dd x-text="fmt(d.water_height_cm,' cm')"></dd></div>
                                        <div class="flex justify-between"><dt>Cahaya</dt><dd x-text="fmt(d.light_lux,' lx')"></dd></div>
                                    </dl>
                                    <div class="mt-2 text-[10px] text-gray-500" x-text="timeAgo(d.recorded_at)"></div>
                                </div>
                            </template>
                        </div>
                        <div x-show="!devices.length && !loadingDevices" class="text-sm text-gray-600">Tidak ada data perangkat.</div>
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
                            <div class="mt-2">
                                <div class="flex justify-between text-xs mb-1 text-gray-700">
                                    <span>Level</span>
                                    <span x-text="tank.percentage ? tank.percentage.toFixed(1)+'%' : '-' "></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                         :style="`width: ${tank.percentage || 0}%`"></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-700 font-medium" x-text="tank.current_volume_liters ? tank.current_volume_liters.toFixed(0)+' L tersisa' : '—'"></p>
                                <p class="mt-1 text-xs text-gray-600" x-text="'Status: '+ (tank.status || '-')"></p>
                            </div>
                        </div>
                        <div class="card" x-show="plan.sessions && plan.sessions.length">
                            <h3 class="font-semibold mb-2 text-gray-900">Rencana Irigasi (3 Sesi)</h3>
                            <ul class="divide-y text-xs">
                                <template x-for="s in plan.sessions" :key="s.index">
                                    <li class="py-1 flex justify-between items-center">
                                        <span>Sesi <span x-text="s.index"></span> (<span x-text="s.time"></span>)</span>
                                        <span class="font-medium" :class="sessionColor(s.status)" x-text="s.actual_l ? s.actual_l+'L' : s.adjusted_l+'L'"></span>
                                    </li>
                                </template>
                            </ul>
                            <p class="mt-2 text-xs text-gray-600" x-text="plan.status ? 'Status: '+plan.status : ''"></p>
                        </div>
                    </div>
                </section>

                <!-- Usage Chart -->
            <section class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                        <h2 class="font-semibold text-lg text-gray-900">Riwayat Penggunaan Air (30 Hari)</h2>
                        <button @click="loadUsage()" class="text-xs px-4 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white shadow-md hover:shadow-lg transition-all">Refresh</button>
                    </div>
                    <div class="card">
                        <canvas id="usageChart" height="120"></canvas>
                        <div class="mt-2 text-xs text-gray-600" x-text="usage.length ? 'Total '+ totalUsage() +' L / '+usage.length+' hari' : 'Belum ada data penggunaan' "></div>
                    </div>
                </section>
            </main>

    <footer class="text-center py-6 text-xs text-gray-500">&copy; {{ date('Y') }} Smart Irrigation</footer>

    <script>
    function dashboard(){
        return {
            darkMode: localStorage.getItem('sis_dark')==='1',
            loadingAll:false, loadingDevices:false, fetchError:false, lastUpdated:null,
            devices:[], tank:{}, plan:{}, usage:[], usageChart:null,
            topStats:[
                {key:'devices', label:'DEVICE AKTIF', value:'-', sub:'', icon:'📡', loading:true},
                {key:'readings', label:'PEMBACAAN', value:'-', sub:'', icon:'📊', loading:true},
                {key:'tank', label:'TANGKI (%)', value:'-', sub:'', icon:'💧', loading:true},
                {key:'plan', label:'RENCANA AIR', value:'-', sub:'', icon:'🗓️', loading:true},
            ],
            applyPersistedTheme(){ if(this.darkMode){ document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); } },
            persistDark(){ localStorage.setItem('sis_dark', this.darkMode?'1':'0'); this.applyPersistedTheme(); },
            toggleDark(){ this.darkMode = !this.darkMode; this.persistDark(); },
            statBy(k){return this.topStats.find(s=>s.key===k);},
            async loadDevices(){
                this.loadingDevices=true;
                try{const r=await fetch('/api/sensor-readings/latest-per-device');const j=await r.json();if(!r.ok) throw new Error(j.message||'err');
                    this.devices=(j.data||[]).map(x=>({device_id:x.device_id,device_name:x.device_name||x.device_id,temperature_c:x.temperature_c??x.temperature,soil_moisture_pct:x.soil_moisture_pct??x.soil_moisture,water_height_cm:x.water_height_cm,light_lux:x.light_lux,recorded_at:x.recorded_at,status:x.status||'normal'}));
                    const d=this.statBy('devices');d.value=this.devices.length;d.sub=this.devices.length+' online';d.loading=false; const rStat=this.statBy('readings');rStat.value=this.devices.length;rStat.sub='perangkat';rStat.loading=false;
                }catch(e){console.error('Device fetch error',e);this.fetchError=true;}finally{this.loadingDevices=false;}
            },
            async loadTank(){
                try{const r=await fetch('/api/water-storage');const j=await r.json();if(!r.ok) throw new Error(); const t=(j.data||[])[0]; if(t){this.tank={id:t.id,tank_name:t.tank_name,current_volume_liters:parseFloat(t.current_volume),capacity_liters:parseFloat(t.total_capacity),percentage:parseFloat(t.percentage),status:t.status}; const s=this.statBy('tank'); s.value=isNaN(this.tank.percentage)?'-':this.tank.percentage.toFixed(1)+'%'; s.sub=this.tank.current_volume_liters?this.tank.current_volume_liters.toFixed(0)+'/'+this.tank.capacity_liters.toFixed(0)+' L':''; s.loading=false;}}
                catch(e){console.error('Tank fetch error',e);this.fetchError=true;}
            },
            async loadPlan(){
                try{const r=await fetch('/api/irrigation/today-plan');const j=await r.json();if(!r.ok) throw new Error(); if(j.data){this.plan=j.data; const s=this.statBy('plan'); if(this.plan.adjusted_total_l){s.value=this.plan.adjusted_total_l.toFixed(0)+' L'; s.sub=(this.plan.status||'');} s.loading=false;}}
                catch(e){console.error('Plan fetch error',e);this.fetchError=true;}
            },
            async loadUsage(){
                try{const r=await fetch('/api/water-storage/daily-usage');const j=await r.json();if(!r.ok) throw new Error(); this.usage=j.data||[]; this.renderUsageChart();}
                catch(e){console.error('Usage fetch error',e);}
            },
            async loadAll(force=false){ if(this.loadingAll && !force) return; this.loadingAll=true; this.fetchError=false; await Promise.all([this.loadDevices(),this.loadTank(),this.loadPlan(),this.loadUsage()]); this.lastUpdated=new Date(); this.loadingAll=false; },
            renderUsageChart(){const el=document.getElementById('usageChart'); if(!el) return; const labels=this.usage.map(r=>r.date); const data=this.usage.map(r=>r.total_l); if(this.usageChart){this.usageChart.data.labels=labels; this.usageChart.data.datasets[0].data=data; this.usageChart.update(); return;} this.usageChart=new Chart(el.getContext('2d'),{type:'line',data:{labels,datasets:[{label:'Liter',data,tension:.3,fill:true,borderColor:'#16a34a',backgroundColor:'rgba(22,163,74,0.15)',pointRadius:2}]},options:{responsive:true,plugins:{legend:{display:false}}}});},
            totalUsage(){return this.usage.reduce((a,b)=>a+b.total_l,0).toFixed(1);},
            fmt(v,suf=''){if(v==null) return '-'; const n=parseFloat(v); return isNaN(n)?'-':n.toFixed(1)+suf;},
            timeAgo(ts){ if(!ts) return '-'; const d=new Date(ts); const diff=(Date.now()-d)/60000; if(diff<1) return 'baru'; if(diff<60) return Math.round(diff)+'m'; const h=diff/60; if(h<24) return h.toFixed(1)+'j'; return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short'});},
            deviceBadgeClass(){return 'bg-gray-100 text-gray-600';},
            statusShort(s){return s?.substring(0,6)||'ok';},
            sessionColor(st){return st==='completed'?'text-green-600':st==='pending'?'text-gray-500':'text-yellow-600';},
            init(){this.loadAll(); setInterval(()=>this.loadAll(),60000);} 
        }
    }
    </script>
</body>
</html>
