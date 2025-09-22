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
        // ------------------------------------------------------------------
        // REDESAIN SKEMA SENSOR (2025-09)
        // ------------------------------------------------------------------
        // Fokus baru: tabel sensor_data hanya menyimpan METRIK PER PERANGKAT.
        // Kolom global (cahaya, angin) dipindahkan keluar / akan diambil dari
        // sumber eksternal (API cuaca) sehingga dihapus untuk mengurangi noise.
        // Perubahan utama:
        //  - temperature_c -> ground_temperature_c (suhu tanah/permakultur)
        //  - tambah irrigation_usage_total_l (akumulasi penggunaan air)
        //  - tambah battery_voltage_v (tegangan supply sederhana)
        //  - pertahankan hanya total daya INA226 (power_mw) untuk node khusus
        //  - buang water_volume_l (dapat diturunkan dari log irigasi / storage)
        //  - hapus light_lux & wind_speed_ms (bukan per-device lagi)
        // Catatan: Guard Schema::hasColumn digunakan agar migration idempotent
        // saat file ini DIMODIFIKASI (sesuai permintaan) tanpa menambah file baru.
        // ------------------------------------------------------------------
        Schema::table('sensor_data', function (Blueprint $table) {
            // === Redesain 2025-09: fokus kolom per-perangkat (node) ===
            // Ganti temperature_c menjadi ground_temperature_c (jika belum ada)
            if (!Schema::hasColumn('sensor_data', 'ground_temperature_c')) {
                $table->float('ground_temperature_c')->nullable()->after('temperature');
            }
            // Kelembapan tanah dalam persen (baru / dipertahankan)
            if (!Schema::hasColumn('sensor_data', 'soil_moisture_pct')) {
                $table->unsignedTinyInteger('soil_moisture_pct')->nullable()->after('ground_temperature_c');
            }
            // Tinggi air (opsional jika node punya sensor ultrasonic)
            if (!Schema::hasColumn('sensor_data', 'water_height_cm')) {
                $table->unsignedSmallInteger('water_height_cm')->nullable()->after('soil_moisture_pct');
            }
            // Total penggunaan air kumulatif (liter) — menggantikan water_volume_l (yang semula bisa dihitung)
            if (!Schema::hasColumn('sensor_data', 'irrigation_usage_total_l')) {
                $table->decimal('irrigation_usage_total_l', 12, 3)->nullable()->after('water_height_cm');
            }
            // Battery level (tegangan) jika perangkat pakai power measurement sederhana (bukan INA226)
            if (!Schema::hasColumn('sensor_data', 'battery_voltage_v')) {
                $table->decimal('battery_voltage_v', 5, 2)->nullable()->after('irrigation_usage_total_l');
            }
            // INA226 (jika node khusus power). Dibiarkan untuk sementara transisi.
            if (!Schema::hasColumn('sensor_data', 'ina226_bus_voltage_v')) {
                $table->decimal('ina226_bus_voltage_v', 6, 3)->nullable()->after('battery_voltage_v');
                $table->integer('ina226_shunt_voltage_mv')->nullable()->after('ina226_bus_voltage_v');
                $table->decimal('ina226_current_ma', 8, 3)->nullable()->after('ina226_shunt_voltage_mv');
                $table->decimal('ina226_power_mw', 10, 3)->nullable()->after('ina226_current_ma');
            }
            // Timestamp perangkat & flags
            if (!Schema::hasColumn('sensor_data', 'device_ts')) {
                $table->timestamp('device_ts')->nullable()->after('ina226_power_mw');
                $table->unsignedBigInteger('device_ts_unix')->nullable()->after('device_ts');
                $table->json('flags')->nullable()->after('device_ts_unix');
                $table->index(['device_id', 'device_ts']);
            }
            // Hapus kolom global yang sudah dipindah ke environment (jika masih ada)
            if (Schema::hasColumn('sensor_data', 'light_lux')) {
                $table->dropColumn('light_lux');
            }
            if (Schema::hasColumn('sensor_data', 'wind_speed_ms')) {
                $table->dropColumn('wind_speed_ms');
            }
            if (Schema::hasColumn('sensor_data', 'temperature_c') && !Schema::hasColumn('sensor_data', 'ground_temperature_c')) {
                // Rename lama ke baru (fallback)
                $table->renameColumn('temperature_c', 'ground_temperature_c');
            }
            if (Schema::hasColumn('sensor_data', 'water_volume_l')) {
                // Dianggap turunan, dihapus agar tidak redundan
                $table->dropColumn('water_volume_l');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            // Rollback desain: tambah kembali kolom yang dihapus & hapus kolom baru
            if (Schema::hasColumn('sensor_data', 'device_ts')) {
                $table->dropIndex(['device_id', 'device_ts']);
            }
            // Tambah kembali kolom global (untuk backward compatibility)
            if (!Schema::hasColumn('sensor_data', 'light_lux')) {
                $table->unsignedInteger('light_lux')->nullable();
            }
            if (!Schema::hasColumn('sensor_data', 'wind_speed_ms')) {
                $table->decimal('wind_speed_ms', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('sensor_data', 'temperature_c') && Schema::hasColumn('sensor_data', 'ground_temperature_c')) {
                $table->renameColumn('ground_temperature_c', 'temperature_c');
            }
            if (!Schema::hasColumn('sensor_data', 'water_volume_l')) {
                $table->decimal('water_volume_l', 10, 2)->nullable();
            }
            // Hapus kolom baru (jika ada)
            foreach ([
                'ground_temperature_c',
                'soil_moisture_pct',
                'water_height_cm',
                'irrigation_usage_total_l',
                'battery_voltage_v',
                'ina226_bus_voltage_v',
                'ina226_shunt_voltage_mv',
                'ina226_current_ma',
                'ina226_power_mw',
                'device_ts',
                'device_ts_unix',
                'flags',
            ] as $col) {
                if (Schema::hasColumn('sensor_data', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
