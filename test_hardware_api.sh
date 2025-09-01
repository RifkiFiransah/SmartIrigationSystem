#!/bin/bash

# Hardware API Test Script
# Untuk testing endpoint data transfer

API_BASE="http://127.0.0.1:8000/api/transfer"

echo "🧪 Testing Hardware Data Transfer API"
echo "======================================"

# Test 1: Kirim Data Sensor
echo ""
echo "1️⃣ Testing Sensor Data Upload..."
curl -X POST "$API_BASE/sensor-data" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "device_id": "DEVICE_TEST_001",
    "temperature": 26.5,
    "humidity": 68.2,
    "soil_moisture": 42.8,
    "water_flow": 125.5,
    "status": "normal"
  }' | jq '.'

echo ""
echo "---"

# Test 2: Update Water Level
echo ""
echo "2️⃣ Testing Water Level Update..."
curl -X POST "$API_BASE/water-level" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tank_id": 1,
    "current_volume": 680.5,
    "sensor_reading": 78.2
  }' | jq '.'

echo ""
echo "---"

# Test 3: Heartbeat
echo ""
echo "3️⃣ Testing Heartbeat..."
curl -X POST "$API_BASE/heartbeat" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "device_id": "DEVICE_TEST_001",
    "firmware_version": "1.0.0",
    "ip_address": "192.168.1.100",
    "signal_strength": -42
  }' | jq '.'

echo ""
echo "---"

# Test 4: Get Device Config
echo ""
echo "4️⃣ Testing Device Config Retrieval..."
curl -X GET "$API_BASE/device-config/DEVICE_TEST_001" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "---"

# Test 5: Get System Status
echo ""
echo "5️⃣ Testing System Status..."
curl -X GET "$API_BASE/system-status" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "======================================"
echo "✅ Hardware API Testing Complete!"
