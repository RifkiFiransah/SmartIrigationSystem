<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('irrigation_valve_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('node_uid')->index();
            $table->time('start_time');
            $table->integer('duration_minutes');
            $table->decimal('water_usage_target_liters', 8, 2)->nullable()->comment('Target penggunaan air dalam liter untuk jadwal ini');
            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            
            // Add performance indexes
            $table->index(['is_active', 'start_time']);
            $table->index('last_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_valve_schedules');
    }
};
