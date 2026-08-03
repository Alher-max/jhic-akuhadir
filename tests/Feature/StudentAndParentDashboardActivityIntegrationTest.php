<?php

namespace Tests\Feature;

use App\Models\ActivitySchedule;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAndParentDashboardActivityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $teacher;
    protected User $parent;
    protected User $student;
    protected SchoolClass $schoolClass;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Bandung',
            'code' => '20264001',
            'slug' => 'sman1bdg',
            'onboarding_completed' => true,
        ]);

        $this->teacher = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'teacher',
            'name' => 'Guru Pengampu',
        ]);

        $this->parent = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'parent',
            'name' => 'Orang Tua Siswa',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $this->schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => 'X IPA 1',
            'wali_kelas_id' => $this->teacher->id,
        ]);

        $this->student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'name' => 'Siswa Cerdas',
            'parent_id' => $this->parent->id,
            'class_id' => $this->schoolClass->id,
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $this->parent->students()->attach($this->student->id, ['relationship' => 'Orang Tua']);

        $this->subject = Subject::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Fisika Dasar',
            'code' => 'FIS01',
        ]);
    }

    /**
     * Test: Dasbor Siswa menampilkan jadwal gabungan KBM dan Kegiatan/Ekskul yang relevan.
     */
    public function test_student_dashboard_displays_merged_kbm_and_relevant_activity_schedules(): void
    {
        $attendanceService = app(\App\Services\AttendanceService::class);
        $todayDayName = $attendanceService->getDayNameInIndonesian(Carbon::now());

        // 1. KBM Schedule
        ClassSchedule::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'day_name' => $todayDayName,
            'period_number' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:15:00',
        ]);

        // 2. Activity Schedule (target_scope = 'all')
        ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->teacher->id,
            'name' => 'Upacara Pagi',
            'day_name' => $todayDayName,
            'start_time' => '07:00:00',
            'end_time' => '07:30:00',
            'target_scope' => 'all',
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Fisika Dasar');
        $response->assertSee('Upacara Pagi');
    }

    /**
     * Test: Dasbor Siswa memfilter kegiatan yang bukan diperuntukkan bagi kelasnya.
     */
    public function test_student_dashboard_filters_out_activities_not_scoped_for_student(): void
    {
        $attendanceService = app(\App\Services\AttendanceService::class);
        $todayDayName = $attendanceService->getDayNameInIndonesian(Carbon::now());

        $otherClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 12,
            'nama_kelas' => 'XII IPA 1',
        ]);

        // Activity khusus kelas 12
        ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->teacher->id,
            'name' => 'Try Out Khusus Kelas 12',
            'day_name' => $todayDayName,
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'target_scope' => 'class',
            'target_class_ids' => [$otherClass->id],
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Try Out Khusus Kelas 12');
    }

    /**
     * Test: Dasbor Orang Tua menampilkan agenda gabungan hari ini (KBM + Kegiatan) untuk anak.
     */
    public function test_parent_dashboard_displays_merged_today_agenda_for_children(): void
    {
        $attendanceService = app(\App\Services\AttendanceService::class);
        $todayDayName = $attendanceService->getDayNameInIndonesian(Carbon::now());

        ClassSchedule::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'day_name' => $todayDayName,
            'period_number' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:15:00',
        ]);

        ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->teacher->id,
            'name' => 'Pramuka Sore',
            'day_name' => $todayDayName,
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'target_scope' => 'all',
        ]);

        $response = $this->actingAs($this->parent)
            ->get(route('parent.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Siswa Cerdas');
        $response->assertSee('Fisika Dasar');
        $response->assertSee('Pramuka Sore');
    }
}
