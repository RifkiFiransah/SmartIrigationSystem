#!/usr/bin/env python3
"""
Irrigation Control API Test Script
Testing semua endpoint untuk irrigation control system
"""

import requests
import json
import time
from datetime import datetime

API_BASE = "http://127.0.0.1:8000/api/irrigation"

def print_response(title, response):
    """Helper untuk print response dengan format bagus"""
    print(f"\n{title}")
    print("=" * len(title))
    try:
        if response.status_code == 200 or response.status_code == 201:
            result = response.json()
            print("✅ SUCCESS:", result.get("message", "No message"))
            if "data" in result:
                print("📊 Data:")
                print(json.dumps(result["data"], indent=2, default=str))
        else:
            print(f"❌ ERROR {response.status_code}:", response.json())
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_get_controls():
    """Test 1: Get all irrigation controls"""
    print("\n1️⃣ Testing Get All Controls...")
    
    try:
        response = requests.get(f"{API_BASE}/controls")
        print_response("All Irrigation Controls", response)
        
        if response.status_code == 200:
            data = response.json()["data"]
            print(f"🔧 Found {len(data)} irrigation controls")
            for control in data:
                print(f"   • {control['control_name']} ({control['control_type']}) - Status: {control['status_icon']} {control['status']}")
                
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_get_status():
    """Test 2: Get irrigation system status"""
    print("\n2️⃣ Testing Get Irrigation Status...")
    
    try:
        response = requests.get(f"{API_BASE}/status")
        print_response("Irrigation System Status", response)
        
        if response.status_code == 200:
            data = response.json()["data"]
            overview = data["system_overview"]
            stats = data["today_stats"]
            print(f"🖥️ Total Controls: {overview['total_controls']}")
            print(f"🟢 Running: {overview['running_controls']}")
            print(f"🤖 Auto Mode: {overview['auto_mode_controls']}")
            print(f"📊 Today Runs: {stats['total_runs']}")
            print(f"⏱️ Today Duration: {stats['total_duration_minutes']:.1f} minutes")
            print(f"💧 Water Used: {stats['total_water_used']} L")
            
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_start_irrigation():
    """Test 3: Start irrigation manually"""
    print("\n3️⃣ Testing Start Irrigation...")
    
    data = {
        "control_id": 1,
        "duration_minutes": 25,
        "trigger_type": "manual",
        "triggered_by": "Python Test Script",
        "notes": "Testing manual irrigation start from Python"
    }
    
    try:
        response = requests.post(f"{API_BASE}/start", json=data)
        print_response("Start Irrigation", response)
        
        if response.status_code == 200:
            result = response.json()["data"]
            print(f"🚿 Started: {result['control_name']}")
            print(f"⏱️ Duration: {result['duration_minutes']} minutes")
            print(f"🕐 Started at: {result['started_at']}")
            
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_status_after_start():
    """Test 4: Check status after starting irrigation"""
    print("\n4️⃣ Testing Status After Start...")
    
    try:
        response = requests.get(f"{API_BASE}/status")
        print_response("Status After Start", response)
        
        if response.status_code == 200:
            data = response.json()["data"]
            running_now = data["running_now"]
            if running_now:
                print("🟢 Currently Running:")
                for running in running_now:
                    print(f"   • {running['control_name']} - {running['duration_so_far']} minutes")
            else:
                print("⚪ No irrigation currently running")
                
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_stop_irrigation():
    """Test 5: Stop irrigation"""
    print("\n5️⃣ Testing Stop Irrigation...")
    
    data = {
        "control_id": 1,
        "reason": "Manual stop for Python testing",
        "triggered_by": "Python Test Script"
    }
    
    try:
        response = requests.post(f"{API_BASE}/stop", json=data)
        print_response("Stop Irrigation", response)
        
        if response.status_code == 200:
            result = response.json()["data"]
            print(f"🛑 Stopped: {result['control_name']}")
            print(f"⏱️ Total Duration: {result.get('total_duration', 'N/A')}")
            
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_toggle_mode():
    """Test 6 & 7: Toggle mode between auto and manual"""
    print("\n6️⃣ Testing Toggle Mode to Auto...")
    
    data = {"control_id": 1, "mode": "auto"}
    
    try:
        response = requests.post(f"{API_BASE}/toggle-mode", json=data)
        print_response("Toggle to Auto Mode", response)
        
    except Exception as e:
        print("❌ EXCEPTION:", str(e))
    
    print("\n7️⃣ Testing Toggle Mode to Manual...")
    
    data = {"control_id": 1, "mode": "manual"}
    
    try:
        response = requests.post(f"{API_BASE}/toggle-mode", json=data)
        print_response("Toggle to Manual Mode", response)
        
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_run_scheduled():
    """Test 8: Run scheduled irrigation"""
    print("\n8️⃣ Testing Run Scheduled Irrigation...")
    
    try:
        response = requests.post(f"{API_BASE}/run-scheduled")
        print_response("Run Scheduled Irrigation", response)
        
        if response.status_code == 200:
            result = response.json()["data"]
            print(f"🔄 Executed: {result['executed_count']} schedules")
            print(f"❌ Errors: {result['error_count']} errors")
            
            if result["executed"]:
                print("✅ Successfully executed:")
                for executed in result["executed"]:
                    print(f"   • {executed['schedule_name']} → {executed['control_name']}")
                    
            if result["errors"]:
                print("❌ Execution errors:")
                for error in result["errors"]:
                    print(f"   • {error['schedule_name']}: {error['error']}")
            
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_get_logs():
    """Test 9: Get irrigation logs"""
    print("\n9️⃣ Testing Get Irrigation Logs...")
    
    try:
        response = requests.get(f"{API_BASE}/logs?per_page=5")
        print_response("Recent Irrigation Logs", response)
        
        if response.status_code == 200:
            result = response.json()["data"]
            logs = result["data"] if "data" in result else result
            
            print(f"📋 Found {len(logs)} recent logs:")
            for log in logs:
                print(f"   • {log['action_icon']} {log['action']} - {log['control_name']} ({log['trigger_type']})")
                print(f"     📅 {log['started_at']} | Duration: {log['duration']} | Status: {log['status_icon']}")
                if log.get('notes'):
                    print(f"     📝 {log['notes']}")
                    
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def test_emergency_stop():
    """Test 10: Emergency stop all irrigation"""
    print("\n🔟 Testing Emergency Stop...")
    
    data = {
        "reason": "Emergency test from Python script",
        "triggered_by": "Python Test Script"
    }
    
    try:
        response = requests.post(f"{API_BASE}/emergency-stop", json=data)
        print_response("Emergency Stop", response)
        
        if response.status_code == 200:
            result = response.json()["data"]
            print(f"🛑 Stopped {result['stopped_count']} irrigation controls")
            
            if result["stopped_controls"]:
                print("🛑 Stopped controls:")
                for stopped in result["stopped_controls"]:
                    print(f"   • {stopped['control_name']}")
            
    except Exception as e:
        print("❌ EXCEPTION:", str(e))

