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
        Schema::table('activity_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_schedules', 'day_name')) {
                $table->string('day_name')->default('Senin')->after('name');
            }
            if (!Schema::hasColumn('activity_schedules', 'target_scope')) {
                $table->string('target_scope')->default('all')->after('late_tolerance_minutes');
            }
            if (!Schema::hasColumn('activity_schedules', 'target_class_ids')) {
                $table->json('target_class_ids')->nullable()->after('target_scope');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('activity_schedules', 'day_name')) {
                $table->dropColumn('day_name');
            }
            if (Schema::hasColumn('activity_schedules', 'target_scope')) {
                $table->dropColumn('target_scope');
            }
            if (Schema::hasColumn('activity_schedules', 'target_class_ids')) {
                $table->dropColumn('target_class_ids');
            }
        });
    }
};
