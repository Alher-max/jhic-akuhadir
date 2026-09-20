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

class KbmClassAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-21 07:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_teacher_can_record_kbm_class_attendance(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Yogyakarta',
            'slug' => 'sman2yogyakarta',
            'code' => 'SMAN2YOGYA',
            'subdomain' => 'sman2yogyakarta',
            'onboarding_completed' => true,
        ]);

        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pak Guru Pengampu',
            'email' => 'guru.pengampu@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $schoolClass = SchoolClass::create([
            'tenant_id' => $tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => '10',
            'nama_kelas' => 'X IPA 1',
        ]);

        $subject = Subject::create([
            'tenant_id' => $tenant->id,
            'name' => 'Matematika',
            'code' => 'MTK-10',
        ]);

        $schedule = ClassSchedule::create([
            'tenant_id' => $tenant->id,
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
        ]);

        $student1 = User::create([
            'tenant_id' => $tenant->id,
            'class_id' => $schoolClass->id,
            'name' => 'Siswa Hadir',
            'email' => 'siswa1@sman2yogyakarta.sch.id',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $student2 = User::create([
            'tenant_id' => $tenant->id,
            'class_id' => $schoolClass->id,
            'name' => 'Siswa Sakit',
            'email' => 'siswa2@sman2yogyakarta.sch.id',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->from(route('teacher.dashboard'))
            ->actingAs($teacher)
            ->post(route('teacher.kbm-attendance'), [
                'schedule_id' => $schedule->id,
                'attendances' => [
                    [
                        'student_id' => $student1->id,
                        'status' => 'present',
                        'notes' => 'Hadir tepat waktu',
                    ],
                    [
                        'student_id' => $student2->id,
                        'status' => 'sick',
                        'notes' => 'Surat dokter terlampir',
                    ],
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'tenant_id' => $tenant->id,
            'user_id' => $student1->id,
            'class_schedule_id' => $schedule->id,
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'tenant_id' => $tenant->id,
            'user_id' => $student2->id,
            'class_schedule_id' => $schedule->id,
            'status' => 'sick',
        ]);
    }
}
