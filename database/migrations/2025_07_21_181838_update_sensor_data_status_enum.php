<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First update existing values
        DB::table('sensor_data')->where('status', 'alert')->update(['status' => 'temp_alert']);
        DB::table('sensor_data')->where('status', 'critical')->update(['status' => 'temp_critical']);
        
        // Drop the enum constraint and recreate with new values
        DB::statement("ALTER TABLE sensor_data MODIFY COLUMN status VARCHAR(20) DEFAULT 'normal'");
        
        // Update to Bahasa Indonesia values
        DB::table('sensor_data')->where('status', 'temp_alert')->update(['status' => 'peringatan']);
        DB::table('sensor_data')->where('status', 'temp_critical')->update(['status' => 'kritis']);
        
        // Recreate as enum with Bahasa Indonesia values
        DB::statement("ALTER TABLE sensor_data MODIFY COLUMN status ENUM('normal', 'peringatan', 'kritis') DEFAULT 'normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::table('sensor_data')->where('status', 'peringatan')->update(['status' => 'temp_alert']);
        DB::table('sensor_data')->where('status', 'kritis')->update(['status' => 'temp_critical']);
        
        DB::statement("ALTER TABLE sensor_data MODIFY COLUMN status VARCHAR(20) DEFAULT 'normal'");
        
        DB::table('sensor_data')->where('status', 'temp_alert')->update(['status' => 'alert']);
        DB::table('sensor_data')->where('status', 'temp_critical')->update(['status' => 'critical']);
        
        DB::statement("ALTER TABLE sensor_data MODIFY COLUMN status ENUM('normal', 'alert', 'critical') DEFAULT 'normal'");
    }
};
