<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Device;
use App\Models\IrrigationDailyPlan;
use App\Models\IrrigationSession;
use Carbon\Carbon;

class DeviceUsageSeeder extends Seeder
{
    public function run(): void
    {
    // Seed ALL active devices so modal setiap perangkat punya riwayat penggunaan
    $devices = Device::where('is_active', true)->orderBy('id')->get();
        if($devices->isEmpty()) return;

        // Create daily plans for last 3 days including today if not exists
        $days = [0,1,2]; // 0=today, 1=yesterday, etc.
        foreach($days as $offset){
            $date = today()->subDays($offset);
            $plan = IrrigationDailyPlan::firstOrCreate([
                'plan_date' => $date->toDateString(),
            ],[
                'base_total_volume_l' => 0,
                'adjusted_total_volume_l' => 0,
                'adjustment_factors' => ['rain_forecast' => false],
                'status' => 'generated',
            ]);

            if($plan->sessions()->count() === 0){
                // create 3 sessions (morning, noon, evening)
                $slots = [
                    ['t'=>'06:30:00','base'=>30],
                    ['t'=>'12:30:00','base'=>40],
                    ['t'=>'17:30:00','base'=>35],
                ];
                $totalPlanned = 0; $totalAdjusted = 0;
                foreach($slots as $i=>$slot){
                    $planned = $slot['base'];
                    $adjusted = $planned + rand(-5,5); // minor adjustment
                    $actual = max(0, $adjusted + rand(-8,8));
                    $status = $actual > 0 ? 'completed' : 'pending';
                    $session = $plan->sessions()->create([
                        'session_index' => $i+1,
                        'scheduled_time' => $slot['t'],
                        'planned_volume_l' => $planned,
                        'adjusted_volume_l' => $adjusted,
                        'actual_volume_l' => $status==='completed' ? $actual : null,
                        'status' => $status,
                        'started_at' => $status==='completed'? $date->copy()->setTimeFromTimeString($slot['t'])->addMinutes(1) : null,
                        'completed_at' => $status==='completed'? $date->copy()->setTimeFromTimeString($slot['t'])->addMinutes(rand(10,25)) : null,
                        'meta' => [
                            'auto_adjust' => true,
                        ],
                    ]);
                    $totalPlanned += $planned;
                    $totalAdjusted += $adjusted;
                }
                $plan->base_total_volume_l = $totalPlanned;
                $plan->adjusted_total_volume_l = $totalAdjusted;
                $plan->save();
            }
        }

        // Create usage logs per device for last 14 days. Untuk konsistensi dengan plan, pecah volume harian
        // ke 1-3 sesi (random) dan simpan session_count di meta agar bisa dihitung di frontend jika perlu.
        foreach($devices as $device){
            for($d=0;$d<14;$d++){
                $date = today()->subDays($d);
                if(rand(0,100) < 10) continue; // skip some days
                $sessionCount = rand(1,3);
                $dailyTotal = 0;
                for($s=1;$s<=$sessionCount;$s++){
                    $sessionVol = rand(8,35) + rand(0,99)/100; // 8 - 35 L
                    $dailyTotal += $sessionVol;
                    DB::table('water_usage_logs')->insert([
                        'water_storage_id' => 1,
                        'device_id' => $device->id,
                        'usage_date' => $date->toDateString(),
                        'volume_used_l' => $sessionVol,
                        'source' => 'irrigation',
                        'meta' => json_encode([
                            'generated' => true,
                            'seed' => 'DeviceUsageSeeder',
                            'session_index' => $s,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
