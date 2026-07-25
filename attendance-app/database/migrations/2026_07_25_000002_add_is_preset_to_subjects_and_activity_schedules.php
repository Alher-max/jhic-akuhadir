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
        if (Schema::hasTable('subjects') && !Schema::hasColumn('subjects', 'is_preset')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->boolean('is_preset')->default(false)->after('name');
            });
        }

        if (Schema::hasTable('activity_schedules') && !Schema::hasColumn('activity_schedules', 'is_preset')) {
            Schema::table('activity_schedules', function (Blueprint $table) {
                $table->boolean('is_preset')->default(false)->after('late_tolerance_minutes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'is_preset')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('is_preset');
            });
        }

        if (Schema::hasTable('activity_schedules') && Schema::hasColumn('activity_schedules', 'is_preset')) {
            Schema::table('activity_schedules', function (Blueprint $table) {
                $table->dropColumn('is_preset');
            });
        }
    }
};
