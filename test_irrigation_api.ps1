# Irrigation Control API Test Script for Windows PowerShell

$ApiBase = "http://127.0.0.1:8000/api/irrigation"

Write-Host "🚿 Testing Irrigation Control API" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green

# Test 1: Get All Controls
Write-Host ""
Write-Host "1️⃣ Testing Get All Controls..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/controls" -Method Get
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 2: Get Irrigation Status
Write-Host ""
Write-Host "2️⃣ Testing Get Irrigation Status..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/status" -Method Get
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 3: Start Irrigation (Manual)
Write-Host ""
Write-Host "3️⃣ Testing Start Irrigation..." -ForegroundColor Yellow

$startData = @{
    control_id = 1
    duration_minutes = 25
    trigger_type = "manual"
    triggered_by = "PowerShell Test"
    notes = "Testing manual irrigation start from PowerShell"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/start" -Method Post -Body $startData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Wait a bit
Write-Host ""
Write-Host "⏰ Waiting 3 seconds..." -ForegroundColor Cyan
Start-Sleep -Seconds 3

# Test 4: Check Status After Start
Write-Host ""
Write-Host "4️⃣ Testing Status After Start..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/status" -Method Get
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 5: Stop Irrigation
Write-Host ""
Write-Host "5️⃣ Testing Stop Irrigation..." -ForegroundColor Yellow

$stopData = @{
    control_id = 1
    reason = "Manual stop for PowerShell testing"
    triggered_by = "PowerShell Test"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/stop" -Method Post -Body $stopData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 6: Toggle Mode
Write-Host ""
Write-Host "6️⃣ Testing Toggle Mode to Auto..." -ForegroundColor Yellow

$modeData = @{
    control_id = 1
    mode = "auto"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/toggle-mode" -Method Post -Body $modeData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 7: Toggle Mode Back
Write-Host ""
Write-Host "7️⃣ Testing Toggle Mode to Manual..." -ForegroundColor Yellow

$modeData2 = @{
    control_id = 1
    mode = "manual"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/toggle-mode" -Method Post -Body $modeData2 -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 8: Run Scheduled (Automation)
Write-Host ""
Write-Host "8️⃣ Testing Run Scheduled Irrigation..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/run-scheduled" -Method Post
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 9: Get Logs
Write-Host ""
Write-Host "9️⃣ Testing Get Irrigation Logs..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/logs?per_page=5" -Method Get
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "---"

# Test 10: Emergency Stop
Write-Host ""
Write-Host "🔟 Testing Emergency Stop..." -ForegroundColor Yellow

$emergencyData = @{
    reason = "Emergency test from PowerShell"
    triggered_by = "PowerShell Test Script"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$ApiBase/emergency-stop" -Method Post -Body $emergencyData -ContentType "application/json"
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host "✅ Irrigation API Testing Complete!" -ForegroundColor Green
