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
        // Drop and recreate water_storages table to fix structure
        Schema::dropIfExists('water_storages');
        
        Schema::create('water_storages', function (Blueprint $table) {
            $table->id();
            $table->string('tank_name');
            $table->string('zone_name');
            $table->text('zone_description')->nullable();
            $table->decimal('capacity_liters', 10, 2);
            $table->decimal('current_volume_liters', 10, 2);
            $table->decimal('percentage', 5, 2);
            $table->enum('status', ['normal', 'low', 'critical', 'full']);
            $table->unsignedBigInteger('device_id')->nullable();
            
            // Area and irrigation lines fields
            $table->string('area_name')->nullable();
            $table->json('irrigation_lines')->nullable();
            $table->integer('total_lines')->default(0);
            $table->decimal('area_size_sqm', 10, 2)->nullable();
            $table->string('plant_types')->nullable();
            $table->string('irrigation_system_type')->nullable();
            
            $table->timestamps();
            
            // Foreign key will be added later after devices table is created
            // $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
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
