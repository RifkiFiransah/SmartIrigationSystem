<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Device extends Model
{
    use HasFactory;

    protected $guarded = ['device_id'];

    // Tambahkan fillable eksplisit agar kolom baru bisa diisi mass assignment bila diperlukan
    protected $fillable = [
        'device_id','device_name','location','is_active',
        'valve_state','valve_state_changed_at',
        'connection_state','connection_state_source','last_seen_at',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valve_state_changed_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public static function boot(){
        parent::boot();

        static::creating(function ($device) {
            if(empty($device->device_id)){
                $device->device_id = 'DEVICE_' . str_pad((string)(self::max('id') + 1), 3, '0', STR_PAD_LEFT);
            }
            // default values jika belum diisi
            $device->valve_state = $device->valve_state ?? 'closed';
            $device->connection_state = $device->connection_state ?? 'offline';
            $device->connection_state_source = $device->connection_state_source ?? 'auto';
        });
    }

    public function SensorData() : HasMany
    {
        return $this->hasMany(SensorData::class);
    }

    public function waterStorages() : HasMany
    {
        return $this->hasMany(WaterStorage::class);
    }

    public function waterUsageLogs() : HasMany
    {
        return $this->hasMany(WaterUsageLog::class);
    }
    
    public function irrigationValves() : HasMany
    {
        return $this->hasMany(\App\Models\IrrigationValve::class);
    }

    // ---------------- Custom Methods -----------------
    
    /**
     * Update device connection state and log the change
     */
    public function updateConnectionState(string $newState, string $source = 'system'): void
    {
        $oldState = $this->connection_state;
        
        if ($oldState !== $newState) {
            $this->update([
                'connection_state' => $newState,
                'connection_state_source' => $source,
                'last_seen_at' => $newState === 'connected' ? now() : $this->last_seen_at,
            ]);
            
            // Log the connection state change
            $this->logConnectionChange($oldState, $newState, $source);
        }
    }
    
    /**
     * Log device connection state changes
     */
    protected function logConnectionChange(string $oldState, string $newState, string $source): void
    {
        try {
            $logService = app(\App\Services\IrrigationValveLogService::class);
            
            $action = match($newState) {
                'connected', 'online' => 'device_connect',
                'disconnected', 'offline' => 'device_disconnect',
                default => 'device_connect', // fallback
            };
            
            $trigger = match($source) {
                'api' => 'api',
                'manual' => 'manual',
                'system' => 'system',
                'auto' => 'device_event',
                default => 'system',
            };
            
            $logService->logDeviceConnection($this, $action, $trigger, [
                'old_state' => $oldState,
                'new_state' => $newState,
                'source' => $source,
            ]);
        } catch (\Throwable $e) {
            // Log error silently, don't break the connection update
            logger()->error('Failed to log device connection change', [
                'device_id' => $this->id,
                'old_state' => $oldState,
                'new_state' => $newState,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set connection state - alias for updateConnectionState for backward compatibility
     */
    public function setConnectionState(string $newState, string $source = 'system'): void
    {
        $this->updateConnectionState($newState, $source);
    }

    public function toggleValve(?string $to = null): void
    {
        $oldState = $this->valve_state;
        $next = $to ?? ($this->valve_state === 'open' ? 'closed' : 'open');
        if(!in_array($next, ['open','closed'])) return;
        
        // Calculate duration if closing (from open to closed)
        $durationSeconds = null;
        if ($oldState === 'open' && $next === 'closed' && $this->valve_state_changed_at) {
            $startTime = strtotime($this->valve_state_changed_at);
            $endTime = time();
            $durationSeconds = $endTime - $startTime;
        }
        
        $this->update([
            'valve_state' => $next,
            'valve_state_changed_at' => now(),
        ]);
        
        // Record valve toggle log with duration
        $this->recordValveLog($oldState, $next, 'admin_panel', $durationSeconds);
    }
    
    protected function recordValveLog(string $oldState, string $newState, string $trigger = 'manual', ?int $durationSeconds = null): void
    {
        try {
            // Find or create corresponding irrigation valve
            $irrigationValve = \App\Models\IrrigationValve::where('device_id', $this->id)->first();
            
            if ($irrigationValve) {
                // Update irrigation valve status to match device
                $irrigationValve->update([
                    'status' => $newState,
                    'last_open_at' => $newState === 'open' ? now() : $irrigationValve->last_open_at,
                    'last_close_at' => $newState === 'closed' ? now() : $irrigationValve->last_close_at,
                ]);
                
                // Use new logging service if available
                try {
                    $logService = app(\App\Services\IrrigationValveLogService::class);
                    // Convert valve states to logging service actions
                    $actionForLogging = $newState === 'open' ? 'open' : 'close';
                    $logService->logValveControl($irrigationValve, $actionForLogging, $trigger, $durationSeconds, [
                        'device_toggle' => true,
                        'old_state' => $oldState,
                        'new_state' => $newState,
                    ]);
                } catch (\Throwable $e) {
                    // Fallback to simple logging
                    if (class_exists(\App\Models\IrrigationValveLog::class)) {
                        \App\Models\IrrigationValveLog::create([
                            'irrigation_valve_id' => $irrigationValve->id,
                            'node_uid' => $irrigationValve->node_uid,
                            'action' => $newState === 'open' ? 'open' : 'close', // Convert to logging format
                            'trigger' => $trigger,
                            'duration_seconds' => $durationSeconds,
                            'notes' => "Device valve toggled from {$oldState} to {$newState}",
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log error but don't break the valve toggle
            Log::warning("Failed to log valve toggle for device {$this->id}: " . $e->getMessage());
        }
    }



    public function autoHeartbeat(): void
    {
        // Jika manual override sedang aktif (source manual) jangan paksa offline->online? tetap izinkan online, tapi tidak ubah source.
        $this->update([
            'connection_state' => 'online',
            'last_seen_at' => now(),
            'connection_state_source' => $this->connection_state_source === 'manual' ? 'manual' : 'auto',
        ]);
    }
}
