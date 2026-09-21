<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\Announcement;

class TeacherAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;
    private Tenant $tenantB;
    private SchoolClass $classA;
    private SchoolClass $classB;
    private User $teacherA;
    private User $teacherB;
    private User $studentA;
    private User $studentB;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Tenant A (Sekolah A)
        $this->tenantA = Tenant::create([
            'name' => 'SMK Negeri 1 Jakarta',
            'code' => 'SMKN1',
            'slug' => 'smkn1-jakarta',
        ]);

        $this->teacherA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Budi Guru Wali',
            'is_active' => true,
        ]);

        $this->classA = SchoolClass::create([
            'nama_kelas' => 'XII RPL 1',
            'jenjang' => 'SMK',
            'tingkat' => 12,
            'wali_kelas_id' => $this->teacherA->id,
            'tenant_id' => $this->tenantA->id,
        ]);

        $this->studentA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA->id,
            'name' => 'Andi Siswa RPL',
            'is_active' => true,
        ]);

        // Setup Tenant B (Sekolah B)
        $this->tenantB = Tenant::create([
            'name' => 'SMA Swasta 2 Bandung',
            'code' => 'SMAS2',
            'slug' => 'smas2-bandung',
        ]);

        $this->teacherB = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'teacher',
            'name' => 'Siti Guru Wali B',
            'is_active' => true,
        ]);

        $this->classB = SchoolClass::create([
            'nama_kelas' => 'XII IPA 1',
            'jenjang' => 'SMA',
            'tingkat' => 12,
            'wali_kelas_id' => $this->teacherB->id,
            'tenant_id' => $this->tenantB->id,
        ]);

        $this->studentB = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'student',
            'class_id' => $this->classB->id,
            'name' => 'Bambang Siswa B',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Test guru membuat pengumuman dengan target siswa (role: student / seluruh kelas).
     */
    public function test_teacher_can_create_announcement_targeting_students(): void
    {
        $response = $this->actingAs($this->teacherA)->post(route('teacher.announcements.store'), [
            'title' => 'Pengumuman Ujian Akhir Semester',
            'description' => 'Harap mempersiapkan kartu ujian dan hadir tepat waktu.',
            'target_audience' => 'students',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pengumuman berhasil dikirim.');

        $this->assertDatabaseHas('announcements', [
            'school_class_id' => $this->classA->id,
            'teacher_id' => $this->teacherA->id,
            'title' => 'Pengumuman Ujian Akhir Semester',
            'target_audience' => 'students',
        ]);
    }

    /**
     * 2. Test halaman GET /teacher/announcements berhasil dibuka (status 200 OK) dan menampilkan pengumuman yang baru dibuat tanpa 500 error.
     */
    public function test_teacher_announcements_page_loads_successfully_without_500(): void
    {
        // Buat pengumuman terlebih dahulu
        $announcement = Announcement::create([
            'school_class_id' => $this->classA->id,
            'teacher_id' => $this->teacherA->id,
            'title' => 'Pengumuman Libur Nasional',
            'description' => 'Hari Senin depan kegiatan KBM ditiadakan.',
            'target_audience' => 'students',
        ]);

        // Kunjungi halaman riwayat pengumuman guru
        $response = $this->actingAs($this->teacherA)->get(route('teacher.announcements.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengumuman Wali Kelas');
        $response->assertSee('Pengumuman Libur Nasional');
        $response->assertSee('XII RPL 1');
        $response->assertSee('Siswa');
    }

    /**
     * 3. Test siswa dari sekolah/tenant yang sama dapat melihat pengumuman tersebut di dashboard/PWA siswa.
     */
    public function test_student_in_same_tenant_can_view_announcement_in_dashboard(): void
    {
        // Buat pengumuman di Sekolah A
        Announcement::create([
            'school_class_id' => $this->classA->id,
            'teacher_id' => $this->teacherA->id,
            'title' => 'Pengumuman Tugas Pemrograman Web',
            'description' => 'Kumpulkan tugas CRUD Laravel sebelum hari Jumat.',
            'target_audience' => 'students',
        ]);

        // Siswa A (Sekolah A) membuka dashboard
        $response = $this->actingAs($this->studentA)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pengumuman Kelas');
        $response->assertSee('Pengumuman Tugas Pemrograman Web');
        $response->assertSee('Kumpulkan tugas CRUD Laravel sebelum hari Jumat.');
    }

    /**
     * 4. Test isolasi multi-tenant: siswa dari sekolah lain TIDAK dapat melihat pengumuman tersebut.
     */
    public function test_student_from_other_tenant_cannot_view_announcement(): void
    {
        // Buat pengumuman di Sekolah A
        Announcement::create([
            'school_class_id' => $this->classA->id,
            'teacher_id' => $this->teacherA->id,
            'title' => 'Pengumuman Internal Sekolah A',
            'description' => 'Hanya untuk siswa SMK Negeri 1 Jakarta.',
            'target_audience' => 'students',
        ]);

        // Siswa B (Sekolah B) membuka dashboard
        $response = $this->actingAs($this->studentB)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Pengumuman Internal Sekolah A');
        $response->assertDontSee('Hanya untuk siswa SMK Negeri 1 Jakarta.');
    }

    /**
     * 5. Test guru dapat menghapus pengumuman kelas binaannya sendiri.
     */
    public function test_teacher_can_delete_own_announcement(): void
    {
        $announcement = Announcement::create([
            'school_class_id' => $this->classA->id,
            'teacher_id' => $this->teacherA->id,
            'title' => 'Pengumuman yang Akan Dihapus',
            'description' => 'Akan segera dihapus.',
            'target_audience' => 'students',
        ]);

        $response = $this->actingAs($this->teacherA)->delete(route('teacher.announcements.destroy', $announcement->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pengumuman berhasil dihapus.');
        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);
    }

    /**
     * 6. Test guru dari sekolah lain tidak dapat menghapus pengumuman sekolah lain (multi-tenant protection).
     */
    public function test_teacher_from_other_tenant_cannot_delete_announcement(): void
    {
        $announcement = Announcement::create([
            'school_class_id' => $this->classA->id,
            'teacher_id' => $this->teacherA->id,
            'title' => 'Pengumuman Sekolah A',
            'description' => 'Milik sekolah A.',
            'target_audience' => 'students',
        ]);

        $response = $this->actingAs($this->teacherB)->delete(route('teacher.announcements.destroy', $announcement->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
        ]);
    }
}
