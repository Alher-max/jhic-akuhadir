<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE tenants DROP CONSTRAINT IF EXISTS tenants_attendance_mode_check");
            DB::statement("ALTER TABLE tenants ALTER COLUMN attendance_mode TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE tenants ALTER COLUMN attendance_mode SET DEFAULT 'daily_arrival'");
        } else {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'attendance_mode')) {
                    $table->string('attendance_mode')->default('daily_arrival')->change();
                }
            });
        }

        // Migrate existing legacy mode values in tenants table
        DB::table('tenants')
            ->where('attendance_mode', 'formal_daily')
            ->update(['attendance_mode' => 'daily_arrival']);

        DB::table('tenants')
            ->where('attendance_mode', 'non_formal_session')
            ->update(['attendance_mode' => 'session_based']);

        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'attendance_type')) {
                $table->string('attendance_type')->default('school')->after('tenant_id');
            }
        });

        DB::table('attendances')
            ->whereNotNull('class_schedule_id')
            ->update(['attendance_type' => 'class']);

        DB::table('attendances')
            ->whereNull('class_schedule_id')
            ->update(['attendance_type' => 'school']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'attendance_type')) {
                $table->dropColumn('attendance_type');
            }
        });
    }
};
