# 🌱 Smart Irrigation Lines Management System

## 📋 System Overview

Sistem manajemen jalur irigasi berbasis area yang telah berhasil diimplementasikan dengan struktur:
- **1 Tank** → **1 Area** → **Multiple Irrigation Lines** → **Multiple Nodes**

## 🏗️ Architecture

### Database Structure
- **WaterStorage Model**: Enhanced dengan field `irrigation_lines` (JSON), `area_name`, `total_lines`, `area_size_sqm`, `plant_types`, `irrigation_system_type`
- **Migration**: `2025_01_20_120000_recreate_water_storages_table.php` - Complete table restructure
- **Seeder**: `WaterStorageSeederFixed.php` - Comprehensive data dengan 3 areas, 24 irrigation lines

### API Endpoints

#### 1. Irrigation Lines Summary
**Endpoint**: `GET /api/irrigation-lines`
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_areas": 3,
      "total_lines": 24,
      "total_active_lines": 18,
      "total_plants": 1270,
      "total_coverage_sqm": 1150,
      "total_flow_rate_lpm": 43.1
    },
    "areas": [...]
  }
}
```

#### 2. Area Details
**Endpoint**: `GET /api/irrigation-lines/area/{areaName}`
```json
{
  "success": true,
  "data": {
    "area_info": {...},
    "statistics": {
      "total_lines": 8,
      "active_lines": 6,
      "maintenance_lines": 1,
      "inactive_lines": 1
    },
    "lines": [...],
    "tanks": [...]
  }
}
```

#### 3. Efficiency Analytics
**Endpoint**: `GET /api/irrigation-lines/analytics/efficiency`
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_active_lines": 18,
      "average_efficiency": 75.39,
      "best_performing_line": {...}
    },
    "line_analytics": [...]
  }
}
```

#### 4. Line Details with Nodes
**Endpoint**: `GET /api/irrigation-lines/line/{lineId}`
```json
{
  "success": true,
  "data": {
    "line_info": {...},
    "area_info": {...},
    "nodes": {
      "total_nodes": 2,
      "active_nodes": 2,
      "node_list": [
        {
          "node_id": "NODE_A1_001",
          "sensor_type": "soil_moisture",
          "location": "Row 1-2",
          "status": "active"
        }
      ]
    },
    "performance_metrics": {...}
  }
}
```

## 🎯 Key Features

### 1. Area-Based Management
- **3 Areas**: Blok Tomat Hidroponik A1, Blok Sayuran Berdaun B1, Backup Area
- **24 Irrigation Lines** total across all areas
- **Different irrigation systems**: Drip, NFT, Misting, Sprinkler

### 2. Detailed Line Information
- Line ID, name, type, status
- Plant count, coverage area (m²)
- Flow rate (L/min), water per plant
- Plant density (plants/m²)

### 3. Node Tracking
- **Node details**: ID, sensor type, location, status
- **Status monitoring**: Active, maintenance, inactive
- **Sensor types**: soil_moisture, temperature_humidity, ph_ec, flow_meter, pressure, etc.

### 4. Efficiency Analytics
- **Efficiency scoring**: Based on water consumption, plant density, irrigation type
- **Performance ratings**: Excellent (≥80%), Good (≥70%), Fair (≥60%), Needs Improvement (<60%)
- **Water consumption rating**: Efficient, Moderate, High
- **Plant density rating**: High, Medium, Low density

### 5. Real-time Dashboard
- **URL**: `http://127.0.0.1:8000/irrigation-lines`
- **Features**:
  - Summary statistics
  - Area cards with line details
  - Real-time status updates
  - Efficiency metrics
  - Responsive design

## 📊 Sample Data Structure

### Area Example: Blok Tomat Hidroponik A1
- **Tank**: Main Water Tank A (2000L, 80% full)
- **Size**: 400 m²
- **Plants**: Tomat Cherry, Tomat Beefsteak
- **System**: Drip irrigation
- **Lines**: 8 total (6 active, 1 maintenance, 1 inactive)
- **Total Plants**: 420
- **Flow Rate**: 15.5 L/min
- **Efficiency**: 0.037 L/min per plant

