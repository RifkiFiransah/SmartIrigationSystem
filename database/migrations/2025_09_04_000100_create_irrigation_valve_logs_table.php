<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('irrigation_valve_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_valve_id')->constrained('irrigation_valves')->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('node_uid')->index();
            $table->enum('action', [
                'open', 'close', 'toggle_mode',                    // Valve actions
                'device_connect', 'device_disconnect',             // Device connection status
                'schedule_create', 'schedule_update', 'schedule_delete', // Schedule management
                'schedule_execute', 'schedule_complete',           // Schedule execution
                'system_auto_open', 'system_auto_close'           // System automated actions
            ]);
            $table->enum('trigger', [
                'manual', 'auto', 'schedule',                     // Basic triggers
                'api', 'system', 'device_event',                 // System triggers
                'admin_panel', 'mobile_app', 'web_interface'     // Source-based triggers
            ])->default('manual');
            $table->string('user_id')->nullable()->comment('User who triggered the action');
            $table->string('source_ip')->nullable()->comment('IP address of the request');
            $table->integer('duration_seconds')->nullable();
            $table->string('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional context data');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['device_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('trigger');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_valve_logs');
    }
};
