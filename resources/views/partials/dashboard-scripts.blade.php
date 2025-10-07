{{-- Dashboard Alpine.js Function --}}
<script>
        function dashboard() {
            return {
                // Language state
                currentLang: localStorage.getItem('sis_lang') || 'id',
                translations: {
                    id: {
                        // Header
                        appTitle: 'Irigasi Pintar',
                        appSubtitle: 'Monitoring & otomasi penyiraman',
                        switchLang: 'Ganti ke Bahasa Inggris',
                        refresh: 'Refresh',
                        loading: 'Memuat',
                        admin: 'Admin',
                        login: 'Masuk',
                        // Weather section
                        currentTime: 'Waktu Sekarang',
                        currentDate: 'Tanggal Hari Ini',
                        currentWeather: 'Cuaca Saat Ini',
                        forecast: 'Prakiraan',
                        next24h: '24 Jam',
                        next7d: 'Minggu',
                        day: 'Tanggal',
                        month: 'Bulan',
                        year: 'Tahun',
                        humidity: 'Kelembapan',
                        windSpeed: 'Kecepatan Angin',
                        pressure: 'Tekanan',
                        lightPercent: 'cahaya',
                        // Tasks
                        activities: 'Aktivitas / Peringatan',
                        weeklyTasks: 'Tugas Minggu Ini',
                        upcomingWeek: 'Minggu Ini',
                        noTasks: 'Tidak ada tugas terjadwal minggu ini',
                        prevWeek: '‹ Minggu Lalu',
                        nextWeek: 'Minggu Depan ›',
                        today: 'Kini butuh',
                        daysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                        // Charts
                        environmentSummary: 'Ringkasan Lingkungan',
                        lightIntensity: 'Intensitas Cahaya',
                        waterLevel: 'Ketinggian Air',
                        soilMoisture: 'Kelembapan Tanah',
                        temperature: 'Suhu',
                        airHumidity: 'Kelembapan Udara',
                        time: 'Waktu',
                        // Metrics
                        temp: 'Suhu',
                        hum: 'Kelembapan Udara',
                        soil: 'Kelembapan Tanah',
                        light: 'Cahaya',
                        rain: 'Hujan',
                        water: 'Ketinggian Air',
                        // Devices
                        devices: 'Perangkat',
                        allDevices: 'Semua Perangkat',
                        noDevices: 'Tidak ada data perangkat',
                        viewDetails: 'Detail',
                        battery: 'Baterai',
                        waterUsageToday: 'Pemakaian Hari Ini',
                        lastUpdate: 'Terakhir',
                        // Tank
                        waterTank: 'Tangki Air',
                        capacity: 'Kapasitas',
                        currentLevel: 'Level Saat Ini',
                        status: 'Status',
                        lastUpdated: 'Terakhir Diperbarui',
                        todaySchedule: 'Jadwal Hari Ini',
                        noSchedule: 'Tidak ada jadwal',
                        // Usage
                        waterUsageHistory: 'Riwayat Penggunaan Air',
                        last30Days: '30 Hari Terakhir',
                        last24Hours: '24 Jam Terakhir',
                        dailyData30: 'Data harian dalam 30 hari terakhir',
                        hourlyData24: 'Data per jam dalam 24 jam terakhir',
                        totalUsage: 'Total',
                        avgUsage: 'Rata-rata',
                        peakUsage: 'Puncak',
                        lowUsage: 'Terendah',
                        days: 'hari',
                        noDataYet: 'Belum ada data',
                        // Location
                        location: 'Lokasi',
                        streetView: 'Street View',
                        villageMap: 'Peta Desa',
                        close: 'Tutup',
                        viewFullMap: 'Lihat Peta Lengkap',
                        // Modal
                        deviceDetails: 'Detail Perangkat',
                        sessions: 'Sesi',
                        usageHistory: 'Riwayat Pemakaian',
                        noData: 'Tidak ada data',
                        // Units
                        celsius: '°C',
                        percent: '%',
                        lux: 'lux',
                        mm: 'mm',
                        cm: 'cm',
                        liter: 'L',
                        kmh: 'km/j',
                        hPa: 'hPa'
                    },
                    en: {
                        // Header
                        appTitle: 'Smart Irrigation',
                        appSubtitle: 'Monitoring & irrigation automation',
                        switchLang: 'Switch to Indonesian',
                        refresh: 'Refresh',
                        loading: 'Loading',
                        admin: 'Admin',
                        login: 'Login',
                        // Weather section
                        currentTime: 'Current Time',
                        currentDate: 'Today\'s Date',
                        currentWeather: 'Current Weather',
                        forecast: 'Forecast',
                        next24h: '24 Hours',
                        next7d: 'Week',
                        day: 'Day',
                        month: 'Month',
                        year: 'Year',
                        humidity: 'Humidity',
                        windSpeed: 'Wind Speed',
                        pressure: 'Pressure',
                        lightPercent: 'light',
                        // Tasks
                        activities: 'Activities / Alerts',
                        weeklyTasks: 'This Week\'s Tasks',
                        upcomingWeek: 'This Week',
                        noTasks: 'No scheduled tasks this week',
                        prevWeek: '‹ Previous Week',
                        nextWeek: 'Next Week ›',
                        today: 'Today need',
                        daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                        // Charts
                        environmentSummary: 'Environmental Summary',
                        lightIntensity: 'Light Intensity',
                        waterLevel: 'Water Level',
                        soilMoisture: 'Soil Moisture',
                        temperature: 'Temperature',
                        airHumidity: 'Air Humidity',
                        time: 'Time',
                        // Metrics
                        temp: 'Temperature',
                        hum: 'Air Humidity',
                        soil: 'Soil Moisture',
                        light: 'Light',
                        rain: 'Rain',
                        water: 'Water Level',
                        // Devices
                        devices: 'Devices',
                        allDevices: 'All Devices',
                        noDevices: 'No device data',
                        viewDetails: 'Details',
                        battery: 'Battery',
                        waterUsageToday: 'Today\'s Usage',
                        lastUpdate: 'Last',
                        // Tank
                        waterTank: 'Water Tank',
                        capacity: 'Capacity',
                        currentLevel: 'Current Level',
                        status: 'Status',
                        lastUpdated: 'Last Updated',
                        todaySchedule: 'Today\'s Schedule',
                        noSchedule: 'No schedule',
                        // Usage
                        waterUsageHistory: 'Water Usage History',
                        last30Days: 'Last 30 Days',
                        last24Hours: 'Last 24 Hours',
                        dailyData30: 'Daily data for the last 30 days',
                        hourlyData24: 'Hourly data for the last 24 hours',
                        totalUsage: 'Total',
                        avgUsage: 'Average',
                        peakUsage: 'Peak',
                        lowUsage: 'Lowest',
                        days: 'days',
                        noDataYet: 'No data yet',
                        // Location
                        location: 'Location',
                        streetView: 'Street View',
                        villageMap: 'Village Map',
                        close: 'Close',
                        viewFullMap: 'View Full Map',
                        // Modal
                        deviceDetails: 'Device Details',
                        sessions: 'Sessions',
                        usageHistory: 'Usage History',
                        noData: 'No data',
                        // Units
                        celsius: '°C',
                        percent: '%',
                        lux: 'lux',
                        mm: 'mm',
                        cm: 'cm',
                        liter: 'L',
                        kmh: 'km/h',
                        hPa: 'hPa'
                    }
                },
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
                // Environmental Charts
                lightIntensityChart: null,
                waterLevelChart: null,
                soilMoistureChart: null,
                temperatureChart: null,
                humidityChart: null,
                lightIntensityData: {
                    labels: [],
                    li1: [],
                    li2: []
                },
                waterLevelData: {
                    labels: [],
                    levels: []
                },
                soilMoistureData: {
                    labels: [],
                    sensors: {} // Will contain SM1, SM2, SM3, etc.
                },
                temperatureData: {
                    labels: [],
                    t1: [],
                    t2: []
                },
                humidityData: {
                    labels: [],
                    h1: [],
                    h2: []
                },
                soilMoistureSensors: [
                    { id: 'SM1', label: 'SM1', color: '#3b82f6' },
                    { id: 'SM4', label: 'SM4', color: '#a855f7' },
                    { id: 'SM2', label: 'SM2', color: '#f97316' },
                    { id: 'SM3', label: 'SM3', color: '#eab308' },
                    { id: 'SM10', label: 'SM10', color: '#84cc16' },
                    { id: 'SM7', label: 'SM7', color: '#ef4444' },
                    { id: 'SM9', label: 'SM9', color: '#ec4899' },
                    { id: 'SM11', label: 'SM11', color: '#22d3ee' },
                    { id: 'SM6', label: 'SM6', color: '#9ca3af' },
                    { id: 'SM5', label: 'SM5', color: '#6366f1' },
                    { id: 'SM8', label: 'SM8', color: '#facc15' }
                ],
                chartMaxPoints: 30,
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
                // Language Methods
                t(key) {
                    return this.translations[this.currentLang][key] || key;
                },
                toggleLanguage() {
                    this.currentLang = this.currentLang === 'id' ? 'en' : 'id';
                    localStorage.setItem('sis_lang', this.currentLang);
                    // Update page title
                    document.title = this.t('appTitle');
                    // Re-render charts with new language
                    this.updateChartLanguage();
                },
                updateChartLanguage() {
                    // Update chart axis labels
                    if (this.lightIntensityChart) {
                        this.lightIntensityChart.options.scales.x.title.text = this.t('time');
                        this.lightIntensityChart.options.scales.y.title.text = `${this.t('lightIntensity')} (${this.t('lux')})`;
                        this.lightIntensityChart.update('none');
                    }
                    if (this.waterLevelChart) {
                        this.waterLevelChart.options.scales.x.title.text = this.t('time');
                        this.waterLevelChart.options.scales.y.title.text = `${this.t('waterLevel')} (${this.t('cm')})`;
                        this.waterLevelChart.update('none');
                    }
                    if (this.soilMoistureChart) {
                        this.soilMoistureChart.options.scales.x.title.text = this.t('time');
                        this.soilMoistureChart.options.scales.y.title.text = `${this.t('soilMoisture')} (${this.t('percent')})`;
                        this.soilMoistureChart.update('none');
                    }
                    if (this.temperatureChart) {
                        this.temperatureChart.options.scales.x.title.text = this.t('time');
                        this.temperatureChart.options.scales.y.title.text = `${this.t('temperature')} (${this.t('celsius')})`;
                        this.temperatureChart.update('none');
                    }
                    if (this.humidityChart) {
                        this.humidityChart.options.scales.x.title.text = this.t('time');
                        this.humidityChart.options.scales.y.title.text = `${this.t('airHumidity')} (${this.t('percent')})`;
                        this.humidityChart.update('none');
                    }
                    if (this.usageChart) {
                        this.usageChart.update('none');
                    }
                    if (this.usageChart24h) {
                        this.usageChart24h.update('none');
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
                metricBy(metricKey) {
                    return this.topMetricCards.find(metric => metric.key === metricKey);
                },
                updateMetric(key, val, desc = '') {
                    const metric = this.metricBy(key);
                    if (!metric) return;
                    if (val == null || isNaN(parseFloat(val))) return; // ignore invalid
                    
                    const newValue = parseFloat(val);
                    
                    // Prevent unnecessary updates that trigger reactivity
                    if (metric.value === newValue && metric.desc === desc) return;
                    
                    metric.value = newValue;
                    if (metric.type === 'plain') {
                        metric.display = metric.value.toFixed(0); // just integer count
                    } else {
                        metric.display = (metric.type === 'gauge' && metric.unit === '%') ? Math.round(metric.value) + metric.unit : (metric.value
                            .toFixed ? metric.value.toFixed((metric.unit === '%' || metric.max <= 20) ? 0 : 1) : metric.value) + metric.unit;
                    }
                    metric.desc = desc;
                    metric.pct = this.normalizePct(metric.value, metric.min, metric.max);
                    metric.color = this.colorFor(metric.pct);
                    // snapshot for tooltip (store first capture per minute)
                    this.metricSnapshots[metric.key] = {
                        value: metric.display,
                        ts: new Date()
                    };
                },
                normalizePct(value, min, max) {
                    if (value == null) return 0;
                    const clamped = Math.max(min, Math.min(max, value));
                    return ((clamped - min) / (max - min)) * 100;
                },
                colorFor(pct) {
                    // 0 red -> 50 orange -> 100 green
                    const hue = (pct * 120) / 100; // 0=red 120=green
                    return `hsl(${hue}, 70%, 45%)`;
                },
                gaugeStyle(metric) {
                    return `background: conic-gradient(${metric.color} 0% ${metric.pct}%, #e5e7eb ${metric.pct}% 100%);`;
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
                        const tempValues = this.devices.map(device => device.temperature_c).filter(value => value != null);
                        if (tempValues.length) temp = tempValues.reduce((accumulator, current) => accumulator + current, 0) / tempValues.length;
                    }
                    if (temp != null && temp !== '-') this.updateMetric('temp', parseFloat(temp), 'now');
                    
                    // Humidity
                    const hum = this.weatherSummary?.humidity;
                    if (hum != null && hum !== '-') this.updateMetric('humidity', parseFloat(hum), 'BMKG');
                    
                    // Light - Multiple fallback strategies
                    let light = null;
                    let lightSource = 'estimasi';
                    
                    // Priority 1: From weather summary
                    if (this.weatherSummary?.light_pct != null) {
                        light = parseFloat(this.weatherSummary.light_pct);
                        lightSource = 'BMKG';
                    }
                    // Priority 2: Calculate from cloud cover (tcc)
                    else if (this.weatherSummary?.tcc != null) {
                        const tcc = parseFloat(this.weatherSummary.tcc);
                        if (!isNaN(tcc)) {
                            light = Math.max(0, Math.min(100, 100 - tcc));
                            lightSource = 'cloud';
                        }
                    }
                    // Priority 3: From device sensors
                    else if (this.devices.length) {
                        const luxValues = this.devices.map(device => device.light_lux).filter(value => value != null && value > 0);
                        if (luxValues.length) {
                            const avgLux = luxValues.reduce((accumulator, current) => accumulator + current, 0) / luxValues.length;
                            light = Math.min(100, (avgLux / 12000) * 100);
                            lightSource = 'sensor';
                        }
                    }
                    
                    // Priority 4: Time-based estimation
                    if (light == null) {
                        const hour = new Date().getHours();
                        if (hour >= 6 && hour <= 18) {
                            const progress = (hour - 6) / 12;
                            light = Math.sin(progress * Math.PI) * 75 + 25;
                        } else {
                            light = 5;
                        }
                        lightSource = 'waktu';
                    }
                    
                    if (light != null) this.updateMetric('light', Math.round(light), lightSource);
                    
                    // Wind
                    const ws = this.weatherSummary?.wind_speed;
                    if (ws != null && ws !== '-') {
                        this.updateMetric('wind', parseFloat(ws), this.weatherSummary?.wind_dir || '');
                    }
                    
                    // Rain - Multiple sources with fallback
                    let rain = null;
                    let rainDesc = 'tidak ada';
                    
                    // Priority 1: From weather summary
                    if (this.weatherSummary?.rain != null) {
                        rain = parseFloat(this.weatherSummary.rain);
                    }
                    // Priority 2: From first forecast entry
                    else if (this.forecastEntries && this.forecastEntries.length > 0) {
                        const firstForecast = this.forecastEntries[0];
                        if (firstForecast?.rain != null) {
                            rain = parseFloat(firstForecast.rain);
                        }
                    }
                    
                    // Default to 0 if no data (means no rain)
                    if (rain == null) rain = 0;
                    
                    // Set description
                    if (rain > 0) {
                        rainDesc = rain > 5 ? 'lebat' : 'ringan';
                    }
                    
                    this.updateMetric('rain', rain, rainDesc);
                    
                    // Tank
                    if (this.tank?.percentage != null) this.updateMetric('tank', parseFloat(this.tank.percentage), 'level');
                    
                    // Battery average
                    if (this.devices.length) {
                        const batteryPercentages = this.devices.map(device => {
                            if (device.battery_voltage_v == null) return null;
                            const voltage = parseFloat(device.battery_voltage_v);
                            if (isNaN(voltage)) return null;
                            return Math.max(0, Math.min(100, ((voltage - 3.3) / (4.2 - 3.3)) * 100));
                        }).filter(percentage => percentage != null);
                        if (batteryPercentages.length) {
                            const avgBattery = batteryPercentages.reduce((accumulator, current) => accumulator + current, 0) / batteryPercentages.length;
                            this.updateMetric('battery', avgBattery, batteryPercentages.length + ' node');
                        }
                    }
                    
                    // Devices count
                    this.updateMetric('devices', this.devices.length, 'online');
                    
                    // After metrics update ensure tooltips dataset refreshed
                    this.refreshMetricTooltips();
                },
                // Environmental Charts Methods
                generateSampleChartData() {
                    // Generate sample data untuk visualisasi awal jika belum ada data
                    const now = new Date();
                    const dataPoints = 15; // Jumlah data point awal
                    
                    // Initialize soil moisture sensors data
                    this.soilMoistureSensors.forEach(sensor => {
                        this.soilMoistureData.sensors[sensor.id] = [];
                    });
                    
                    // Generate time labels (mundur dari sekarang)
                    for (let i = dataPoints; i > 0; i--) {
                        const time = new Date(now - i * 60000); // Setiap menit
                        const timeLabel = time.toLocaleTimeString('id-ID', { 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        });
                        
                        // Light intensity data (simulasi pola siang hari)
                        const progress = i / dataPoints;
                        const lightBase = Math.sin(progress * Math.PI) * 10000 + 2000; // 2000-12000 lux
                        const lightIntensity1 = Math.round(lightBase + Math.random() * 500);
                        const lightIntensity2 = Math.round(lightBase * 0.95 + Math.random() * 500);
                        
                        // Water level data (simulasi level yang stabil dengan variasi kecil)
                        const waterBase = 135; // cm
                        const waterLevel = waterBase + Math.sin(progress * Math.PI * 2) * 3 + Math.random() * 2;
                        
                        // Soil Moisture data (berbagai level untuk setiap sensor)
                        this.soilMoistureSensors.forEach((sensor, idx) => {
                            const baseValue = 60 + (idx * 5); // Stagger base values 60-110
                            const smValue = baseValue + Math.sin((progress + idx * 0.2) * Math.PI) * 15 + Math.random() * 5;
                            this.soilMoistureData.sensors[sensor.id].push(parseFloat(smValue.toFixed(1)));
                        });
                        
                        // Temperature data (pola menurun seperti sore hari)
                        const tempBase1 = 33 - (progress * 8); // T1: 33 -> 25°C
                        const tempBase2 = 27 - (progress * 3); // T2: 27 -> 24°C
                        const temperature1 = tempBase1 + Math.random() * 0.5;
                        const temperature2 = tempBase2 + Math.random() * 0.5;
                        
                        // Humidity data (pola meningkat seperti sore hari)
                        const humBase1 = 35 + (progress * 13); // H1: 35 -> 48%
                        const humBase2 = 54 + (progress * 5);  // H2: 54 -> 59%
                        const humidity1 = humBase1 + Math.random() * 2;
                        const humidity2 = humBase2 + Math.random() * 2;
                        
                        // Tambahkan ke data arrays
                        this.lightIntensityData.labels.push(timeLabel);
                        this.lightIntensityData.li1.push(lightIntensity1);
                        this.lightIntensityData.li2.push(lightIntensity2);
                        
                        this.waterLevelData.labels.push(timeLabel);
                        this.waterLevelData.levels.push(parseFloat(waterLevel.toFixed(1)));
                        
                        this.soilMoistureData.labels.push(timeLabel);
                        
                        this.temperatureData.labels.push(timeLabel);
                        this.temperatureData.t1.push(parseFloat(temperature1.toFixed(1)));
                        this.temperatureData.t2.push(parseFloat(temperature2.toFixed(1)));
                        
                        this.humidityData.labels.push(timeLabel);
                        this.humidityData.h1.push(parseFloat(humidity1.toFixed(1)));
                        this.humidityData.h2.push(parseFloat(humidity2.toFixed(1)));
                    }
                    
                    console.log('Sample data generated:', {
                        lightPoints: this.lightIntensityData.labels.length,
                        waterPoints: this.waterLevelData.labels.length,
                        soilPoints: this.soilMoistureData.labels.length,
                        tempPoints: this.temperatureData.labels.length,
                        humidityPoints: this.humidityData.labels.length
                    });
                },
                initEnvironmentalCharts() {
                    // Generate sample data untuk visualisasi awal
                    this.generateSampleChartData();
                    
                    // Initialize Light Intensity Chart
                    const lightCtx = document.getElementById('lightIntensityChart');
                    if (lightCtx) {
                        this.lightIntensityChart = new Chart(lightCtx, {
                            type: 'line',
                            data: {
                                labels: this.lightIntensityData.labels,
                                datasets: [{
                                    label: 'LI2',
                                    data: this.lightIntensityData.li2,
                                    borderColor: '#22d3ee',
                                    backgroundColor: 'rgba(34, 211, 238, 0.15)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#22d3ee',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    fill: true
                                }, {
                                    label: 'LI1',
                                    data: this.lightIntensityData.li1,
                                    borderColor: '#ef4444',
                                    backgroundColor: 'rgba(239, 68, 68, 0.15)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#ef4444',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        titleColor: '#fff',
                                        bodyColor: '#fff',
                                        borderColor: '#22d3ee',
                                        borderWidth: 1
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Waktu',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {top: 10, bottom: 0}
                                        },
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db',
                                            lineWidth: 1
                                        },
                                        ticks: {
                                            color: '#374151',
                                            maxRotation: 0,
                                            autoSkip: true,
                                            maxTicksLimit: 8,
                                            font: {
                                                size: 11,
                                                weight: '500'
                                            }
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: this.t('lightIntensity') + ' (' + this.t('lux') + ')',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {bottom: 10}
                                        },
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db',
                                            lineWidth: 1
                                        },
                                        ticks: {
                                            color: '#374151',
                                            callback: function(value) {
                                                return value.toLocaleString();
                                            },
                                            font: {
                                                size: 11,
                                                weight: '500'
                                            },
                                            padding: 8
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        },
                                        min: 0
                                    }
                                },
                                interaction: {
                                    mode: 'nearest',
                                    axis: 'x',
                                    intersect: false
                                }
                            }
                        });
                    }

                    // Initialize Water Level Chart
                    const waterCtx = document.getElementById('waterLevelChart');
                    if (waterCtx) {
                        this.waterLevelChart = new Chart(waterCtx, {
                            type: 'line',
                            data: {
                                labels: this.waterLevelData.labels,
                                datasets: [{
                                    label: 'WL',
                                    data: this.waterLevelData.levels,
                                    borderColor: '#84cc16',
                                    backgroundColor: 'rgba(132, 204, 22, 0.15)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#84cc16',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        titleColor: '#fff',
                                        bodyColor: '#fff',
                                        borderColor: '#84cc16',
                                        borderWidth: 1
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Waktu',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {top: 10, bottom: 0}
                                        },
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db',
                                            lineWidth: 1
                                        },
                                        ticks: {
                                            color: '#374151',
                                            maxRotation: 0,
                                            autoSkip: true,
                                            maxTicksLimit: 8,
                                            font: {
                                                size: 11,
                                                weight: '500'
                                            }
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Ketinggian Air (cm)',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {bottom: 10}
                                        },
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db',
                                            lineWidth: 1
                                        },
                                        ticks: {
                                            color: '#374151',
                                            callback: function(value) {
                                                return value.toFixed(1);
                                            },
                                            font: {
                                                size: 11,
                                                weight: '500'
                                            },
                                            padding: 8
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        },
                                        min: 0
                                    }
                                },
                                interaction: {
                                    mode: 'nearest',
                                    axis: 'x',
                                    intersect: false
                                }
                            }
                        });
                    }

                    // Initialize Soil Moisture Chart
                    const soilCtx = document.getElementById('soilMoistureChart');
                    if (soilCtx) {
                        const datasets = this.soilMoistureSensors.map(sensor => ({
                            label: sensor.label,
                            data: this.soilMoistureData.sensors[sensor.id],
                            borderColor: sensor.color,
                            backgroundColor: sensor.color + '20',
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 2,
                            pointBackgroundColor: sensor.color,
                            fill: false
                        }));

                        this.soilMoistureChart = new Chart(soilCtx, {
                            type: 'line',
                            data: {
                                labels: this.soilMoistureData.labels,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                        titleColor: '#fff',
                                        bodyColor: '#fff'
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Waktu',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {top: 10, bottom: 0}
                                        },
                                        grid: { 
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db'
                                        },
                                        ticks: { 
                                            color: '#374151', 
                                            maxTicksLimit: 10, 
                                            font: { size: 10, weight: '500' } 
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Kelembapan Tanah (%)',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {bottom: 10}
                                        },
                                        grid: { 
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db'
                                        },
                                        ticks: { 
                                            color: '#374151', 
                                            font: { size: 10, weight: '500' } 
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        },
                                        min: 0,
                                        max: 100
                                    }
                                }
                            }
                        });
                    }

                    // Initialize Temperature Chart
                    const tempCtx = document.getElementById('temperatureChart');
                    if (tempCtx) {
                        this.temperatureChart = new Chart(tempCtx, {
                            type: 'line',
                            data: {
                                labels: this.temperatureData.labels,
                                datasets: [{
                                    label: 'T1',
                                    data: this.temperatureData.t1,
                                    borderColor: '#a855f7',
                                    backgroundColor: 'rgba(168, 85, 247, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#a855f7',
                                    fill: true
                                }, {
                                    label: 'T2',
                                    data: this.temperatureData.t2,
                                    borderColor: '#22d3ee',
                                    backgroundColor: 'rgba(34, 211, 238, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#22d3ee',
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: 'rgba(0, 0, 0, 0.9)'
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Waktu',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {top: 10, bottom: 0}
                                        },
                                        grid: { 
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db'
                                        },
                                        ticks: { 
                                            color: '#374151', 
                                            maxTicksLimit: 8, 
                                            font: { size: 10, weight: '500' } 
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Suhu (°C)',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {bottom: 10}
                                        },
                                        grid: { 
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db'
                                        },
                                        ticks: { 
                                            color: '#374151', 
                                            font: { size: 10, weight: '500' },
                                            callback: function(value) { return value + '°C'; }
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        },
                                        min: 20,
                                        max: 35
                                    }
                                }
                            }
                        });
                    }

                    // Initialize Humidity Chart
                    const humCtx = document.getElementById('humidityChart');
                    if (humCtx) {
                        this.humidityChart = new Chart(humCtx, {
                            type: 'line',
                            data: {
                                labels: this.humidityData.labels,
                                datasets: [{
                                    label: 'H2',
                                    data: this.humidityData.h2,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#3b82f6',
                                    fill: true
                                }, {
                                    label: 'H1',
                                    data: this.humidityData.h1,
                                    borderColor: '#f97316',
                                    backgroundColor: 'rgba(249, 115, 22, 0.2)',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#f97316',
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: 'rgba(0, 0, 0, 0.9)'
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Waktu',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {top: 10, bottom: 0}
                                        },
                                        grid: { 
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db'
                                        },
                                        ticks: { 
                                            color: '#374151', 
                                            maxTicksLimit: 8, 
                                            font: { size: 10, weight: '500' } 
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Kelembapan Udara (%)',
                                            color: '#374151',
                                            font: {
                                                size: 12,
                                                weight: '600'
                                            },
                                            padding: {bottom: 10}
                                        },
                                        grid: { 
                                            color: 'rgba(0, 0, 0, 0.1)',
                                            drawBorder: true,
                                            borderColor: '#d1d5db'
                                        },
                                        ticks: { 
                                            color: '#374151', 
                                            font: { size: 10, weight: '500' },
                                            callback: function(value) { return value + '%'; }
                                        },
                                        border: {
                                            display: true,
                                            color: '#9ca3af'
                                        },
                                        min: 30,
                                        max: 65
                                    }
                                }
                            }
                        });
                    }
                },
                updateEnvironmentalCharts() {
                    const now = new Date();
                    const timeLabel = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                    // Get light intensity from multiple sources
                    const devices = this.devices || [];
                    let hasLightData = false;
                    let li1 = null;
                    let li2 = null;
                    
                    // Priority 1: Try to get from devices
                    if (devices.length > 0) {
                        // Try to get from first device
                        if (devices[0]?.light_lux != null && devices[0].light_lux > 0) {
                            li1 = devices[0].light_lux;
                            hasLightData = true;
                        }
                        
                        // Try to get from second device if exists
                        if (devices.length > 1 && devices[1]?.light_lux != null && devices[1].light_lux > 0) {
                            li2 = devices[1].light_lux;
                            hasLightData = true;
                        }
                    }
                    
                    // Priority 2: If no device data, use BMKG weather data (light_pct)
                    if (!hasLightData && this.weatherSummary?.light_pct != null) {
                        // Convert light percentage to lux equivalent (estimate)
                        // Assuming max daylight = 12000 lux
                        const lightPct = parseFloat(this.weatherSummary.light_pct);
                        li1 = (lightPct / 100) * 12000;
                        li2 = li1 * 0.92; // Slight variation for visualization
                        hasLightData = true;
                    }
                    
                    // Fallback: Create slight variation if only one value exists
                    if (hasLightData) {
                        if (li1 != null && li2 == null) {
                            li2 = li1 * 0.95;
                        } else if (li2 != null && li1 == null) {
                            li1 = li2 * 1.05;
                        }
                        
                        // Update chart data
                        this.lightIntensityData.labels.push(timeLabel);
                        this.lightIntensityData.li1.push(li1);
                        this.lightIntensityData.li2.push(li2);

                        // Keep only last N points
                        if (this.lightIntensityData.labels.length > this.chartMaxPoints) {
                            this.lightIntensityData.labels.shift();
                            this.lightIntensityData.li1.shift();
                            this.lightIntensityData.li2.shift();
                        }

                        // Update chart
                        if (this.lightIntensityChart) {
                            this.lightIntensityChart.update('none');
                        }
                    }

                    // Get water level from tank or water height
                    let waterLevel = null;
                    
                    // Try multiple sources for water level
                    if (this.tank?.water_level_cm != null && this.tank.water_level_cm > 0) {
                        waterLevel = this.tank.water_level_cm;
                    } else if (devices.length > 0 && devices[0]?.water_height_cm != null && devices[0].water_height_cm > 0) {
                        waterLevel = devices[0].water_height_cm;
                    } else if (this.tank?.percentage != null && this.tank.percentage > 0) {
                        waterLevel = (this.tank.percentage / 100 * 150); // Convert percentage to cm
                    }
                    
                    // Only update if we have valid water level data
                    if (waterLevel != null && waterLevel > 0) {
                        this.waterLevelData.labels.push(timeLabel);
                        this.waterLevelData.levels.push(waterLevel);

                        // Keep only last N points
                        if (this.waterLevelData.labels.length > this.chartMaxPoints) {
                            this.waterLevelData.labels.shift();
                            this.waterLevelData.levels.shift();
                        }

                        // Update chart
                        if (this.waterLevelChart) {
                            this.waterLevelChart.update('none');
                        }
                    }
                    
                    // Debug log to check data
                    console.log('Chart Update:', {
                        time: timeLabel,
                        lightData: hasLightData,
                        lightSource: li1 != null ? (devices[0]?.light_lux ? 'device' : 'bmkg') : 'none',
                        li1: li1,
                        li2: li2,
                        li1Count: this.lightIntensityData.li1.length,
                        waterLevel: waterLevel,
                        wlCount: this.waterLevelData.levels.length,
                        devices: devices.length,
                        weatherLight: this.weatherSummary?.light_pct,
                        tankPercent: this.tank?.percentage
                    });
                },
                clearChart(type) {
                    if (type === 'light') {
                        this.lightIntensityData.labels = [];
                        this.lightIntensityData.li1 = [];
                        this.lightIntensityData.li2 = [];
                        if (this.lightIntensityChart) {
                            this.lightIntensityChart.update();
                        }
                    } else if (type === 'water') {
                        this.waterLevelData.labels = [];
                        this.waterLevelData.levels = [];
                        if (this.waterLevelChart) {
                            this.waterLevelChart.update();
                        }
                    } else if (type === 'soilMoisture') {
                        this.soilMoistureData.labels = [];
                        this.soilMoistureSensors.forEach(sensor => {
                            this.soilMoistureData.sensors[sensor.id] = [];
                        });
                        if (this.soilMoistureChart) {
                            this.soilMoistureChart.update();
                        }
                    } else if (type === 'temperature') {
                        this.temperatureData.labels = [];
                        this.temperatureData.t1 = [];
                        this.temperatureData.t2 = [];
                        if (this.temperatureChart) {
                            this.temperatureChart.update();
                        }
                    } else if (type === 'humidity') {
                        this.humidityData.labels = [];
                        this.humidityData.h1 = [];
                        this.humidityData.h2 = [];
                        if (this.humidityChart) {
                            this.humidityChart.update();
                        }
                    }
                },
                refreshMetricTooltips() {
                    // Attach title attribute dynamically to overlay chips (executed after DOM paint)
                    this.$nextTick(() => {
                        document.querySelectorAll('[data-metric-chip]').forEach(el => {
                            const metricKey = el.getAttribute('data-metric-chip');
                            const snap = this.metricSnapshots[metricKey];
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
                    this.fetchError = false;
                    try {
                        const response = await fetch('/api/sensor-readings/latest-per-device');
                        const jsonData = await response.json();
                        if (!response.ok) throw new Error(jsonData.message || 'err');
                        this.devices = (jsonData.data || []).map(device => ({
                            id: device.device_id,
                            device_id: device.device_id,
                            device_name: device.device_name || device.device_id,
                            // Sensor data
                            temperature_c: device.temperature_c ?? device.ground_temperature_c ?? device.temperature,
                            ground_temperature_c: device.ground_temperature_c ?? device.temperature_c,
                            soil_moisture_pct: device.soil_moisture_pct ?? device.soil_moisture,
                            battery_voltage_v: device.battery_voltage_v,
                            light_lux: device.light_lux,
                            water_height_cm: device.water_height_cm,
                            irrigation_usage_total_l: device.irrigation_usage_total_l,
                            ina226_power_mw: device.ina226_power_mw,
                            // Device status
                            connection_state: device.connection_state || 'offline',
                            valve_state: device.valve_state || 'closed',
                            is_active: device.is_active,
                            location: device.location || '',
                            // Metadata
                            recorded_at: device.recorded_at,
                            status: device.status || 'normal',
                            water_usage_today_l: device.water_usage_today_l ? parseFloat(device.water_usage_today_l) : null
                        }));
                        this.computeTopMetrics();
                    } catch (error) {
                        console.error('Device fetch error', error);
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
                        const response = await fetch('/api/water-storage');
                        const jsonData = await response.json();
                        if (!response.ok) throw new Error();
                        const tankData = (jsonData.data || [])[0];
                        if (tankData) {
                            this.tank = {
                                id: tankData.id,
                                tank_name: tankData.tank_name || tankData.name || 'Tangki Air',
                                current_volume_liters: parseFloat(tankData.current_volume_liters || tankData.current_volume || 0),
                                capacity_liters: parseFloat(tankData.capacity_liters || tankData.total_capacity || 0),
                                percentage: parseFloat(tankData.percentage || 0),
                                status: tankData.status || 'normal'
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
                        const response = await fetch('/api/irrigation/today-plan');
                        const jsonData = await response.json();
                        if (!response.ok) throw new Error();
                        if (jsonData.data) {
                            this.plan = jsonData.data;
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
                        const response = await fetch('/api/water-storage/daily-usage');
                        const jsonData = await response.json();
                        if (!response.ok) throw new Error();
                        // ✅ Convert to plain array BEFORE assigning to Alpine
                        const rawData = jsonData.data || [];
                        this.usage = rawData.map(item => ({
                            date: item.date || item.usage_date,
                            usage_date: item.usage_date || item.date,
                            total_l: parseFloat(item.total_l) || 0
                        }));
                        this.renderUsageChart30d();
                    } catch (error) {
                        console.error('Usage fetch error', error);
                        // Fallback with mock data for demo
                        this.usage = this.generateMock30dData();
                        this.renderUsageChart30d();
                    }
                },
                async loadUsageDaily() {
                    try {
                        const response = await fetch('/api/water-storage/hourly-usage');
                        const jsonData = await response.json();
                        if (!response.ok) throw new Error();
                        // ✅ Convert to plain array BEFORE assigning to Alpine
                        const rawData = jsonData.data || [];
                        this.usage24h = rawData.map(item => ({
                            hour: item.hour,
                            total_l: parseFloat(item.total_l) || 0,
                            datetime: item.datetime
                        }));
                        this.renderUsageChart24h();
                    } catch (error) {
                        console.error('24h Usage fetch error', error);
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
                    
                    try {
                        await Promise.all([
                            this.loadDevices(),
                            this.loadTank(),
                            this.loadPlan(),
                            this.loadUsage(),
                            this.loadUsageDaily()
                        ]);
                        
                        // After core data loaded, derive light & wind and fetch weather
                        this.computeLightWindFromDevices();
                        this.loadEnvStats();
                        this.lastUpdated = new Date();
                        this.computeTopMetrics();
                        this.updateEnvironmentalCharts();
                    } catch (error) {
                        console.error('Load all error:', error);
                        this.fetchError = true;
                    } finally {
                        this.loadingAll = false;
                    }
                },
                computeLightWindFromDevices() {
                    if (!this.devices.length) return;
                    const luxValues = this.devices.map(device => device.light_lux).filter(value => value != null);
                    const windValues = this.devices.map(device => device.wind_speed_ms).filter(value => value != null);
                    if (luxValues.length) {
                        const avgLux = Math.round(luxValues.reduce((accumulator, current) => accumulator + current, 0) / luxValues.length);
                        this.updateMetric('light', avgLux, `avg ${luxValues.length}`);
                    }
                    if (windValues.length) {
                        const maxWind = Math.max(...windValues);
                        this.updateMetric('wind', (Math.round(maxWind * 10) / 10), 'max');
                    }
                },
                loadEnvStats() {
                    // Attempt backend proxy (recommended to implement) else fallback direct
                    fetch('/api/bmkg/forecast')
                        .then(response => response.ok ? response.json() : Promise.reject())
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
                                .then(response => response.json())
                                .then(raw => {
                                    try {
                                        const blocks = raw?.data?.[0]?.cuaca;
                                        if (Array.isArray(blocks)) {
                                            const flat = [];
                                            blocks.forEach(block => Array.isArray(block) && block.forEach(entry => flat.push(entry)));
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
                    this.forecastEntries = list.map(entry => ({
                        local_datetime: entry.local_datetime || entry.datetime || null,
                        temp: entry.t ?? entry.temperature_c,
                        humidity: entry.humidity ?? entry.hu ?? entry.h,
                        rain: entry.rain ?? entry.tp ?? null,
                        label: this.translateWeather(entry.weather_desc || entry.weather_desc_en || entry.weather_desc_id ||
                            entry.weather),
                        icon: entry.weather_icon || entry.image || null,
                        wind_speed: entry.wind_speed_ms ?? entry.ws ?? null,
                        wind_dir: entry.wind_dir_cardinal || entry.wd || null,
                        tcc: entry.tcc ?? null
                    })).filter(entry => entry.local_datetime);
                    // 24h slice
                    const now = Date.now();
                    this.forecast24h = this.forecastEntries.filter(entry => new Date(entry.local_datetime) - now < 24 * 3600 * 1000)
                        .slice(0, 12).map(entry => ({
                            ...entry,
                            hour: new Date(entry.local_datetime).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            })
                        }));
                    // Weekly group (by date)
                    const map = {};
                    this.forecastEntries.forEach(entry => {
                        const date = new Date(entry.local_datetime);
                        const key = date.toISOString().substring(0, 10);
                        if (!map[key]) map[key] = {
                            temps: [],
                            rains: [],
                            icons: [],
                            labels: [],
                            date: key
                        };
                        map[key].temps.push(entry.temp);
                        if (entry.rain != null) map[key].rains.push(entry.rain);
                        if (entry.icon) map[key].icons.push(entry.icon);
                        if (entry.label) map[key].labels.push(entry.label);
                    });
                    this.forecastWeekly = Object.values(map).slice(0, 7).map(group => {
                        const dateTime = new Date(group.date + 'T00:00:00');
                        return {
                            date: group.date,
                            day: dateTime.toLocaleDateString('id-ID', {
                                weekday: 'long'
                            }),
                            min: Math.min(...group.temps),
                            max: Math.max(...group.temps),
                            rain: group.rains.length ? (Math.round((group.rains.reduce((accumulator, current) => accumulator + current, 0)) * 10) / 10) :
                                null,
                            icon: group.icons[0] || null,
                            label: group.labels[0] || ''
                        };
                    });
                    // Build summary for today
                    if (this.forecastEntries.length) {
                        const today = new Date().toISOString().substring(0, 10);
                        const todayEntries = this.forecastEntries.filter(e => e.local_datetime.startsWith(today));
                        const temps = todayEntries.map(e => e.temp).filter(v => v != null);
                        const first = this.forecastEntries[0];
                        
                        // Calculate light_pct with fallback
                        let lightPct = null;
                        if (first?.tcc != null) {
                            lightPct = Math.max(0, Math.min(100, 100 - first.tcc));
                        } else {
                            // Time-based fallback
                            const hour = new Date().getHours();
                            if (hour >= 6 && hour <= 18) {
                                const progress = (hour - 6) / 12;
                                lightPct = Math.sin(progress * Math.PI) * 75 + 25;
                            } else {
                                lightPct = 5;
                            }
                        }
                        
                        this.weatherSummary = {
                            temp: first?.temp ?? '-',
                            label: first?.label || '-',
                            humidity: first?.humidity ?? '-',
                            wind_speed: first?.wind_speed ?? '-',
                            wind_dir: first?.wind_dir ?? '',
                            rain: first?.rain ?? 0,
                            light_pct: lightPct,
                            tcc: first?.tcc,
                            icon: first?.icon || null,
                            time: first?.local_datetime ? new Date(first.local_datetime).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : '',
                            temp_min: temps.length ? Math.min(...temps) : null,
                            temp_max: temps.length ? Math.max(...temps) : null
                        };
                        
                        console.log('Weather summary built:', {
                            temp: this.weatherSummary.temp,
                            rain: this.weatherSummary.rain,
                            light_pct: this.weatherSummary.light_pct
                        });
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
                        const date = new Date(year, month, dayNum);
                        const isCurrentMonth = dayNum >= 1 && dayNum <= daysInMonth;
                        const iso = date.toISOString().substring(0, 10);
                        const fEntries = this.forecastEntries.filter(entry => entry.local_datetime.startsWith(iso));
                        const temps = fEntries.map(entry => entry.temp).filter(value => value != null);
                        const rainValues = fEntries.map(entry => entry.rain).filter(value => value != null);
                        const rainSum = rainValues.length ? Math.round(rainValues.reduce((accumulator, current) => accumulator + current, 0) * 10) / 10 : null;
                        const icon = fEntries.find(entry => entry.icon)?.icon || null;
                        const label = fEntries.find(entry => entry.label)?.label || '';
                        const usageForDay = this.usage.find(usage => usage.date === iso || usage.day === iso);
                        result.push({
                            key: iso,
                            date: iso,
                            day: date.getDate(),
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
                        const date = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + i);
                        const iso = date.toISOString().substring(0, 10);
                        const fEntries = this.forecastEntries.filter(entry => entry.local_datetime.startsWith(iso));
                        let avgTemp = '-';
                        let forecastIcon = null;
                        let forecastLabel = '';
                        let category = 'idle';
                        let style = this.categoryStyles['idle'];
                        if (fEntries.length) {
                            const temps = fEntries.map(entry => entry.temp).filter(value => value != null);
                            if (temps.length) avgTemp = Math.round(temps.reduce((accumulator, current) => accumulator + current, 0) / temps.length);
                            // Pilih entri mendekati tengah hari (12:00) sebagai ikon; fallback 11/13; lalu pertama.
                            const midday = fEntries.find(entry => /T12:00:00/.test(entry.local_datetime)) || fEntries.find(entry =>
                                /T11:00:00|T13:00:00/.test(entry.local_datetime)) || fEntries[0];
                            forecastIcon = midday?.icon || midday?.weather_icon || null;
                            forecastLabel = midday?.label || this.translateWeather(midday?.weather_desc) || '';
                            // Hitung curah hujan total untuk kategorisasi.
                            const rainValues = fEntries.map(entry => entry.rain).filter(value => value != null);
                            const rainSum = rainValues.length ? rainValues.reduce((accumulator, current) => accumulator + current, 0) : 0;
                            const cfg = this.categoryConfig;
                            if (rainSum >= (cfg.shipment?.minRain ?? 5)) category = 'ship';
                            else if (avgTemp !== '-' && rainSum <= (cfg.fertilization?.maxRain ?? 2) && avgTemp >= (cfg
                                    .fertilization?.minTemp ?? 30)) category = 'fert';
                            else if (avgTemp !== '-' && rainSum <= (cfg.plowing?.maxRain ?? 2) && avgTemp < (cfg.plowing
                                    ?.maxTemp ?? 30)) category = 'plowing';
                            style = this.categoryStyles[category] || this.categoryStyles['idle'];
                        }
                        const dayObject = {
                            date: iso,
                            day: date.getDate(),
                            temp: avgTemp === '-' ? '-' : avgTemp + '°',
                            weekdayShort: date.toLocaleDateString('id-ID', {
                                weekday: 'short'
                            }),
                            category,
                            categoryBg: style.bg,
                            icon: forecastIcon, // only show real BMKG icon if available
                            label: forecastLabel || (avgTemp === '-' ? '' : forecastLabel),
                            active: false
                        };
                        const todayIso = new Date().toISOString().substring(0, 10);
                        if (iso === todayIso) dayObject.active = true;
                        days.push(dayObject);
                    }
                    // Fallback untuk hari minggu ini yang tidak punya data BMKG: gunakan hari terdekat yang punya data
                    // (utamakan mundur ke belakang, jika tidak ada ambil yang di depan). Tandai dengan estimated flag.
                    const todayIso = new Date().toISOString().substring(0, 10);
                    for (let i = 0; i < days.length; i++) {
                        const currentDay = days[i];
                        if (currentDay.temp === '-' && currentDay.date <= todayIso) {
                            let sourceDay = null;
                            for (let backwardIndex = i - 1; backwardIndex >= 0; backwardIndex--) {
                                if (days[backwardIndex].temp !== '-') {
                                    sourceDay = days[backwardIndex];
                                    break;
                                }
                            }
                            if (!sourceDay) {
                                for (let forwardIndex = i + 1; forwardIndex < days.length; forwardIndex++) {
                                    if (days[forwardIndex].temp !== '-') {
                                        sourceDay = days[forwardIndex];
                                        break;
                                    }
                                }
                            }
                            if (!sourceDay && this.weatherSummary && this.weatherSummary.temp) {
                                sourceDay = {
                                    temp: Math.round(this.weatherSummary.temp) + '°',
                                    icon: this.weatherSummary.icon,
                                    label: this.weatherSummary.label,
                                    category: 'idle',
                                    categoryBg: this.categoryStyles['idle'].bg
                                };
                            }
                            if (sourceDay) {
                                currentDay.temp = sourceDay.temp;
                                currentDay.icon = currentDay.icon || sourceDay.icon;
                                currentDay.label = currentDay.label || sourceDay.label;
                                currentDay.categoryBg = currentDay.categoryBg == this.categoryStyles['idle'].bg ? sourceDay.categoryBg || currentDay
                                    .categoryBg : currentDay.categoryBg;
                                currentDay.estimated = true;
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
                    const weatherCode = (code || '').toString().toLowerCase();
                    if (weatherCode.includes('cerah') || weatherCode.includes('sun')) return 'Cerah';
                    if (weatherCode.includes('berawan') || weatherCode.includes('cloud')) return 'Berawan';
                    if (weatherCode.includes('mendung') || weatherCode.includes('overcast')) return 'Mendung';
                    if (weatherCode.includes('hujan') || weatherCode.includes('rain')) return 'Hujan';
                    if (weatherCode.includes('malam') || weatherCode.includes('night')) return 'Malam';
                    return code || '-';
                },

                renderUsageChart24h() {
                    const el = document.getElementById('usageChart24h');
                    if (!el) return;
                    
                    try {
                        // ✅ Destroy existing chart first to prevent memory leaks and loops
                        if (this.usageChart24h) {
                            this.usageChart24h.destroy();
                            this.usageChart24h = null;
                        }
                        
                        // ✅ Convert to plain arrays (deep clone to avoid Proxy)
                        const plainData = JSON.parse(JSON.stringify(this.usage24h || []));
                        const labels = plainData.map(r => (r.hour || '00') + ':00');
                        const data = plainData.map(r => parseFloat(r.total_l) || 0);
                        
                        if (!labels.length || !data.length) {
                            console.log('No 24h data to render');
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
                            labels: labels,
                            datasets: [{
                                label: 'Liter/Jam',
                                data: data,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.2)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false, // Disable animation to prevent loops
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
                    } catch (error) {
                        console.error('Chart 24h render error:', error);
                        // ✅ Destroy broken chart instance
                        if (this.usageChart24h) {
                            this.usageChart24h.destroy();
                            this.usageChart24h = null;
                        }
                    }
                },
                renderUsageChart30d() {
                    const el = document.getElementById('usageChart30d');
                    if (!el) {
                        console.log('Element usageChart30d not found');
                        return;
                    }
                    
                    try {
                        console.log('30d data:', this.usage);
                        
                        // ✅ Destroy existing chart first to prevent memory leaks and loops
                        if (this.usageChart) {
                            this.usageChart.destroy();
                            this.usageChart = null;
                        }
                        
                        // ✅ Convert to plain arrays (deep clone to avoid Proxy)
                        const plainData = JSON.parse(JSON.stringify(this.usage || []));
                        const labels = plainData.map(r => {
                            const date = new Date(r.date || r.usage_date);
                            if (isNaN(date)) return r.date || r.usage_date;
                            const day = String(date.getDate()).padStart(2, '0');
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const year = date.getFullYear();
                            return `${day}-${month}-${year}`;
                        });
                        const data = plainData.map(r => parseFloat(r.total_l) || 0);
                        console.log('30d labels:', labels, 'data:', data);
                        
                        if (!labels.length || !data.length) {
                            console.log('No 30d data to render');
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
                            labels: labels,
                            datasets: [{
                                label: 'Liter',
                                data: data,
                                tension: .3,
                                fill: true,
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22,163,74,0.15)',
                                pointRadius: 2,
                                pointBackgroundColor: '#16a34a',
                                pointBorderColor: '#16a34a'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 0 // Completely disable animations to prevent circular refs
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 8,
                                    displayColors: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        display: true,
                                        color: 'rgba(0,0,0,0.1)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        color: '#666'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#666',
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            }
                        },
                        plugins: [watermark]
                    });
                    } catch (error) {
                        console.error('Chart 30d render error:', error);
                        // ✅ Destroy broken chart instance
                        if (this.usageChart) {
                            this.usageChart.destroy();
                            this.usageChart = null;
                        }
                    }
                },
                totalUsage() {
                    if (!this.usage || !this.usage.length) return '0.0';
                    return this.usage.reduce((accumulator, item) => accumulator + (parseFloat(item.total_l) || 0), 0).toFixed(1);
                },
                totalUsage24h() {
                    if (!this.usage24h || !this.usage24h.length) return '0.0';
                    return this.usage24h.reduce((accumulator, item) => accumulator + (parseFloat(item.total_l) || 0), 0).toFixed(1);
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
                fmt(value, suffix = '') {
                    if (value == null) return '-';
                    const number = parseFloat(value);
                    return isNaN(number) ? '-' : number.toFixed(1) + suffix;
                },
                batteryDisplay(device) {
                    if (!device || device.battery_voltage_v == null || device.battery_voltage_v === undefined) return '-';
                    const voltage = parseFloat(device.battery_voltage_v);
                    if (isNaN(voltage) || voltage <= 0) return '-';
                    // Li-Ion 1S: 3.3V (0%) - 4.2V (100%)
                    const percentage = Math.max(0, Math.min(100, ((voltage - 3.3) / (4.2 - 3.3)) * 100));
                    return voltage.toFixed(2) + 'V (' + percentage.toFixed(0) + '%)';
                },
                batteryDisplayShort(device) {
                    if (!device || device.battery_voltage_v == null || device.battery_voltage_v === undefined) return '-';
                    const voltage = parseFloat(device.battery_voltage_v);
                    if (isNaN(voltage) || voltage <= 0) return '-';
                    // Li-Ion 1S: 3.3V (0%) - 4.2V (100%)
                    const percentage = Math.max(0, Math.min(100, ((voltage - 3.3) / (4.2 - 3.3)) * 100));
                    return percentage.toFixed(0) + '%';
                },
                tankFillColor() {
                    const percentage = this.tank?.percentage || 0;
                    if (percentage < 25) return '#dc2626';
                    if (percentage < 50) return '#f59e0b';
                    if (percentage < 75) return '#3b82f6';
                    return '#16a34a';
                },
                tankFillStyle() {
                    const color = this.tankFillColor();
                    return `background: linear-gradient(180deg, ${col}cc 0%, ${col}ee 60%, ${col} 100%); box-shadow: inset 0 2px 4px rgba(0,0,0,0.25);`;
                },
                tankStatusClass() {
                    const status = (this.tank?.status || '').toLowerCase();
                    if (status.includes('krit') || status === 'low') return 'text-red-600';
                    if (status.includes('warning') || status.includes('wasp')) return 'text-amber-600';
                    return 'text-green-600';
                },
                tankLabelClass() {
                    const percentage = this.tank?.percentage || 0;
                    if (percentage < 25) return 'bg-red-600/70 text-white';
                    if (percentage < 50) return 'bg-amber-500/70 text-white';
                    if (percentage < 75) return 'bg-blue-600/70 text-white';
                    return 'bg-green-600/70 text-white';
                },
                deviceUsageToday(deviceId) {
                    const device = this.devices.find(dev => dev.device_id === deviceId || dev.id === deviceId);
                    if (!device || device.water_usage_today_l == null) return '-';
                    return device.water_usage_today_l.toFixed(0) + 'L';
                },
                timeAgo(timestamp) {
                    if (!timestamp) return '-';
                    const date = new Date(timestamp);
                    const diff = (Date.now() - date) / 60000;
                    if (diff < 1) return 'baru';
                    if (diff < 60) return Math.round(diff) + 'm';
                    const hours = diff / 60;
                    if (hours < 24) return hours.toFixed(1) + 'j';
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    });
                },
                deviceBadgeClass() {
                    return 'bg-gray-100 text-gray-600';
                },
                statusShort(statusText) {
                    return statusText?.substring(0, 6) || 'ok';
                },
                sessionColor(sessionStatus) {
                    return sessionStatus === 'completed' ? 'text-green-600' : sessionStatus === 'pending' ? 'text-gray-500' : 'text-yellow-600';
                },
                init() {
                    // Initialize charts first
                    setTimeout(() => {
                        this.initEnvironmentalCharts();
                    }, 500);
                    
                    // Initialize leaflet
                    setTimeout(() => this.initLeaflet(), 800);
                    
                    // Load data after charts ready
                    setTimeout(() => {
                        this.loadAll();
                    }, 1200);
                    
                    // Auto refresh setiap 60 detik - hanya jika tidak sedang loading
                    setInterval(() => {
                        if (!this.loadingAll) {
                            this.loadAll();
                        }
                    }, 60000);
                    
                    // Clock tick
                    this.tickClock();
                    setInterval(() => this.tickClock(), 1000);
                },
                tickClock() {
                    const now = new Date();
                    const pad = number => number.toString().padStart(2, '0');
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
