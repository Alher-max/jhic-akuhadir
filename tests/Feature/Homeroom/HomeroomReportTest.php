<?php

declare(strict_types=1);

namespace Tests\Feature\Homeroom;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\ExtracurricularGrade;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Subject;
use App\Models\SubjectGrade;
use App\Models\Tenant;
use App\Models\User;
use App\Services\HomeroomReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeroomReportTest extends TestCase
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
    protected User $studentA1;
    protected User $studentA2;
    protected User $parentA;
    protected User $teacherB1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant A
        $this->tenantA = Tenant::create([
            'name' => 'SMA Teladan Bangsa',
            'slug' => 'sma-teladan-bangsa',
            'code' => 'SMATB',
            'institution_type' => 'school',
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

        // Attach subjects to Class A1 via ClassSchedule
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

        // 2. Setup Tenant B (Isolation verification)
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
            'nama_kelas' => 'X-A',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->teacherB1->id,
        ]);
    }

    /**
     * Test 1: Otorisasi Wali Kelas & Hak Supervisi
     * Wali kelas A mengakses kelas A (200), ditolak di kelas B (403), non-guru ditolak (403), operator berhak supervisi (200).
     */
    public function test_homeroom_authorization(): void
    {
        // 1. Wali kelas A1 mengakses kelas bimbingannya (Class A1) -> 200 OK
        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.index'));
        $response->assertOk();

        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.leger', $this->classA1));
        $response->assertOk();

        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.attendance', $this->classA1));
        $response->assertOk();

        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.notes', $this->classA1));
        $response->assertOk();

        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.extracurricular', $this->classA1));
        $response->assertOk();

        // 2. Wali kelas A1 mencoba mengakses kelas A2 (wali kelasnya adalah Teacher A2) -> 403 Forbidden
        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.leger', $this->classA2));
        $response->assertForbidden();

        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.attendance', $this->classA2));
        $response->assertForbidden();

        // 3. Siswa mencoba mengakses rute rapor wali kelas -> 403 Forbidden
        $response = $this->actingAs($this->studentA1)->get(route('homeroom.reports.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->studentA1)->get(route('homeroom.reports.leger', $this->classA1));
        $response->assertForbidden();

        // 4. Orang tua / wali murid mencoba mengakses -> 403 Forbidden
        $response = $this->actingAs($this->parentA)->get(route('homeroom.reports.index'));
        $response->assertForbidden();

        // 5. Operator / Kepala Sekolah memiliki hak supervisi ke seluruh rombel tenant -> 200 OK
        $response = $this->actingAs($this->operatorA)->get(route('homeroom.reports.leger', $this->classA1));
        $response->assertOk();

        $response = $this->actingAs($this->operatorA)->get(route('homeroom.reports.leger', $this->classA2));
        $response->assertOk();
    }

    /**
     * Test 2: Auto-Pull Perhitungan Presensi HadirYuk
     * Memverifikasi akumulasi Sakit, Izin, Alpa pada rentang tanggal semester aktif & tipe 'school'.
     */
    public function test_auto_pull_attendance_calculation(): void
    {
        // 1. Data kehadiran valid dalam semester aktif (2026-07-15 s.d. 2026-12-20)
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2026-08-01',
            'status' => 'sick',
        ]);
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2026-08-02',
            'status' => 'sick',
        ]);
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2026-08-03',
            'status' => 'permission',
        ]);
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2026-08-04',
            'status' => 'alpha',
        ]);
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2026-08-05',
            'status' => 'present', // Hadir (tidak menambah S/I/A)
        ]);

        // 2. Data di luar rentang tanggal tahun ajaran (TIDAK boleh dihitung)
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2026-05-10', // Sebelum start_date
            'status' => 'sick',
        ]);
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'school',
            'date' => '2027-01-10', // Setelah end_date
            'status' => 'alpha',
        ]);

        // 3. Data KBM / bukan school level (TIDAK boleh dihitung)
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'attendance_type' => 'kbm',
            'date' => '2026-08-06',
            'status' => 'sick',
        ]);

        // Eksekusi Auto-Pull via API/Endpoint Controller
        $response = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.sync-attendance', $this->classA1));

        $response->assertSessionHas('success');

        // Verifikasi hasil sinkronisasi pada tabel student_reports
        $report = StudentReport::where('tenant_id', $this->tenantA->id)
            ->where('academic_year_id', $this->yearA->id)
            ->where('class_id', $this->classA1->id)
            ->where('student_id', $this->studentA1->id)
            ->first();

        $this->assertNotNull($report);
        $this->assertSame(2, $report->sick_count);
        $this->assertSame(1, $report->permission_count);
        $this->assertSame(1, $report->alpha_count);
        $this->assertSame(4, $report->total_absence);
    }

    /**
     * Test 3: Isolasi Tenant pada Dasbor & Rapor Wali Kelas
     * Pengguna Tenant A tidak dapat melihat, mengubah, atau menghapus data Tenant B.
     */
    public function test_tenant_isolation_on_homeroom_reports(): void
    {
        // Setup report & extracurricular di Tenant B
        $studentB = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'student',
            'class_id' => $this->classB1->id,
            'name' => 'Siswa B',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $reportB = StudentReport::create([
            'tenant_id' => $this->tenantB->id,
            'academic_year_id' => $this->yearB->id,
            'class_id' => $this->classB1->id,
            'student_id' => $studentB->id,
            'wali_kelas_id' => $this->teacherB1->id,
            'sick_count' => 1,
            'permission_count' => 0,
            'alpha_count' => 0,
        ]);

        $ekskulB = ExtracurricularGrade::create([
            'tenant_id' => $this->tenantB->id,
            'student_report_id' => $reportB->id,
            'activity_name' => 'Pramuka Tenant B',
            'predicate' => 'Baik',
        ]);

        // 1. Teacher A1 mencoba mengakses Leger kelas Tenant B -> 403 atau 404
        $response = $this->actingAs($this->teacherA1)->get(route('homeroom.reports.leger', $this->classB1));
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // 2. Operator A mencoba mengakses kelas Tenant B -> 403 atau 404 (lintas tenant diblokir)
        $response = $this->actingAs($this->operatorA)->get(route('homeroom.reports.leger', $this->classB1));
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // 3. Teacher A1 mencoba menghapus nilai ekskul milik Tenant B -> 404
        $response = $this->actingAs($this->teacherA1)
            ->delete(route('homeroom.reports.extracurricular.destroy', $ekskulB));
        $response->assertNotFound();

        // 4. Pastikan data ekskul Tenant B tetap utuh
        $this->assertDatabaseHas('extracurricular_grades', [
            'id' => $ekskulB->id,
            'activity_name' => 'Pramuka Tenant B',
        ]);
    }

    /**
     * Test 4: Integritas Kompilasi Matriks Leger Nilai
     * Nilai mapel dari Buku Nilai (Sprint 2) terpetakan akurat ke Leger siswa, rata-rata mapel, & rata-rata siswa.
     */
    public function test_leger_matrix_data_integrity(): void
    {
        // Input nilai Siswa 1: MTK=80, ING=90
        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectMath->id,
            'student_id' => $this->studentA1->id,
            'teacher_id' => $this->teacherA1->id,
            'score' => 80.00,
            'highest_achievement' => 'Menguasai Aljabar',
        ]);

        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectEnglish->id,
            'student_id' => $this->studentA1->id,
            'teacher_id' => $this->teacherA2->id,
            'score' => 90.00,
            'highest_achievement' => 'Menguasai Grammar',
        ]);

        // Input nilai Siswa 2: MTK=70, ING=80
        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectMath->id,
            'student_id' => $this->studentA2->id,
            'teacher_id' => $this->teacherA1->id,
            'score' => 70.00,
        ]);

        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectEnglish->id,
            'student_id' => $this->studentA2->id,
            'teacher_id' => $this->teacherA2->id,
            'score' => 80.00,
        ]);

        // Ambil data matriks melalui HomeroomReportService
        $service = app(HomeroomReportService::class);
        $matrix = $service->buildLegerMatrix($this->classA1, $this->yearA);

        $this->assertCount(2, $matrix['students']);
        $this->assertCount(2, $matrix['subjects']);

        // Verifikasi nilai individu Siswa 1: MTK=80, ING=90, Rata-rata = 85.0
        $this->assertEquals(80.0, $matrix['matrix'][$this->studentA1->id][$this->subjectMath->id]);
        $this->assertEquals(90.0, $matrix['matrix'][$this->studentA1->id][$this->subjectEnglish->id]);
        $this->assertEquals(85.0, $matrix['averages'][$this->studentA1->id]);

        // Verifikasi nilai individu Siswa 2: MTK=70, ING=80, Rata-rata = 75.0
        $this->assertEquals(70.0, $matrix['matrix'][$this->studentA2->id][$this->subjectMath->id]);
        $this->assertEquals(80.0, $matrix['matrix'][$this->studentA2->id][$this->subjectEnglish->id]);
        $this->assertEquals(75.0, $matrix['averages'][$this->studentA2->id]);

        // Verifikasi Rata-rata Mata Pelajaran
        // MTK: (80 + 70) / 2 = 75.0
        $this->assertEquals(75.0, $matrix['subjectAverages'][$this->subjectMath->id]);
        // ING: (90 + 80) / 2 = 85.0
        $this->assertEquals(85.0, $matrix['subjectAverages'][$this->subjectEnglish->id]);

        // Verifikasi Rata-rata Keseluruhan Rombel: (85.0 + 75.0) / 2 = 80.0
        $this->assertEquals(80.0, $matrix['overallAverage']);

        // Verifikasi melalui JSON request ke controller
        $response = $this->actingAs($this->teacherA1)
            ->getJson(route('homeroom.reports.leger', $this->classA1));

        $response->assertOk()
            ->assertJsonPath('data.overallAverage', 80);
    }

    /**
     * Test 5: Persistensi Catatan Karakter, Kenaikan Kelas, & Ekstrakurikuler
     * Batch update catatan & kenaikan kelas serta penambahan dan penghapusan nilai ekskul.
     */
    public function test_extracurricular_and_notes_persistence(): void
    {
        // 1. Simpan Catatan Karakter & Status Kenaikan Kelas melalui batch-update
        $payload = [
            'class_id' => $this->classA1->id,
            'reports' => [
                [
                    'student_id' => $this->studentA1->id,
                    'sick_count' => 1,
                    'permission_count' => 2,
                    'alpha_count' => 0,
                    'homeroom_notes' => 'Menunjukkan perkembangan karakter yang sangat mandiri dan berintegritas.',
                    'promotion_status' => 'Naik ke Kelas XI',
                    'status' => 'submitted',
                ],
                [
                    'student_id' => $this->studentA2->id,
                    'sick_count' => 0,
                    'permission_count' => 0,
                    'alpha_count' => 1,
                    'homeroom_notes' => 'Perlu meningkatkan kedisiplinan dan kehadiran di kelas.',
                    'promotion_status' => 'Naik ke Kelas XI',
                    'status' => 'draft',
                ],
            ],
        ];

        $response = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.batch-update', $this->classA1), $payload);

        $response->assertSessionHas('success');

        // Verifikasi database student_reports
        $this->assertDatabaseHas('student_reports', [
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 1,
            'permission_count' => 2,
            'alpha_count' => 0,
            'promotion_status' => 'Naik ke Kelas XI',
            'status' => 'submitted',
        ]);

        $report1 = StudentReport::where('student_id', $this->studentA1->id)->first();
        $this->assertNotNull($report1);

        // 2. Tambah Nilai Ekstrakurikuler untuk Siswa 1
        $ekskulPayload = [
            'student_report_id' => $report1->id,
            'activity_name' => 'Pramuka Garuda',
            'predicate' => 'Sangat Baik',
            'description' => 'Aktif dalam kegiatan jambore daerah dan berjiwa kepemimpinan tinggi.',
        ];

        $response = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.extracurricular.store'), $ekskulPayload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('extracurricular_grades', [
            'tenant_id' => $this->tenantA->id,
            'student_report_id' => $report1->id,
            'activity_name' => 'Pramuka Garuda',
            'predicate' => 'Sangat Baik',
        ]);

        $ekskul = ExtracurricularGrade::where('student_report_id', $report1->id)->first();
        $this->assertNotNull($ekskul);

        // 3. Hapus Nilai Ekstrakurikuler
        $response = $this->actingAs($this->teacherA1)
            ->delete(route('homeroom.reports.extracurricular.destroy', $ekskul));

        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('extracurricular_grades', [
            'id' => $ekskul->id,
        ]);
    }
}
