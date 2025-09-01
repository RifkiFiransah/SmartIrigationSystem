#!/usr/bin/env python3
"""
Hardware API Test Script in Python
Untuk testing endpoint data transfer dengan hardware
"""

import requests
import json
import time
from datetime import datetime

API_BASE = "http://127.0.0.1:8000/api/transfer"

def test_sensor_data():
    """Test kirim data sensor"""
    print("1️⃣ Testing Sensor Data Upload...")
    
    data = {
        "device_id": "DEVICE_PYTHON_001",
        "temperature": 27.3,
        "humidity": 72.5,
        "soil_moisture": 48.2,
        "water_flow": 135.8,
        "status": "normal",
        "timestamp": datetime.now().isoformat() + "Z"
    }
    
    try:
        response = requests.post(f"{API_BASE}/sensor-data", json=data)
        if response.status_code == 201:
            result = response.json()
            print("✅ SUCCESS:", result["message"])
            print("📊 Data ID:", result["data"]["id"])
        else:
            print("❌ ERROR:", response.json())
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_water_level():
    """Test update level air"""
    print("\n2️⃣ Testing Water Level Update...")
    
    data = {
        "tank_id": 1,
        "current_volume": 720.5,
        "sensor_reading": 82.3,
        "timestamp": datetime.now().isoformat() + "Z"
    }
    
    try:
        response = requests.post(f"{API_BASE}/water-level", json=data)
        if response.status_code == 200:
            result = response.json()
            print("✅ SUCCESS:", result["message"])
            print("💧 Tank:", result["data"]["tank_name"])
            print("📊 Percentage:", f"{result['data']['percentage']}%")
        else:
            print("❌ ERROR:", response.json())
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_heartbeat():
    """Test heartbeat/ping"""
    print("\n3️⃣ Testing Heartbeat...")
    
    data = {
        "device_id": "DEVICE_PYTHON_001",
        "firmware_version": "2.1.0",
        "ip_address": "192.168.1.150",
        "signal_strength": -38
    }
    
    try:
        response = requests.post(f"{API_BASE}/heartbeat", json=data)
        if response.status_code == 200:
            result = response.json()
            print("✅ SUCCESS:", result["message"])
            print("⏰ Server Time:", result["data"]["server_time"])
        else:
            print("❌ ERROR:", response.json())
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_device_config():
    """Test ambil konfigurasi device"""
    print("\n4️⃣ Testing Device Config...")
    
    try:
        response = requests.get(f"{API_BASE}/device-config/DEVICE_PYTHON_001")
        if response.status_code == 200:
            result = response.json()
            print("✅ SUCCESS:", result["message"])
            print("⚙️ Sampling Interval:", result["config"]["settings"]["sampling_interval"], "seconds")
            print("🔄 Transmission Interval:", result["config"]["settings"]["transmission_interval"], "seconds")
        else:
            result = response.json()
            print("⚠️ Device not found, got default config")
            print("⚙️ Default Sampling:", result["config"]["settings"]["sampling_interval"], "seconds")
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_system_status():
    """Test ambil status sistem"""
    print("\n5️⃣ Testing System Status...")
    
    try:
        response = requests.get(f"{API_BASE}/system-status")
        if response.status_code == 200:
            result = response.json()
            print("✅ SUCCESS:", result["message"])
            stats = result["data"]["system_stats"]
            print("🖥️ Total Devices:", stats["total_devices"])
            print("🟢 Online Devices:", stats["online_devices"])
            print("💧 Total Water Tanks:", stats["total_water_tanks"])
            print("⚠️ Low Water Tanks:", stats["low_water_tanks"])
            print("💚 System Health:", stats["system_health"])
        else:
            print("❌ ERROR:", response.json())
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def simulate_hardware_loop():
    """Simulasi hardware yang mengirim data secara berkala"""
    print("\n🔄 Starting Hardware Simulation Loop...")
    print("Press Ctrl+C to stop")
    
    device_id = "DEVICE_PYTHON_SIM"
    
    try:
        for i in range(10):  # 10 cycles
            print(f"\n--- Cycle {i+1} ---")
            
            # Simulasi data sensor yang berubah-ubah
            import random
            temp = round(20 + random.random() * 15, 1)  # 20-35°C
            humidity = round(40 + random.random() * 40, 1)  # 40-80%
            soil = round(30 + random.random() * 40, 1)  # 30-70%
            flow = round(random.random() * 200, 1)  # 0-200 L/h
            
            # Kirim data sensor
            sensor_data = {
                "device_id": device_id,
                "temperature": temp,
                "humidity": humidity,
                "soil_moisture": soil,
                "water_flow": flow,
                "status": "normal"
            }
            
            response = requests.post(f"{API_BASE}/sensor-data", json=sensor_data)
            if response.status_code == 201:
                print(f"📊 Sensor data sent: T={temp}°C, H={humidity}%, S={soil}%, F={flow}L/h")
            
            # Kirim heartbeat
            heartbeat_data = {
                "device_id": device_id,
                "firmware_version": "2.1.0",
                "signal_strength": random.randint(-60, -30)
            }
            
            response = requests.post(f"{API_BASE}/heartbeat", json=heartbeat_data)
            if response.status_code == 200:
                print("💓 Heartbeat sent")
            
            # Tunggu 5 detik sebelum cycle berikutnya
            time.sleep(5)
            
    except KeyboardInterrupt:
        print("\n⏹️ Simulation stopped by user")

if __name__ == "__main__":
    print("🧪 Hardware Data Transfer API Testing")
    print("====================================")
    
    # Test semua endpoint
    test_sensor_data()
    test_water_level()
    test_heartbeat()
    test_device_config()
    test_system_status()
    
    print("\n" + "="*40)
    
    # Tanya user apakah ingin simulasi
    try:
        choice = input("\n🤖 Run hardware simulation? (y/n): ").lower()
        if choice == 'y':
            simulate_hardware_loop()
    except KeyboardInterrupt:
        pass
    
    print("\n✅ Testing Complete!")
