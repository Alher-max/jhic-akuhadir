<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonFormalAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_non_formal_session_mode_setting(): void
    {
        $tenant = Tenant::create([
            'name' => 'LPK Garuda',
            'code' => 'LPKGAR',
            'slug' => 'lpk-garuda',
            'attendance_mode' => 'formal_daily',
            'session_late_tolerance_minutes' => 10,
        ]);

        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Admin',
            'email' => 'operator@lpkgaruda.id',
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
            'attendance_mode' => 'non_formal_session',
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

        $this->assertEquals('non_formal_session', $tenant->attendance_mode);
        $this->assertEquals(15, $tenant->session_late_tolerance_minutes);
    }

    public function test_non_formal_attendance_allows_multiple_sessions_per_day(): void
    {
        $tenant = Tenant::create([
            'name' => 'LPK Garuda',
            'code' => 'LPKGAR',
            'slug' => 'lpk-garuda',
            'attendance_mode' => 'non_formal_session',
            'session_late_tolerance_minutes' => 10,
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Non Formal',
            'email' => 'siswa@lpkgaruda.id',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $schoolClass = SchoolClass::create([
            'tenant_id' => $tenant->id,
            'jenjang' => 'SMK',
            'tingkat' => '10',
            'nama_kelas' => 'Sesi Pagi',
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
            'email' => 'guru@lpkgaruda.id',
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

        // Presensi Sesi 1
        $response1 = $this->actingAs($student)->post(route('member.clock-in'));
        $response1->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'tenant_id' => $tenant->id,
            'class_schedule_id' => $session1->id,
            'date' => now()->format('Y-m-d'),
        ]);

        // Simulated Presensi Sesi 2 (Multiple Sessions per day)
        $attendanceService = app(\App\Services\AttendanceService::class);
        $this->assertFalse($attendanceService->hasAttendedSession($student, now()->format('Y-m-d'), $session2->id));

        Attendance::create([
            'user_id' => $student->id,
            'tenant_id' => $tenant->id,
            'class_schedule_id' => $session2->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => 'present',
        ]);

        $this->assertTrue($attendanceService->hasAttendedSession($student, now()->format('Y-m-d'), $session2->id));
    }
}
