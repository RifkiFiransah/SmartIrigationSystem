<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('irrigation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_control_id')->constrained('irrigation_controls')->onDelete('cascade');
            $table->foreignId('irrigation_schedule_id')->nullable()->constrained('irrigation_schedules')->onDelete('set null');
            $table->enum('action', ['start', 'stop', 'pause', 'error', 'manual_override']);
            $table->enum('trigger_type', ['manual', 'schedule', 'sensor', 'emergency', 'api'])->default('manual');
            $table->string('triggered_by')->nullable()->comment('user, system, device_id');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->decimal('water_flow_rate', 8, 2)->nullable()->comment('liter per menit');
            $table->decimal('total_water_used', 10, 2)->nullable()->comment('total liter yang digunakan');
            $table->json('sensor_data_snapshot')->nullable()->comment('snapshot data sensor saat trigger');
            $table->enum('status', ['running', 'completed', 'failed', 'cancelled'])->default('running');
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['irrigation_control_id', 'action']);
            $table->index(['trigger_type', 'status']);
            $table->index(['started_at', 'ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irrigation_logs');
    }
};