def main():
    """Main test function"""
    print("🚿 Testing Irrigation Control API")
    print("==================================")
    
    # Run all tests
    test_get_controls()
    test_get_status()
    test_start_irrigation()
    
    # Wait a bit to see the effect
    print("\n⏰ Waiting 3 seconds...")
    time.sleep(3)
    
    test_status_after_start()
    test_stop_irrigation()
    test_toggle_mode()
    test_run_scheduled()
    test_get_logs()
    test_emergency_stop()
    
    print("\n" + "="*40)
    print("✅ Irrigation API Testing Complete!")
    
    # Ask user if they want to run a simulation
    try:
        choice = input("\n🤖 Run irrigation simulation? (y/n): ").lower()
        if choice == 'y':
            irrigation_simulation()
    except KeyboardInterrupt:
        pass

def irrigation_simulation():
    """Simulasi sistem irigasi bekerja"""
    print("\n🔄 Starting Irrigation Simulation...")
    print("Press Ctrl+C to stop")
    
    try:
        for i in range(5):  # 5 cycles
            print(f"\n--- Simulation Cycle {i+1} ---")
            
            # Start irrigation
            start_data = {
                "control_id": 1,
                "duration_minutes": 5,
                "trigger_type": "api",
                "triggered_by": "Simulation Script",
                "notes": f"Simulation cycle {i+1}"
            }
            
            response = requests.post(f"{API_BASE}/start", json=start_data)
            if response.status_code == 200:
                print("✅ Irrigation started")
            
            # Wait 2 seconds
            time.sleep(2)
            
            # Check status
            response = requests.get(f"{API_BASE}/status")
            if response.status_code == 200:
                data = response.json()["data"]
                running_count = data["system_overview"]["running_controls"]
                print(f"🔄 Running controls: {running_count}")
            
            # Stop irrigation
            stop_data = {
                "control_id": 1,
                "reason": f"End of simulation cycle {i+1}",
                "triggered_by": "Simulation Script"
            }
            
            response = requests.post(f"{API_BASE}/stop", json=stop_data)
            if response.status_code == 200:
                print("🛑 Irrigation stopped")
            
            # Wait before next cycle
            if i < 4:  # Don't wait after last cycle
                time.sleep(1)
    
    except KeyboardInterrupt:
        print("\n⏹️ Simulation stopped by user")
        # Emergency stop
        requests.post(f"{API_BASE}/emergency-stop", json={
            "reason": "Simulation interrupted",
            "triggered_by": "User Interrupt"
        })

if __name__ == "__main__":
    main()
