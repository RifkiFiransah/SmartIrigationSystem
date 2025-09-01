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
        Schema::create('water_storages', function (Blueprint $table) {
            $table->id();
            $table->string('tank_name')->nullable(); // Nama tangki air
            $table->unsignedBigInteger('device_id')->nullable(); // Relasi ke device (opsional)
            $table->decimal('total_capacity', 10, 2); // Kapasitas total dalam liter
            $table->decimal('current_volume', 10, 2)->default(0); // Volume saat ini dalam liter
            $table->decimal('percentage', 5, 2)->virtualAs('ROUND((current_volume / total_capacity) * 100, 2)'); // Persentase otomatis
            $table->string('status')->default('normal'); // normal, low, empty, full
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
            
            // Foreign key constraint (opsional)
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
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
