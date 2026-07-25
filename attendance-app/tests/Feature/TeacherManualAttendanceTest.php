<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherManualAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_record_manual_attendance_for_student(): void
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
            'name' => 'Guru Penguji',
            'email' => 'guru@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa XI IPA 1 #1',
            'email' => 'siswa1@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->from(route('teacher.dashboard'))
            ->actingAs($teacher)
            ->post(route('teacher.manual-attendance'), [
                'student_id' => $student->id,
                'status' => 'present',
                'notes' => 'Presensi manual disetujui guru (lupa HP)',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'tenant_id' => $tenant->id,
            'user_id' => $student->id,
            'status' => 'present',
            'notes' => 'Presensi manual disetujui guru (lupa HP)',
        ]);
    }
}
