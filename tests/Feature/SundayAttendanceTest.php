<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSetting;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SundayAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-20 07:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_student_can_clock_in_and_out_on_sunday_when_daily_schedule_is_active(): void
    {
        $tenant = $this->createTenant(['attendance_mode' => 'daily_arrival']);
        $student = $this->createStudent($tenant);
        $this->enablePwa($tenant);
        AttendanceSchedule::create([
            'tenant_id' => $tenant->id,
            'day_name' => 'Minggu',
            'time_in' => '07:00:00',
            'time_out' => '12:00:00',
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->post(route('member.clock-in'))
            ->assertSessionHas('success');
        $attendance = Attendance::where('user_id', $student->id)
            ->where('attendance_type', 'school')
            ->whereDate('date', '2026-09-20')
            ->first();
        $this->assertNotNull($attendance);
        $this->assertSame('2026-09-20', $attendance->date->format('Y-m-d'));

        $this->actingAs($student)
            ->post(route('member.clock-out'))
            ->assertSessionHas('success');
        $this->assertFalse(Attendance::where('user_id', $student->id)
            ->whereDate('date', '2026-09-20')
            ->whereNull('clock_out')
            ->exists());
    }

    public function test_teacher_and_student_can_record_sunday_kbm_attendance(): void
    {
        $tenant = $this->createTenant(['attendance_mode' => 'session_based']);
        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Guru Minggu',
            'email' => 'guru.minggu@example.test',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $class = SchoolClass::create([
            'tenant_id' => $tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => '10',
            'nama_kelas' => 'X Minggu',
        ]);
        $subject = Subject::create([
            'tenant_id' => $tenant->id,
            'name' => 'Kegiatan Minggu',
            'code' => 'KBM-M',
        ]);
        $schedule = ClassSchedule::create([
            'tenant_id' => $tenant->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_name' => 'Minggu',
            'period_number' => 1,
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
        ]);
        $student = $this->createStudent($tenant, ['class_id' => $class->id]);
        $this->enablePwa($tenant);

        $this->actingAs($teacher)
            ->post(route('teacher.kbm-attendance'), [
                'schedule_id' => $schedule->id,
                'attendances' => [[
                    'student_id' => $student->id,
                    'status' => 'present',
                ]],
            ])
            ->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $student->id)
            ->where('class_schedule_id', $schedule->id)
            ->where('attendance_type', 'class')
            ->whereDate('date', '2026-09-20')
            ->first();
        $this->assertNotNull($attendance);
        $this->assertSame('2026-09-20', $attendance->date->format('Y-m-d'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'class_schedule_id' => $schedule->id,
            'status' => 'present',
        ]);
    }

    public function test_sunday_attendance_is_rejected_without_active_day_or_official_schedule(): void
    {
        $tenant = $this->createTenant(['attendance_mode' => 'daily_arrival']);
        $student = $this->createStudent($tenant);
        $this->enablePwa($tenant);

        $this->actingAs($student)
            ->postJson(route('pwa.store'))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertFalse(Attendance::where('user_id', $student->id)
            ->whereDate('date', '2026-09-20')
            ->exists());
    }

    private function createTenant(array $attributes = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Tenant Minggu',
            'slug' => 'tenant-minggu-' . uniqid(),
            'code' => 'MINGGU' . random_int(100, 999),
            'subdomain' => 'minggu-' . uniqid(),
            'onboarding_completed' => true,
            'working_days' => [],
        ], $attributes));
    }

    private function createStudent(Tenant $tenant, array $attributes = []): User
    {
        return User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Minggu',
            'email' => uniqid('siswa-') . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
            'email_verified_at' => now(),
        ], $attributes));
    }

    private function enablePwa(Tenant $tenant): void
    {
        AttendanceSetting::create([
            'tenant_id' => $tenant->id,
            'method_pwa' => true,
        ]);
    }
}
