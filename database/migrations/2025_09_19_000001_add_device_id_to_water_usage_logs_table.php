<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('water_usage_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('water_usage_logs', 'device_id')) {
                $table->foreignId('device_id')->nullable()->after('water_storage_id')->constrained('devices')->nullOnDelete();
                $table->index(['device_id', 'usage_date']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('water_usage_logs', function (Blueprint $table) {
            if (Schema::hasColumn('water_usage_logs', 'device_id')) {
                $table->dropForeign(['device_id']);
                $table->dropIndex(['device_id', 'usage_date']);
                $table->dropColumn('device_id');
            }
        });
    }
};
