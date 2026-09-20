<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE class_schedules DROP CONSTRAINT IF EXISTS class_schedules_day_name_check');
        DB::statement("
            ALTER TABLE class_schedules
            ADD CONSTRAINT class_schedules_day_name_check
            CHECK (day_name IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'))
        ");

        DB::statement('ALTER TABLE attendance_schedules DROP CONSTRAINT IF EXISTS attendance_schedules_day_name_check');
        DB::statement("
            ALTER TABLE attendance_schedules
            ADD CONSTRAINT attendance_schedules_day_name_check
            CHECK (day_name IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE class_schedules DROP CONSTRAINT IF EXISTS class_schedules_day_name_check');
        DB::statement("
            ALTER TABLE class_schedules
            ADD CONSTRAINT class_schedules_day_name_check
            CHECK (day_name IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'))
        ");

        DB::statement('ALTER TABLE attendance_schedules DROP CONSTRAINT IF EXISTS attendance_schedules_day_name_check');
        DB::statement("
            ALTER TABLE attendance_schedules
            ADD CONSTRAINT attendance_schedules_day_name_check
            CHECK (day_name IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'))
        ");
    }
};
