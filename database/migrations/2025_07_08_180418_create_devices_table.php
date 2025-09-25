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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('device_name')->unique();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('valve_state', ['open', 'closed'])->default('closed');
            $table->timestamp('valve_state_changed_at')->nullable();
            $table->enum('connection_state', ['online', 'offline'])->default('offline');
            $table->enum('connection_state_source', ['auto', 'manual'])->default('auto');
            $table->timestamp('last_seen_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['is_active', 'connection_state']);
            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