### Line Example: L001 - Jalur Tomat Cherry A
- **Type**: Drip irrigation
- **Plants**: 50 plants
- **Coverage**: 50 m²
- **Flow**: 2.5 L/min (0.05 L/min per plant)
- **Efficiency Score**: 100%
- **Nodes**: 2 active nodes (soil_moisture, temperature_humidity)

## 🧪 Testing

### Test Scripts Location
Test scripts telah dipindahkan ke directory terpisah untuk better organization:
```
../mqtt_daemon_project/api_tests/
├── test_irrigation_lines_api.py    # Comprehensive API testing
├── test_line_details.py            # Detailed line information testing
├── test_irrigation_api.py          # Irrigation control testing
├── test_zone_api.py                # Zone management testing
└── test_hardware_api.py            # Hardware/device testing
```

### Running Tests
```bash
# Navigate to mqtt_daemon_project
cd ../mqtt_daemon_project/

# Test irrigation lines management
python api_tests/test_irrigation_lines_api.py

# Test line details
python api_tests/test_line_details.py

# Test zone management
python api_tests/test_zone_api.py
```

### Test Results
- ✅ All API endpoints working correctly
- ✅ Efficiency calculations accurate
- ✅ Node information properly structured
- ✅ Error handling for invalid requests
- ✅ Dashboard loading and displaying data

## 🔧 Technical Implementation

### Controller: `IrrigationLineController.php`
- `getIrrigationLinesSummary()`: Area overview with statistics
- `getAreaIrrigationLines()`: Detailed area information
- `getLineEfficiencyAnalytics()`: Performance analytics
- `getLineDetails()`: Individual line with nodes

### Model Enhancements: `WaterStorage.php`
- `getIrrigationInfoAttribute()`: Irrigation summary calculations
- `getActiveIrrigationLines()`: Filter active lines
- `getTotalPlantsAttribute()`: Plant count aggregation

### Efficiency Algorithm
```php
private function calculateEfficiencyScore($waterPerPlant, $plantsPerSqm, $lineType)
{
    $score = 100;
    
    // Optimal water per plant based on type
    $optimalWater = match ($lineType) {
        'drip' => 0.05,
        'nft' => 0.03,
        'sprinkler' => 0.08,
        'misting' => 0.01,
        default => 0.05
    };
    
    // Penalty for water usage deviation
    $waterDeviation = abs($waterPerPlant - $optimalWater) / $optimalWater;
    $score -= min($waterDeviation * 30, 40);
    
    // Bonus for good plant density
    if ($plantsPerSqm >= 3 && $plantsPerSqm <= 8) {
        $score += 10;
    } elseif ($plantsPerSqm < 1) {
        $score -= 20;
    }
    
    return max(0, min(100, round($score)));
}
```

## 🎉 Completion Status

### ✅ Completed Features
1. **Database Structure**: Complete with irrigation lines JSON field
2. **API Endpoints**: All 4 endpoints implemented and tested
3. **Data Management**: Comprehensive seeder with realistic data
4. **Efficiency Analytics**: Advanced scoring algorithm
5. **Node Tracking**: Detailed sensor information
6. **Dashboard**: Interactive web interface
7. **Testing**: Comprehensive test coverage

### 📈 Performance Metrics
- **Total Areas**: 3
- **Total Lines**: 24 (18 active, 4 maintenance, 2 inactive)
- **Total Plants**: 1,270 plants
- **Coverage Area**: 1,150 m²
- **Total Flow Rate**: 43.1 L/min
- **Average Efficiency**: 75.39%

## 🚀 Next Steps (Optional Enhancements)

1. **Real-time Updates**: WebSocket integration for live data
2. **Mobile App**: React Native or Flutter mobile interface
3. **IoT Integration**: Direct sensor data integration
4. **Advanced Analytics**: Machine learning for predictive maintenance
5. **Automated Control**: API endpoints for irrigation control
6. **Reporting**: PDF reports and data export
7. **User Management**: Role-based access control

---

**System Status**: ✅ **FULLY OPERATIONAL**
**Last Updated**: January 2025
**Total Development Time**: Complete irrigation lines management system with area-based architecture
