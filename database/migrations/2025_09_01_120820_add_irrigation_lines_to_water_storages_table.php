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
            $table->string('area_name')->after('zone_name')->nullable()->comment('Nama area/blok yang dilayani tangki');
            $table->json('irrigation_lines')->after('area_name')->nullable()->comment('Jalur-jalur irigasi di area ini');
            $table->integer('total_lines')->after('irrigation_lines')->default(0)->comment('Total jalur irigasi');
            $table->decimal('area_size_sqm', 10, 2)->after('total_lines')->nullable()->comment('Luas area dalam meter persegi');
            $table->string('plant_types')->after('area_size_sqm')->nullable()->comment('Jenis tanaman di area ini');
            
            // Rename zone_name to be more specific
            $table->renameColumn('zone_name', 'zone_name'); // Keep as is for now
            $table->text('irrigation_system_type')->after('plant_types')->nullable()->comment('Jenis sistem irigasi (drip, sprinkler, NFT, dll)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_storages', function (Blueprint $table) {
            $table->dropColumn([
                'area_name',
                'irrigation_lines',
                'total_lines',
                'area_size_sqm',
                'plant_types',
                'irrigation_system_type'
            ]);
        });
    }
};
