<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('irrigation_daily_plans', function (Blueprint $table) {
            $table->id();
            $table->date('plan_date')->unique();
            $table->decimal('base_total_volume_l', 10, 2)->default(0);
            $table->decimal('adjusted_total_volume_l', 10, 2)->default(0);
            $table->json('adjustment_factors')->nullable();
            $table->string('status')->default('generated'); // generated | running | completed | canceled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_daily_plans');
    }
};
