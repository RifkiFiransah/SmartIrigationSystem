<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('irrigation_valve_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_valve_id')->constrained('irrigation_valves')->onDelete('cascade');
            $table->string('node_uid')->index();
            $table->enum('action', ['open', 'close', 'toggle_mode']);
            $table->enum('trigger', ['manual', 'auto', 'schedule'])->default('manual');
            $table->integer('duration_seconds')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_valve_logs');
    }
};
