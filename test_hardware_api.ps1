# Hardware API Test Script for Windows PowerShell
# Untuk testing endpoint data transfer

$ApiBase = "http://127.0.0.1:8000/api/transfer"

Write-Host "🧪 Testing Hardware Data Transfer API" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green

# Test 1: Kirim Data Sensor
Write-Host ""
Write-Host "1️⃣ Testing Sensor Data Upload..." -ForegroundColor Yellow

$sensorData = @{
    device_id = "DEVICE_TEST_001"
    temperature = 26.5
    humidity = 68.2
    soil_moisture = 42.8
    water_flow = 125.5
    status = "normal"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/sensor-data" -Method Post -Body $sensorData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 2: Update Water Level
Write-Host ""
Write-Host "2️⃣ Testing Water Level Update..." -ForegroundColor Yellow

$waterData = @{
    tank_id = 1
    current_volume = 680.5
    sensor_reading = 78.2
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/water-level" -Method Post -Body $waterData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 3: Heartbeat
Write-Host ""
Write-Host "3️⃣ Testing Heartbeat..." -ForegroundColor Yellow

$heartbeatData = @{
    device_id = "DEVICE_TEST_001"
    firmware_version = "1.0.0"
    ip_address = "192.168.1.100"
    signal_strength = -42
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/heartbeat" -Method Post -Body $heartbeatData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 4: Get Device Config
Write-Host ""
Write-Host "4️⃣ Testing Device Config Retrieval..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/device-config/DEVICE_TEST_001" -Method Get
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 5: Get System Status
Write-Host ""
Write-Host "5️⃣ Testing System Status..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/system-status" -Method Get
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host "✅ Hardware API Testing Complete!" -ForegroundColor Green
