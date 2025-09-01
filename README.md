# 🌱 Smart Irrigation System - Laravel Backend

## 📋 Project Overview

Laravel backend system for Smart Irrigation Management with area-based irrigation line control, real-time monitoring, and automated irrigation controls.

## 🚀 Features

### ✅ Core Features
- **Area-based Irrigation Management** - Multiple irrigation lines per area
- **Smart Analytics** - Efficiency scoring and water consumption optimization  
- **Real-time Dashboard** - Interactive web interface with Alpine.js
- **REST API** - Complete API endpoints for mobile/IoT integration
- **Node Tracking** - Detailed sensor monitoring per irrigation line
- **Multiple Irrigation Types** - Drip, NFT, Misting, Sprinkler systems

### 📊 System Metrics
- **3 Areas**: Blok Tomat Hidroponik, Blok Sayuran Berdaun, Backup Area
- **24 Irrigation Lines**: Various types with efficiency tracking
- **1,270+ Plants** managed across all areas
- **1,150 m²** total coverage area
- **Real-time efficiency analytics** with optimization recommendations

## 🏗️ Project Structure

This Laravel project is part of a larger Smart Irrigation System:

```
BackendSystem/
├── SmartIrigationSystem/          # 🌱 This Laravel Backend
│   ├── app/Http/Controllers/Api/   # API controllers
│   ├── app/Models/                 # Eloquent models
│   ├── database/migrations/        # Database schema
│   ├── resources/views/            # Dashboard views
│   └── routes/api.php              # API routes
│
├── mqtt_daemon_project/            # 🔧 Python MQTT & Testing
│   ├── api_tests/                  # API testing scripts
│   └── mqtt_daemon.py              # MQTT integration
│
└── documention/                    # 📚 Project Documentation
    ├── IRRIGATION_LINES_SUMMARY.md
    ├── PROJECT_STRUCTURE_UPDATE.md
    └── HARDWARE_API_DOCS.md
```

## 🔧 Installation & Setup

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/MariaDB
- Node.js & NPM

### Installation
```bash
# Clone and setup
git clone https://github.com/RifkiFiransah/SmartIrigationSystem.git
cd SmartIrigationSystem

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start development server
php artisan serve
```

## 🌐 API Endpoints

### Irrigation Lines Management
- `GET /api/irrigation-lines` - Get all areas and lines summary
- `GET /api/irrigation-lines/area/{areaName}` - Get specific area details  
- `GET /api/irrigation-lines/analytics/efficiency` - Get efficiency analytics
- `GET /api/irrigation-lines/line/{lineId}` - Get detailed line information

### Zone Management  
- `GET /api/zones` - Get zones summary
- `GET /api/zones/{zoneName}` - Get zone details

### Water Storage
- `GET /api/water-storage` - Get tank status
- `POST /api/water-storage/update-volume` - Update tank volume

## 🖥️ Web Dashboard

Access the interactive dashboards:

- **Main Dashboard**: `http://127.0.0.1:8000/dashboard`
- **Irrigation Lines**: `http://127.0.0.1:8000/irrigation-lines`

## 🧪 Testing

API testing scripts are located in `../mqtt_daemon_project/api_tests/`:

```bash
# Navigate to testing directory
cd ../mqtt_daemon_project/

# Test irrigation lines API
python api_tests/test_irrigation_lines_api.py

# Test zone management
python api_tests/test_zone_api.py

# Test line details
python api_tests/test_line_details.py
```

## 📚 Documentation

Comprehensive documentation is available in `../documention/`:

- **[Irrigation Lines System](../documention/IRRIGATION_LINES_SUMMARY.md)** - Complete system documentation
- **[Project Structure](../documention/PROJECT_STRUCTURE_UPDATE.md)** - File organization guide
- **[Hardware API](../documention/HARDWARE_API_DOCS.md)** - Device integration docs

## 🛠️ Key Models & Controllers

### Models
- **WaterStorage** - Tank and irrigation lines management
- **Device** - IoT device integration
- **SensorData** - Sensor readings and monitoring

### Controllers
- **IrrigationLineController** - Irrigation lines management API
- **ZoneController** - Zone-based area management
- **WaterStorageController** - Tank monitoring and control

## 🎯 Development Status

### ✅ Completed
- [x] Area-based irrigation line management
- [x] Efficiency analytics and scoring
- [x] Real-time dashboard interface
- [x] Complete API endpoints
- [x] Database structure and seeding
- [x] Node tracking per irrigation line

### 🔄 In Progress
- [ ] Mobile app integration
- [ ] Advanced IoT device integration
- [ ] Automated scheduling system
- [ ] Weather integration

---

**Laravel Version**: 11.x  
**PHP Version**: 8.1+  
**Database**: MySQL  
**Frontend**: Alpine.js + Blade Templates

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
