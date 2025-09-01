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
        Schema::table('water_storages', function (Blueprint $table) {
            $table->string('zone_name')->after('tank_name')->nullable()->comment('Nama zona/jalur tanaman');
            $table->text('zone_description')->after('zone_name')->nullable()->comment('Deskripsi zona tanaman');
            $table->json('associated_devices')->after('device_id')->nullable()->comment('Node-node tambahan di zona ini');
            $table->decimal('max_daily_usage', 10, 2)->after('current_volume')->nullable()->comment('Estimasi penggunaan maksimal per hari (L)');
            
            // Update status enum untuk menambahkan maintenance
            $table->enum('status', ['normal', 'low', 'empty', 'full', 'maintenance'])
                  ->default('normal')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_storages', function (Blueprint $table) {
            $table->dropColumn([
                'zone_name',
                'zone_description', 
                'associated_devices',
                'max_daily_usage'
            ]);
            
            // Revert status enum
            $table->enum('status', ['normal', 'low', 'empty', 'full'])
                  ->default('normal')
                  ->change();
        });
    }
};
