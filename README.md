# 🌱 Sistem Irigasi Cerdas

## 📋 Tentang Proyek

Sistem manajemen irigasi cerdas berbasis Laravel untuk monitoring dan kontrol otomatis sistem penyiraman tanaman dengan sensor IoT dan dashboard real-time.

## 🚀 Fitur Utama

- **Dashboard Real-time** - Tampilan web interaktif untuk monitoring
- **Sensor Per-Device (Simplified)** - Suhu tanah, kelembapan tanah, total penggunaan irigasi, tegangan baterai, & daya (INA226 optional)
- **Manajemen Tangki Air** - Monitoring kapasitas & perhitungan penggunaan
- **Rencana Irigasi** - Penjadwalan otomatis penyiraman harian
- **API REST** - Endpoint lengkap untuk integrasi IoT dan mobile
- **12 Node Sensor** - Monitoring detail per perangkat IoT
- **Power Monitoring** - Konsumsi daya (mW) untuk node dengan INA226

## 🏗️ Struktur Proyek

```
SmartIrigationSystem/
├── app/Http/Controllers/Api/   # Controller API
├── app/Models/                 # Model Eloquent
├── database/migrations/        # Skema database
├── database/seeders/           # Data awal
├── resources/views/            # Tampilan dashboard
└── routes/api.php              # Route API
```

## 🔧 Instalasi

### Kebutuhan Sistem
- PHP 8.1+
- Composer
- MySQL/MariaDB

### Cara Instalasi
```bash
# Clone repository
git clone https://github.com/RifkiFiransah/SmartIrigationSystem.git
cd SmartIrigationSystem

# Install dependency
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Jalankan server
php artisan serve
```

Buka browser dan akses: `http://127.0.0.1:8000`

## 🌐 API Endpoints

### Sensor Data
- `GET /api/sensor-readings/latest-per-device` - Data sensor terbaru per device
- `GET /api/sensor-readings/hourly` - Data sensor per jam
- `GET /api/sensor-readings/daily` - Data sensor harian

### Tangki Air
- `GET /api/water-storage` - Status tangki air
- `GET /api/water-storage/daily-usage` - Penggunaan air harian

### Rencana Irigasi
- `GET /api/irrigation/today-plan` - Rencana irigasi hari ini

### Cuaca Eksternal
- `GET /api/weather/external?lat={lat}&lon={lon}` - Data cuaca ringkas eksternal (proxy BMKG/Open-Meteo) termasuk suhu, kelembapan relatif, kondisi langit, estimasi lux & kecepatan angin (jika tersedia)
- `GET /api/weather/hourly?lat={lat}&lon={lon}&hours=24` - Deret waktu hingga 24 jam (temperature, humidity, wind_speed, estimated lux) untuk grafik analitik

#### Konfigurasi Provider Cuaca
Layanan cuaca mendukung beberapa provider dengan fallback bertingkat:

Prioritas: (1) BMKG jika dipilih -> (2) Open-Meteo -> (3) Synthetic (dummy)

Tambahkan pada `.env` untuk menggunakan BMKG:
```
WEATHER_PROVIDER=bmkg
BMKG_FORECAST_URL="https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=32.08.10.2001"
```

Default tanpa konfigurasi akan menggunakan `open-meteo` lalu fallback ke synthetic jika gagal.

Override provider per-request (untuk debugging):
```
/api/weather/external?provider=bmkg
/api/weather/hourly?hours=24&provider=open-meteo
```

Lux (intensitas cahaya) saat ini adalah estimasi berbasis kondisi & waktu karena API BMKG publik belum menyediakan langsung nilai irradiance/lux. Jika nanti tersedia parameter radiasi global, mudah ditambahkan pemetaan langsung.

## 🖥️ Dashboard Web

Akses dashboard di: `http://127.0.0.1:8000`

Fitur dashboard:
- **Monitoring Real-time** - Data sensor dari 12 node
- **Status Tangki** - Level air dan kapasitas
- **Rencana Irigasi** - Jadwal penyiraman harian
- **Grafik Penggunaan** - Riwayat penggunaan air
- **Grafik Tegangan Baterai** - Tren 24 jam voltage supply per node
- **Grafik Total & Delta Irigasi** - Garis kumulatif + batang delta per interval
- **Grafik Lingkungan Eksternal** - 24 jam angin & estimasi lux dari layanan cuaca

## 🛠️ Komponen Utama

### Model
- **Device** - Manajemen perangkat IoT
- **SensorData** - Data pembacaan sensor per-perangkat (ground_temperature_c, soil_moisture_pct, irrigation_usage_total_l, battery_voltage_v, ina226_power_mw)
- **WaterStorage** - Manajemen tangki air
- **IrrigationDailyPlan** - Rencana irigasi harian

### Controller API
- **SensorDataController** - API data sensor
- **WaterStorageController** - API tangki air
- **IrrigationPlanController** - API rencana irigasi

## 📊 Status Pengembangan

### ✅ Selesai
- [x] Dashboard monitoring real-time
- [x] API sensor data lengkap
- [x] Manajemen tangki air
- [x] Rencana irigasi otomatis
- [x] Database & seeder data
- [x] 12 node sensor aktif
- [x] Refactor skema sensor (hapus light & angin per-device)
- [x] Simplifikasi INA226 (hanya power)
- [x] Endpoint cuaca eksternal dasar (`/api/weather/external`)
- [x] Widget grafik tegangan baterai
- [x] Widget grafik penggunaan irigasi (cumulative + delta)
- [x] Endpoint hourly cuaca eksternal (`/api/weather/hourly`)
- [x] Widget grafik lingkungan eksternal (wind + lux)
- [x] Konfigurasi multi-provider cuaca (BMKG/Open-Meteo + fallback synthetic)

### 🔄 Dalam Pengembangan
- [ ] Integrasi aplikasi mobile
- [ ] Sistem penjadwalan otomatis lanjutan
- [ ] Integrasi cuaca eksternal lanjutan (widget dashboard + enrichment keputusan irigasi)
- [ ] Notifikasi alert
- [ ] Optimasi penyimpanan data sensor (rollup agregasi)

## 🗒️ Catatan Desain Skema (2025-09)

Refactor skema dilakukan untuk menyederhanakan tabel `sensor_data` agar hanya menyimpan metrik yang benar-benar dikirim langsung oleh node:

| Metrik | Kolom | Keterangan |
|-------|-------|-----------|
| Suhu Tanah | ground_temperature_c | Sensor suhu permukaan / probe tanah |
| Kelembapan Tanah | soil_moisture_pct | Persentase kelembapan (% volumetric) |
| Total Irigasi | irrigation_usage_total_l | Akumulasi liter yang telah digunakan node |
| Tegangan Baterai | battery_voltage_v | Tegangan supply (jika battery powered) |
| Daya | ina226_power_mw | Optional, hanya untuk node dengan INA226 |

Metrik lingkungan (intensitas cahaya, kecepatan angin) sekarang direncanakan diambil dari layanan cuaca eksternal dan tidak lagi disimpan per-device untuk mengurangi redundansi & noise.

---

## 🔧 Teknologi

- **Framework**: Laravel 11.x
- **Database**: MySQL
- **Frontend**: Alpine.js + Blade
- **PHP**: 8.1+

## 📝 Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
