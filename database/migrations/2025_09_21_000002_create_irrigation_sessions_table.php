<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('irrigation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_daily_plan_id')->constrained('irrigation_daily_plans')->onDelete('cascade');
            $table->unsignedInteger('session_index');
            $table->time('scheduled_time');
            $table->decimal('planned_volume_l', 10, 2)->default(0);
            $table->decimal('adjusted_volume_l', 10, 2)->nullable();
            $table->decimal('actual_volume_l', 10, 2)->nullable();
            $table->string('status')->default('pending'); // pending | running | completed | skipped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            // Short custom name to avoid MySQL 64-char limit
            $table->unique(['irrigation_daily_plan_id','session_index'], 'plan_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_sessions');
    }
};
