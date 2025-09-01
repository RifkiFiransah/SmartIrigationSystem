<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Irrigation - Lines Management</title>
    <script src="https://unpkg.com/alpine@3.x.x/dist/alpine.min.js" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .title {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #718096;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .areas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
        }
        
        .area-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .area-header {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px;
        }
        
        .area-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .area-info {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .area-content {
            padding: 20px;
        }
        
        .area-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .area-stat {
            text-align: center;
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
        }
        
        .area-stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3748;
        }
        
        .area-stat-label {
            font-size: 0.8rem;
            color: #718096;
            margin-top: 5px;
        }
        
        .lines-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .line-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #48bb78;
        }
        
        .line-item.maintenance {
            border-left-color: #ed8936;
        }
        
        .line-item.inactive {
            border-left-color: #e53e3e;
            opacity: 0.7;
        }
        
        .line-info {
            flex: 1;
        }
        
        .line-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 3px;
        }
        
        .line-details {
            font-size: 0.8rem;
            color: #718096;
        }
        
        .line-status {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-maintenance {
            background: #fdd8b5;
            color: #7b341e;
        }
        
        .status-inactive {
            background: #fed7d7;
            color: #822727;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .error {
            background: #fed7d7;
            color: #822727;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard" x-data="irrigationLinesData()" x-init="loadData()">
        <!-- Header -->
        <div class="header">
            <div class="title">🌱 Smart Irrigation Lines Management</div>
            <div class="subtitle">Area-based irrigation line monitoring and control system</div>
        </div>
        
        <!-- Error Message -->
        <div x-show="error" class="error" x-text="error"></div>
        
        <!-- Loading State -->
        <div x-show="loading" class="stat-card" style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 10px;">⏳</div>
            <div>Loading irrigation lines data...</div>
        </div>
        
        <!-- Summary Stats -->
        <div x-show="!loading && summary" class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🏞️</div>
                <div class="stat-value" x-text="summary.total_areas"></div>
                <div class="stat-label">Total Areas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💧</div>
                <div class="stat-value" x-text="summary.total_lines"></div>
                <div class="stat-label">Irrigation Lines</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🟢</div>
                <div class="stat-value" x-text="summary.total_active_lines"></div>
                <div class="stat-label">Active Lines</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🌿</div>
                <div class="stat-value" x-text="summary.total_plants.toLocaleString()"></div>
                <div class="stat-label">Total Plants</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📐</div>
                <div class="stat-value" x-text="summary.total_coverage_sqm + ' m²'"></div>
                <div class="stat-label">Coverage Area</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚰</div>
                <div class="stat-value" x-text="summary.total_flow_rate_lpm + ' L/min'"></div>
                <div class="stat-label">Total Flow Rate</div>
            </div>
        </div>
        
        <!-- Refresh Button -->
        <div style="text-align: center; margin-bottom: 20px;">
            <button class="refresh-btn" @click="loadData()" :class="{ loading: loading }">
                <span x-show="!loading">🔄 Refresh Data</span>
                <span x-show="loading">Loading...</span>
            </button>
        </div>
        
        <!-- Areas Grid -->
        <div x-show="!loading && areas.length > 0" class="areas-grid">
            <template x-for="area in areas" :key="area.area_name">
                <div class="area-card">
                    <div class="area-header">
                        <div class="area-name" x-text="area.area_name"></div>
                        <div class="area-info">
                            <strong x-text="area.zone_name"></strong> • 
                            <span x-text="area.plant_types"></span> • 
                            <span x-text="area.irrigation_system_type.toUpperCase()"></span>
                        </div>
                    </div>
                    <div class="area-content">
                        <!-- Area Statistics -->
                        <div class="area-stats">
                            <div class="area-stat">
                                <div class="area-stat-value" x-text="area.total_lines"></div>
                                <div class="area-stat-label">Total Lines</div>
                            </div>
                            <div class="area-stat">
                                <div class="area-stat-value" x-text="area.active_lines"></div>
                                <div class="area-stat-label">Active</div>
                            </div>
                            <div class="area-stat">
                                <div class="area-stat-value" x-text="area.total_plants"></div>
                                <div class="area-stat-label">Plants</div>
                            </div>
                            <div class="area-stat">
                                <div class="area-stat-value" x-text="area.water_efficiency_lpm_per_plant.toFixed(3)"></div>
                                <div class="area-stat-label">L/min/plant</div>
                            </div>
                        </div>
                        
                        <!-- Lines List -->
                        <div class="lines-list">
                            <template x-for="line in area.lines" :key="line.line_id">
                                <div class="line-item" :class="line.status">
                                    <div class="line-info">
                                        <div class="line-name" x-text="line.line_name"></div>
                                        <div class="line-details">
                                            <span x-text="line.line_type.toUpperCase()"></span> • 
                                            <span x-text="line.plant_count + ' plants'"></span> • 
                                            <span x-text="line.flow_rate_lpm + ' L/min'"></span> • 
                                            <span x-text="line.coverage_sqm + ' m²'"></span>
                                        </div>
                                    </div>
                                    <div class="line-status" :class="'status-' + line.status" x-text="line.status"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function irrigationLinesData() {
            return {
                loading: false,
                error: null,
                summary: null,
                areas: [],
                
                async loadData() {
                    this.loading = true;
                    this.error = null;
                    
                    try {
                        const response = await fetch('/api/irrigation-lines');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.summary = data.data.summary;
                            this.areas = data.data.areas;
                        } else {
                            this.error = 'Failed to load irrigation lines data: ' + (data.message || 'Unknown error');
                        }
                    } catch (error) {
                        this.error = 'Network error: ' + error.message;
                        console.error('Error loading irrigation lines:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
