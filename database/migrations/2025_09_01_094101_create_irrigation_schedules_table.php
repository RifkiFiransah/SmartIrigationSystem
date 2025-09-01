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
        Schema::create('irrigation_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_name');
            $table->foreignId('irrigation_control_id')->constrained('irrigation_controls')->onDelete('cascade');
            $table->enum('schedule_type', ['daily', 'weekly', 'custom', 'sensor_based'])->default('daily');
            $table->time('start_time');
            $table->integer('duration_minutes');
            $table->json('days_of_week')->nullable()->comment('array hari dalam seminggu [1,2,3] untuk weekly');
            $table->json('trigger_conditions')->nullable()->comment('kondisi sensor untuk trigger');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['irrigation_control_id', 'is_active']);
            $table->index(['schedule_type', 'is_enabled']);
            $table->index('next_run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irrigation_schedules');
    }
};
