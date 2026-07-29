<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonFormalAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_unified_session_based_mode_setting_by_operator(): void
    {
        $tenant = Tenant::create([
            'name' => 'Sekolah Garudayan',
            'code' => 'GARUDA',
            'slug' => 'sekolah-garudayan',
            'attendance_mode' => 'daily_arrival',
            'session_late_tolerance_minutes' => 10,
            'onboarding_completed' => true,
        ]);

        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Admin',
            'email' => 'operator@garuda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $schedule = \App\Models\AttendanceSchedule::create([
            'tenant_id' => $tenant->id,
            'day_name' => 'Senin',
            'time_in' => '07:00:00',
            'time_out' => '14:00:00',
            'late_tolerance_minutes' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($operator)->post(route('attendance-schedules.update'), [
            'attendance_mode' => 'session_based',
            'session_late_tolerance_minutes' => 15,
            'schedules' => [
                [
                    'id' => $schedule->id,
                    'time_in' => '07:00:00',
                    'time_out' => '14:00:00',
                    'late_tolerance_minutes' => 15,
                    'is_active' => 1,
                ]
            ]
        ]);

        $response->assertRedirect();
        $tenant->refresh();

        $this->assertEquals('session_based', $tenant->attendance_mode);
        $this->assertEquals(15, $tenant->session_late_tolerance_minutes);
    }

    public function test_teacher_role_cannot_modify_attendance_schedules_or_modes(): void
    {
        $tenant = Tenant::create([
            'name' => 'Sekolah Garudayan',
            'code' => 'GARUDA2',
            'slug' => 'sekolah-garudayan-2',
            'attendance_mode' => 'daily_arrival',
            'onboarding_completed' => true,
        ]);

        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Guru Mata Pelajaran',
            'email' => 'guru@garuda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $schedule = \App\Models\AttendanceSchedule::create([
            'tenant_id' => $tenant->id,
            'day_name' => 'Senin',
            'time_in' => '07:00:00',
            'time_out' => '14:00:00',
            'late_tolerance_minutes' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher)->post(route('attendance-schedules.update'), [
            'attendance_mode' => 'session_based',
            'schedules' => [
                [
                    'id' => $schedule->id,
                    'time_in' => '07:00:00',
                    'time_out' => '14:00:00',
                    'late_tolerance_minutes' => 15,
                    'is_active' => 1,
                ]
            ]
        ]);

        $response->assertStatus(403);
    }

    public function test_session_based_attendance_records_dual_presence_properly(): void
    {
        $tenant = Tenant::create([
            'name' => 'Sekolah Garudayan',
            'code' => 'GARUDA3',
            'slug' => 'sekolah-garudayan-3',
            'attendance_mode' => 'session_based',
            'session_late_tolerance_minutes' => 10,
            'onboarding_completed' => true,
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Utama',
            'email' => 'siswa@garuda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $schoolClass = SchoolClass::create([
            'tenant_id' => $tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => '10',
            'nama_kelas' => 'X IPA 1',
        ]);
        $student->update(['class_id' => $schoolClass->id]);

        $subject1 = Subject::create([
            'tenant_id' => $tenant->id,
            'code' => 'MAT01',
            'name' => 'Matematika Terapan',
        ]);

        $subject2 = Subject::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROG01',
            'name' => 'Pemrograman Dasar',
        ]);

        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayIndo = $daysMap[(int)now()->format('N')];

        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Guru Pengampu',
            'email' => 'guru2@garuda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $session1 = ClassSchedule::create([
            'tenant_id' => $tenant->id,
            'class_id' => $schoolClass->id,
            'subject_id' => $subject1->id,
            'teacher_id' => $teacher->id,
            'day_name' => $todayIndo,
            'period_number' => 1,
            'start_time' => '07:00:00',
            'end_time' => '09:00:00',
        ]);

        $session2 = ClassSchedule::create([
            'tenant_id' => $tenant->id,
            'class_id' => $schoolClass->id,
            'subject_id' => $subject2->id,
            'teacher_id' => $teacher->id,
            'day_name' => $todayIndo,
            'period_number' => 2,
            'start_time' => '09:30:00',
            'end_time' => '11:30:00',
        ]);

        // Travel to 07:15 today for deterministic session 1 clock-in
        Carbon::setTestNow(now()->setTime(7, 15, 0));

        // Presensi Sesi 1
        $response1 = $this->actingAs($student)->post(route('member.clock-in'));
        $response1->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'tenant_id' => $tenant->id,
            'attendance_type' => 'class',
            'class_schedule_id' => $session1->id,
            'date' => now()->format('Y-m-d'),
        ]);

        // Simulated Presensi Sesi 2
        $attendanceService = app(\App\Services\AttendanceService::class);
        $this->assertFalse($attendanceService->hasAttendedClassSession($student, now()->format('Y-m-d'), $session2->id));

        Attendance::create([
            'user_id' => $student->id,
            'tenant_id' => $tenant->id,
            'attendance_type' => 'class',
            'class_schedule_id' => $session2->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => 'present',
        ]);

        $this->assertTrue($attendanceService->hasAttendedClassSession($student, now()->format('Y-m-d'), $session2->id));

        Carbon::setTestNow();
    }
}
