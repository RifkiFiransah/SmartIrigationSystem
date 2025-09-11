<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('irrigation_valves', function (Blueprint $table) {
            $table->id();
            $table->string('node_uid')->unique();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->unsignedInteger('gpio_pin')->nullable();
            $table->enum('status', ['open', 'closed'])->default('closed');
            $table->enum('mode', ['auto', 'manual'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_open_at')->nullable();
            $table->timestamp('last_close_at')->nullable();
            $table->timestamp('last_evaluated_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_valves');
    }
};
