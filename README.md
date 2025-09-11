# 🌱 Sistem Irigasi Cerdas

## 📋 Tentang Proyek

Sistem manajemen irigasi cerdas berbasis Laravel untuk monitoring dan kontrol otomatis sistem penyiraman tanaman dengan sensor IoT dan dashboard real-time.

## 🚀 Fitur Utama

- **Dashboard Real-time** - Tampilan web interaktif untuk monitoring
- **Sensor Monitoring** - Suhu, kelembaban tanah, ketinggian air, dan cahaya
- **Manajemen Tangki Air** - Monitoring volume dan status tangki
- **Rencana Irigasi** - Penjadwalan otomatis penyiraman harian
- **API REST** - Endpoint lengkap untuk integrasi IoT dan mobile
- **8 Node Sensor** - Monitoring detail per perangkat IoT

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

## 🖥️ Dashboard Web

Akses dashboard di: `http://127.0.0.1:8000`

Fitur dashboard:
- **Monitoring Real-time** - Data sensor dari 8 node
- **Status Tangki** - Level air dan kapasitas
- **Rencana Irigasi** - Jadwal penyiraman harian
- **Grafik Penggunaan** - Riwayat penggunaan air

## 🛠️ Komponen Utama

### Model
- **Device** - Manajemen perangkat IoT
- **SensorData** - Data pembacaan sensor
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
- [x] Database dan seeder data
- [x] 8 node sensor aktif

### 🔄 Dalam Pengembangan
- [ ] Integrasi aplikasi mobile
- [ ] Sistem penjadwalan otomatis
- [ ] Integrasi cuaca
- [ ] Notifikasi alert

---

## 🔧 Teknologi

- **Framework**: Laravel 11.x
- **Database**: MySQL
- **Frontend**: Alpine.js + Blade
- **PHP**: 8.1+

## 📝 Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
