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
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            
            // Legacy fields (maintained for compatibility)
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->float('soil_moisture')->nullable();
            $table->float('water_flow')->nullable();
            $table->float('light_intensity')->nullable();
            
            // Enhanced sensor fields
            $table->float('ground_temperature_c')->nullable();
            $table->unsignedTinyInteger('soil_moisture_pct')->nullable();
            $table->unsignedSmallInteger('water_height_cm')->nullable();
            $table->decimal('irrigation_usage_total_l', 12, 3)->nullable();
            $table->decimal('battery_voltage_v', 5, 2)->nullable();
            
            // Additional sensor types
            $table->decimal('ph_level', 4, 2)->nullable();
            $table->decimal('nitrogen_level', 8, 2)->nullable();
            $table->decimal('phosphorus_level', 8, 2)->nullable();
            $table->decimal('potassium_level', 8, 2)->nullable();
            
            // Power measurement (for nodes with INA226 sensor)
            $table->decimal('ina226_power_mw', 10, 3)->nullable()->comment('Power consumption in milliwatts from INA226 sensor');
            $table->decimal('ina226_current_ma', 10, 3)->nullable()->comment('Current in milliamps from INA226 sensor');
            $table->decimal('ina226_voltage_v', 8, 3)->nullable()->comment('Bus voltage from INA226 sensor');
            
            // Timing and metadata
            $table->timestamp('device_ts')->nullable()->comment('Device timestamp');
            $table->unsignedBigInteger('device_ts_unix')->nullable()->comment('Device timestamp as unix');
            $table->timestamp('recorded_at')->useCurrent();
            $table->string('status', 30)->default('normal');
            $table->json('flags')->nullable()->comment('Quality flags and metadata');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['device_id', 'recorded_at']);
            $table->index(['device_id', 'status']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
