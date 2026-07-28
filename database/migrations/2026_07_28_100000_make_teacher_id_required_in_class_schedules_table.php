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
        // First, check if there are any records with NULL teacher_id and populate them with the class's homeroom teacher or first teacher in tenant
        $nullSchedules = DB::table('class_schedules')->whereNull('teacher_id')->get();
        foreach ($nullSchedules as $cs) {
            $class = DB::table('school_classes')->where('id', $cs->class_id)->first();
            $teacherId = $class?->wali_kelas_id;

            if (!$teacherId) {
                $teacher = DB::table('users')
                    ->where('tenant_id', $cs->tenant_id)
                    ->whereIn('role', ['guru', 'wali_kelas', 'guru_mapel', 'operator', 'staff'])
                    ->first();
                $teacherId = $teacher?->id;
            }

            if ($teacherId) {
                DB::table('class_schedules')->where('id', $cs->id)->update(['teacher_id' => $teacherId]);
            } else {
                DB::table('class_schedules')->where('id', $cs->id)->delete();
            }
        }

        Schema::table('class_schedules', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->change();
        });
    }
};
