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
        Schema::create('irrigation_controls', function (Blueprint $table) {
            $table->id();
            $table->string('control_name');
            $table->string('control_type')->comment('pump, valve, motor'); // jenis kontrol
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->string('pin_number')->nullable()->comment('GPIO pin untuk hardware'); // pin untuk hardware
            $table->enum('status', ['on', 'off', 'auto', 'manual', 'error'])->default('off');
            $table->enum('mode', ['auto', 'manual'])->default('manual');
            $table->integer('duration_minutes')->nullable()->comment('durasi dalam menit untuk mode auto');
            $table->time('last_activated_at')->nullable();
            $table->time('last_deactivated_at')->nullable();
            $table->json('settings')->nullable()->comment('pengaturan tambahan');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'status']);
            $table->index(['control_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irrigation_controls');
    }
};
