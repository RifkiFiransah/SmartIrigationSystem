<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('water_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_storage_id')->constrained('water_storages')->cascadeOnDelete();
            $table->date('usage_date');
            $table->decimal('volume_used_l', 10, 2); // jumlah air yang dipakai
            $table->string('source', 30)->default('irrigation'); // irrigation, manual, adjust, auto_calc
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['water_storage_id', 'usage_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_usage_logs');
    }
};
