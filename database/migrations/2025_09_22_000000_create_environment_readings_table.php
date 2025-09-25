<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environment_readings', function (Blueprint $table) {
            $table->id();
            // Single farm (no site_id needed now) – future-proof optional
            // $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('recorded_at')->index();
            $table->unsignedInteger('light_lux')->nullable();
            $table->decimal('wind_speed_ms', 5, 2)->nullable();
            $table->decimal('external_temp_c', 5, 2)->nullable();
            $table->unsignedTinyInteger('external_humidity_pct')->nullable();
            $table->decimal('rainfall_mm', 6, 2)->nullable();
            $table->enum('source', ['local_sensor','bmkg_api'])->default('local_sensor')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Optional: move data from sensor_data into environment_readings (only if existing)
        if (Schema::hasTable('sensor_data') && Schema::hasColumn('sensor_data', 'light_lux')) {
            // We only migrate distinct timestamps (coarse). Use raw query for portability.
            DB::statement("INSERT INTO environment_readings (recorded_at, light_lux, wind_speed_ms, created_at, updated_at, source)
                SELECT DISTINCT recorded_at, light_lux, wind_speed_ms, NOW(), NOW(), 'local_sensor'
                FROM sensor_data
                WHERE (light_lux IS NOT NULL OR wind_speed_ms IS NOT NULL)");
        }

        // Modify sensor_data: drop global columns (now in environment_readings)
        Schema::table('sensor_data', function (Blueprint $table) {
            if (Schema::hasColumn('sensor_data', 'light_lux')) $table->dropColumn('light_lux');
            if (Schema::hasColumn('sensor_data', 'wind_speed_ms')) $table->dropColumn('wind_speed_ms');
        });
    }

    public function down(): void
    {
        // Add columns back to sensor_data
        Schema::table('sensor_data', function (Blueprint $table) {
            if (! Schema::hasColumn('sensor_data', 'light_lux')) {
                $table->unsignedInteger('light_lux')->nullable()->after('water_volume_l');
            }
            if (! Schema::hasColumn('sensor_data', 'wind_speed_ms')) {
                $table->decimal('wind_speed_ms', 5, 2)->nullable()->after('light_lux');
            }
        });

        Schema::dropIfExists('environment_readings');
    }
};
