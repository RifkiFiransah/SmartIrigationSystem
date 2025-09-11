<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('water_storages', function (Blueprint $table) {
            if (!Schema::hasColumn('water_storages', 'height_cm')) {
                $table->decimal('height_cm', 8, 2)->nullable()->after('capacity_liters');
            }
            if (!Schema::hasColumn('water_storages', 'calibration_offset_cm')) {
                $table->decimal('calibration_offset_cm', 8, 2)->default(0)->after('height_cm');
            }
            if (!Schema::hasColumn('water_storages', 'last_height_cm')) {
                $table->decimal('last_height_cm', 8, 2)->nullable()->after('calibration_offset_cm');
            }
            if (!Schema::hasColumn('water_storages', 'last_height_recorded_at')) {
                $table->timestamp('last_height_recorded_at')->nullable()->after('last_height_cm');
            }
        });
    }

    public function down(): void
    {
        Schema::table('water_storages', function (Blueprint $table) {
            if (Schema::hasColumn('water_storages', 'last_height_recorded_at')) {
                $table->dropColumn('last_height_recorded_at');
            }
            if (Schema::hasColumn('water_storages', 'last_height_cm')) {
                $table->dropColumn('last_height_cm');
            }
            if (Schema::hasColumn('water_storages', 'calibration_offset_cm')) {
                $table->dropColumn('calibration_offset_cm');
            }
            if (Schema::hasColumn('water_storages', 'height_cm')) {
                $table->dropColumn('height_cm');
            }
        });
    }
};
