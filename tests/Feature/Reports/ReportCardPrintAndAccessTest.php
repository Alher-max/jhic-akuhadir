<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\ExtracurricularGrade;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Subject;
use App\Models\SubjectGrade;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardPrintAndAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected AcademicYear $yearA;
    protected AcademicYear $yearB;
    protected SchoolClass $classA1;
    protected SchoolClass $classA2;
    protected SchoolClass $classB1;
    protected Subject $subjectMath;
    protected Subject $subjectEnglish;
    protected User $teacherA1;
    protected User $teacherA2;
    protected User $operatorA;
    protected User $headmasterA;
    protected User $studentA1;
    protected User $studentA2;
    protected User $parentA;
    protected User $teacherB1;
    protected User $studentB1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant A
        $this->tenantA = Tenant::create([
            'name' => 'SMA Teladan Bangsa',
            'slug' => 'sma-teladan-bangsa',
            'code' => 'SMATB',
            'institution_type' => 'school',
            'npsn' => '20109988',
            'address' => 'Jl. Pendidikan No. 45, Jakarta',
            'city' => 'Jakarta Selatan',
            'onboarding_completed' => true,
        ]);

        $this->yearA = AcademicYear::create([
            'tenant_id' => $this->tenantA->id,
            'name' => '2026/2027',
            'semester' => '1',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        $this->teacherA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->operatorA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->headmasterA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'headmaster',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->classA1 = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'X-MIPA-1',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->teacherA1->id,
        ]);

        $this->classA2 = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'X-MIPA-2',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->teacherA2->id,
        ]);

        $this->studentA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Ahmad Dahlan',
            'nisn' => '0012345678',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->studentA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Budi Utomo',
            'nisn' => '0012345679',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->parentA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        // Hubungkan Parent A dengan Student A1 via pivot / direct relation
        $this->parentA->students()->attach($this->studentA1->id);

        $this->subjectMath = Subject::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'MTK',
            'name' => 'Matematika',
        ]);

        $this->subjectEnglish = Subject::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'ING',
            'name' => 'Bahasa Inggris',
        ]);

        ClassSchedule::create([
            'tenant_id' => $this->tenantA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectMath->id,
            'teacher_id' => $this->teacherA1->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);

        ClassSchedule::create([
            'tenant_id' => $this->tenantA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectEnglish->id,
            'teacher_id' => $this->teacherA2->id,
            'day_name' => 'Selasa',
            'period_number' => 2,
            'start_time' => '09:15',
            'end_time' => '10:45',
        ]);

        // Input nilai
        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectMath->id,
            'student_id' => $this->studentA1->id,
            'teacher_id' => $this->teacherA1->id,
            'score' => 88.00,
            'highest_achievement' => 'Menguasai persamaan kuadrat dengan sangat baik',
            'lowest_achievement' => 'Perlu peningkatan dalam trigonometri',
        ]);

        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectEnglish->id,
            'student_id' => $this->studentA1->id,
            'teacher_id' => $this->teacherA2->id,
            'score' => 92.00,
            'highest_achievement' => 'Sangat mahir dalam analytical exposition',
        ]);

        // 2. Setup Tenant B
        $this->tenantB = Tenant::create([
            'name' => 'SMA Harapan Bangsa',
            'slug' => 'sma-harapan-bangsa',
            'code' => 'SMAHB',
            'institution_type' => 'school',
            'onboarding_completed' => true,
        ]);

        $this->yearB = AcademicYear::create([
            'tenant_id' => $this->tenantB->id,
            'name' => '2026/2027',
            'semester' => '1',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        $this->teacherB1 = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->classB1 = SchoolClass::create([
            'tenant_id' => $this->tenantB->id,
            'nama_kelas' => 'X-B',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->teacherB1->id,
        ]);

        $this->studentB1 = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'student',
            'class_id' => $this->classB1->id,
            'name' => 'Siswa Luar Tenant',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    /**
     * Test 1: Otorisasi Cetak & Isolasi Tenant
     * Wali kelas hanya bisa mencetak rombelnya; Operator/Kepsek supervisi seluruh kelas tenant; isolasi lintas tenant 404/403.
     */
    public function test_print_authorization_and_tenant_isolation(): void
    {
        // 1. Wali kelas A1 mencetak single dan batch kelas A1 -> 200 OK
        $response = $this->actingAs($this->teacherA1)
            ->get(route('homeroom.reports.print.single', [$this->classA1, $this->studentA1]));
        $response->assertOk();

        $response = $this->actingAs($this->teacherA1)
            ->get(route('homeroom.reports.print.batch', $this->classA1));
        $response->assertOk();

        // 2. Wali kelas A1 mencoba mencetak kelas A2 (wali kelasnya Teacher A2) -> 403 Forbidden
        $response = $this->actingAs($this->teacherA1)
            ->get(route('homeroom.reports.print.batch', $this->classA2));
        $response->assertForbidden();

        // 3. Operator & Kepala Sekolah memiliki izin supervisi cetak seluruh kelas di tenant -> 200 OK
        $response = $this->actingAs($this->operatorA)
            ->get(route('homeroom.reports.print.batch', $this->classA1));
        $response->assertOk();

        $response = $this->actingAs($this->headmasterA)
            ->get(route('homeroom.reports.print.batch', $this->classA2));
        $response->assertOk();

        // 4. Lintas Tenant: Teacher A1 mencoba mencetak kelas Tenant B -> 404/403
        $response = $this->actingAs($this->teacherA1)
            ->get(route('homeroom.reports.print.batch', $this->classB1));
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // 5. Siswa A1 mencoba mengakses rute cetak rombel wali kelas -> 403 Forbidden
        $response = $this->actingAs($this->studentA1)
            ->get(route('homeroom.reports.print.batch', $this->classA1));
        $response->assertForbidden();
    }

    /**
     * Test 2: Publication Guard untuk Siswa & Orang Tua
     * Rapor dalam status 'draft' ditahan; setelah 'published' dapat diakses dan dicetak.
     */
    public function test_student_and_parent_publication_guard(): void
    {
        // Setup draft report
        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 1,
            'permission_count' => 1,
            'alpha_count' => 0,
            'status' => 'draft',
        ]);

        // 1. Siswa mengakses dashboard saat draft -> melihat banner penahanan
        $response = $this->actingAs($this->studentA1)
            ->get(route('student.report-card'));
        $response->assertOk()
            ->assertSee('Sedang Dalam Proses Penyusunan');

        // 2. Siswa mencoba membuka cetak saat masih draft -> ditolak 403 Forbidden
        $response = $this->actingAs($this->studentA1)
            ->get(route('student.report-card.print'));
        $response->assertForbidden();

        // 3. Orang tua mengakses dashboard saat draft -> melihat banner penahanan
        $response = $this->actingAs($this->parentA)
            ->get(route('parent.report-card', ['child_id' => $this->studentA1->id]));
        $response->assertOk()
            ->assertSee('Sedang Dalam Proses Penyusunan');

        // 4. Orang tua mencoba membuka cetak saat masih draft -> ditolak 403 Forbidden
        $response = $this->actingAs($this->parentA)
            ->get(route('parent.report-card.print', ['child_id' => $this->studentA1->id]));
        $response->assertForbidden();

        // 5. Publikasikan rapor (status -> 'published')
        $report->status = 'published';
        $report->published_at = now();
        $report->generateVerificationHash();
        $report->save();

        // 6. Siswa mengakses dashboard saat published -> melihat nilai dan tombol cetak
        $response = $this->actingAs($this->studentA1)
            ->get(route('student.report-card'));
        $response->assertOk()
            ->assertSee('Rapor Resmi Telah Diterbitkan')
            ->assertSee('Matematika');

        // 7. Siswa membuka cetak saat published -> 200 OK
        $response = $this->actingAs($this->studentA1)
            ->get(route('student.report-card.print'));
        $response->assertOk()
            ->assertSee('LAPORAN HASIL BELAJAR (RAPOR)');

        // 8. Orang tua membuka cetak saat published -> 200 OK
        $response = $this->actingAs($this->parentA)
            ->get(route('parent.report-card.print', ['child_id' => $this->studentA1->id]));
        $response->assertOk()
            ->assertSee('LAPORAN HASIL BELAJAR (RAPOR)');
    }

    /**
     * Test 3: Rute Publik Verifikasi Dokumen Rapor (QR Code Scan)
     * Hash valid menampilkan halaman terverifikasi (200); hash tidak valid menampilkan 404.
     */
    public function test_public_verification_route(): void
    {
        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 2,
            'permission_count' => 1,
            'alpha_count' => 0,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $validHash = $report->generateVerificationHash();

        // 1. Kunjungi rute verifikasi publik dengan hash valid (tanpa login / guest)
        $response = $this->get(route('report.verify', $validHash));
        $response->assertOk()
            ->assertSee('DOKUMEN RESMI TERVERIFIKASI')
            ->assertSee('Ahmad Dahlan')
            ->assertSee('SMA Teladan Bangsa')
            ->assertSee('X-MIPA-1');

        // 2. Kunjungi rute verifikasi publik dengan hash fiktif / tidak valid -> 404
        $invalidHash = 'non_existent_fake_hash_1234567890';
        $response = $this->get(route('report.verify', $invalidHash));
        $response->assertNotFound()
            ->assertSee('DOKUMEN TIDAK VALID / TIDAK DITEMUKAN');
    }

    /**
     * Test 4: Rendering View Cetak Single & Batch Rombel (Standar A4)
     * Memverifikasi keberadaan Kop Sekolah, Nilai Mapel, Presensi S/I/A, QR Hash, dan .page-break.
     */
    public function test_single_and_batch_print_view_rendering(): void
    {
        $report1 = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 2,
            'permission_count' => 1,
            'alpha_count' => 0,
            'homeroom_notes' => 'Ananda sangat berbakat dalam matematika.',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $report1->generateVerificationHash();

        $report2 = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA2->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 0,
            'permission_count' => 0,
            'alpha_count' => 0,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $report2->generateVerificationHash();

        ExtracurricularGrade::create([
            'tenant_id' => $this->tenantA->id,
            'student_report_id' => $report1->id,
            'activity_name' => 'Pramuka Garuda',
            'predicate' => 'Sangat Baik',
            'description' => 'Aktif dan disiplin',
        ]);

        // 1. Cetak Single: Memuat Kop, Mapel, Presensi, Ekskul, dan Token Hash
        $response = $this->actingAs($this->teacherA1)
            ->get(route('homeroom.reports.print.single', [$this->classA1, $this->studentA1]));

        $response->assertOk()
            ->assertSee('SMA Teladan Bangsa')
            ->assertSee('Ahmad Dahlan')
            ->assertSee('Matematika')
            ->assertSee('88')
            ->assertSee('Pramuka Garuda')
            ->assertSee('Sakit (S)')
            ->assertSee('Ananda sangat berbakat dalam matematika.')
            ->assertSee('Dokumen Resmi Terverifikasi');

        // 2. Cetak Batch: Memuat seluruh siswa dalam kelas dan pemisah .page-break
        $response = $this->actingAs($this->teacherA1)
            ->get(route('homeroom.reports.print.batch', $this->classA1));

        $response->assertOk()
            ->assertSee('Ahmad Dahlan')
            ->assertSee('Budi Utomo')
            ->assertSee('page-break');
    }

    /**
     * Test 5: Aksi Publikasi Rapor Kelas & Pembuatan Hash Unik Otomatis
     * Endpoint publishClassReports mengubah seluruh siswa kelas menjadi 'published' & men-generate verification_hash unik.
     */
    public function test_class_report_publication_and_hash_generation(): void
    {
        // 1. Jalankan aksi publikasi rapor untuk kelas A1
        $response = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.publish', $this->classA1));

        $response->assertSessionHas('success');

        // 2. Ambil rapor seluruh siswa di kelas A1
        $reports = StudentReport::where('tenant_id', $this->tenantA->id)
            ->where('class_id', $this->classA1->id)
            ->get();

        $this->assertCount(2, $reports);

        foreach ($reports as $report) {
            $this->assertSame('published', $report->status);
            $this->assertNotNull($report->published_at);
            $this->assertNotNull($report->verification_hash);
            $this->assertSame(64, strlen($report->verification_hash));
        }

        // Pastikan hash masing-masing siswa unik
        $this->assertNotSame($reports[0]->verification_hash, $reports[1]->verification_hash);

        // 3. Jalankan aksi kunci rapor kelas
        $response = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.lock', $this->classA1));

        $response->assertSessionHas('success');

        $lockedReports = StudentReport::where('tenant_id', $this->tenantA->id)
            ->where('class_id', $this->classA1->id)
            ->pluck('status');

        $this->assertTrue($lockedReports->every(fn($status) => $status === 'locked'));
    }
}
