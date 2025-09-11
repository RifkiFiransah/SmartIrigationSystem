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
        Schema::table('sensor_data', function (Blueprint $table) {
            // New environmental metrics
            $table->float('temperature_c')->nullable()->after('temperature'); // Celsius from new sensor
            $table->unsignedTinyInteger('soil_moisture_pct')->nullable()->after('temperature_c'); // % soil moisture

            // Water level and volume
            $table->unsignedSmallInteger('water_height_cm')->nullable()->after('soil_moisture_pct'); // ultrasonic height (cm)
            $table->decimal('water_volume_l', 10, 2)->nullable()->after('water_height_cm'); // estimated/actual liters

            // Light and wind
            $table->unsignedInteger('light_lux')->nullable()->after('light_intensity'); // precise lux
            $table->decimal('wind_speed_ms', 5, 2)->nullable()->after('light_lux'); // m/s

            // INA226 electrical metrics
            $table->decimal('ina226_bus_voltage_v', 6, 3)->nullable()->after('wind_speed_ms');
            $table->integer('ina226_shunt_voltage_mv')->nullable()->after('ina226_bus_voltage_v');
            $table->decimal('ina226_current_ma', 8, 3)->nullable()->after('ina226_shunt_voltage_mv');
            $table->decimal('ina226_power_mw', 10, 3)->nullable()->after('ina226_current_ma');

            // Device-side timestamp (RTC) and flags
            $table->timestamp('device_ts')->nullable()->after('ina226_power_mw');
            $table->unsignedBigInteger('device_ts_unix')->nullable()->after('device_ts');
            $table->json('flags')->nullable()->after('device_ts_unix');

            // Time-series index for device timeline queries
            $table->index(['device_id', 'device_ts']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex(['device_id', 'device_ts']);

            // Drop added columns
            $table->dropColumn([
                'temperature_c',
                'soil_moisture_pct',
                'water_height_cm',
                'water_volume_l',
                'light_lux',
                'wind_speed_ms',
                'ina226_bus_voltage_v',
                'ina226_shunt_voltage_mv',
                'ina226_current_ma',
                'ina226_power_mw',
                'device_ts',
                'device_ts_unix',
                'flags',
            ]);
        });
    }
};
