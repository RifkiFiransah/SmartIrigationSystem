#!/bin/bash

# Irrigation Control API Test Script

API_BASE="http://127.0.0.1:8000/api/irrigation"

echo "🚿 Testing Irrigation Control API"
echo "=================================="

# Test 1: Get All Controls
echo ""
echo "1️⃣ Testing Get All Controls..."
curl -X GET "$API_BASE/controls" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "---"

# Test 2: Get Irrigation Status
echo ""
echo "2️⃣ Testing Get Irrigation Status..."
curl -X GET "$API_BASE/status" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "---"

# Test 3: Start Irrigation (Manual)
echo ""
echo "3️⃣ Testing Start Irrigation..."
curl -X POST "$API_BASE/start" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "control_id": 1,
    "duration_minutes": 25,
    "trigger_type": "manual",
    "triggered_by": "Test API",
    "notes": "Testing manual irrigation start"
  }' | jq '.'

echo ""
echo "---"

# Wait a bit
echo ""
echo "⏰ Waiting 3 seconds..."
sleep 3

# Test 4: Check Status After Start
echo ""
echo "4️⃣ Testing Status After Start..."
curl -X GET "$API_BASE/status" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "---"

# Test 5: Stop Irrigation
echo ""
echo "5️⃣ Testing Stop Irrigation..."
curl -X POST "$API_BASE/stop" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "control_id": 1,
    "reason": "Manual stop for testing",
    "triggered_by": "Test API"
  }' | jq '.'

echo ""
echo "---"

# Test 6: Toggle Mode
echo ""
echo "6️⃣ Testing Toggle Mode to Auto..."
curl -X POST "$API_BASE/toggle-mode" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "control_id": 1,
    "mode": "auto"
  }' | jq '.'

echo ""
echo "---"

# Test 7: Toggle Mode Back
echo ""
echo "7️⃣ Testing Toggle Mode to Manual..."
curl -X POST "$API_BASE/toggle-mode" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "control_id": 1,
    "mode": "manual"
  }' | jq '.'

echo ""
echo "---"

# Test 8: Run Scheduled (Automation)
echo ""
echo "8️⃣ Testing Run Scheduled Irrigation..."
curl -X POST "$API_BASE/run-scheduled" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "---"

# Test 9: Get Logs
echo ""
echo "9️⃣ Testing Get Irrigation Logs..."
curl -X GET "$API_BASE/logs?per_page=5" \
  -H "Accept: application/json" | jq '.'

echo ""
echo "---"

# Test 10: Emergency Stop (if any running)
echo ""
echo "🔟 Testing Emergency Stop..."
curl -X POST "$API_BASE/emergency-stop" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "reason": "Emergency test",
    "triggered_by": "Test Script"
  }' | jq '.'

echo ""
echo "======================================"
echo "✅ Irrigation API Testing Complete!"
