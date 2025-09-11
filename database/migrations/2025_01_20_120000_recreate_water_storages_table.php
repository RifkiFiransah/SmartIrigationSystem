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
        // Recreate water_storages with final schema
        Schema::dropIfExists('water_storages');

        Schema::create('water_storages', function (Blueprint $table) {
            $table->id();
            $table->string('tank_name');
            $table->string('zone_name')->nullable();
            $table->text('zone_description')->nullable();
            // Keep decimals to match seeders and model casts
            $table->decimal('capacity_liters', 10, 2);
            $table->decimal('current_volume_liters', 10, 2)->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('status', 20)->default('normal'); // normal|low|full|maintenance
            $table->unsignedBigInteger('device_id')->nullable()->index();
            // Area and irrigation details used by seeders/UI
            $table->string('area_name')->nullable();
            $table->json('irrigation_lines')->nullable();
            $table->integer('total_lines')->default(0);
            $table->decimal('area_size_sqm', 10, 2)->nullable();
            $table->string('plant_types')->nullable();
            $table->string('irrigation_system_type')->nullable();
            $table->json('associated_devices')->nullable();
            $table->decimal('max_daily_usage', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            // Avoid FK here to prevent ordering issues; keep index only
            // Add FK in a later migration if desired
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_storages');
    }
};
