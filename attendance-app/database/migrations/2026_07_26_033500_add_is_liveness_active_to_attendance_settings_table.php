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
        if (!Schema::hasColumn('attendance_settings', 'is_liveness_active')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                $table->boolean('is_liveness_active')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('attendance_settings', 'is_liveness_active')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                $table->dropColumn('is_liveness_active');
            });
        }
    }
};
