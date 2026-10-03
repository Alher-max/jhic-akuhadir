<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Subject;
use App\Models\SubjectGrade;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
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
    protected Subject $subjectB1;
    protected User $teacherA1; // Wali kelas classA1, mengajar Matematika di classA1
    protected User $teacherA2; // Wali kelas classA2, mengajar B. Inggris di classA1
    protected User $teacherA3; // Guru tenant A yang tidak ditugaskan di classA1
    protected User $operatorA;
    protected User $headmasterA;
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

        $this->teacherA3 = User::factory()->create([
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
            'nis' => '1001',
            'gender' => 'L',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->studentA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Siti Nurhaliza',
            'nisn' => '0012345679',
            'nis' => '1002',
            'gender' => 'P',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->parentA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
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

        // Penugasan Mengajar di Class A1:
        // Teacher A1 -> Matematika
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

        // Teacher A2 -> Bahasa Inggris
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

        // Input Nilai SubjectGrade untuk Student A1 & A2
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
            'lowest_achievement' => '',
        ]);

        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectMath->id,
            'student_id' => $this->studentA2->id,
            'teacher_id' => $this->teacherA1->id,
            'score' => 78.50,
            'highest_achievement' => 'Memahami statistik dasar',
            'lowest_achievement' => 'Perlu latihan aljabar',
        ]);

        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectEnglish->id,
            'student_id' => $this->studentA2->id,
            'teacher_id' => $this->teacherA2->id,
            'score' => 85.00,
            'highest_achievement' => 'Percaya diri dalam speaking',
            'lowest_achievement' => 'Perlu pengayaan grammar',
        ]);

        // StudentReport untuk Kompilasi Leger
        StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 2,
            'permission_count' => 1,
            'alpha_count' => 0,
            'homeroom_notes' => 'Prestasi belajar sangat memuaskan, pertahankan di semester berikutnya.',
            'status' => 'draft',
        ]);

        StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA2->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 0,
            'permission_count' => 0,
            'alpha_count' => 1,
            'homeroom_notes' => 'Tingkatkan kedisiplinan dan rajin berlatih.',
            'status' => 'draft',
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
            'nama_kelas' => 'X-A',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'wali_kelas_id' => $this->teacherB1->id,
        ]);

        $this->subjectB1 = Subject::create([
            'tenant_id' => $this->tenantB->id,
            'code' => 'BIO',
            'name' => 'Biologi',
        ]);
    }

    /**
     * Test 1: Otorisasi Berjenjang untuk Ekspor e-Rapor, RDM, dan Leger.
     */
    public function test_export_authorization(): void
    {
        // 1. Guru yang ditugaskan (Teacher A1 mengajar Matematika di Class A1) -> Boleh ekspor e-Rapor & RDM
        $responseERapor = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        );
        $responseERapor->assertOk();

        $responseRDM = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-rdm', [$this->classA1, $this->subjectMath])
        );
        $responseRDM->assertOk();

        // 2. Guru yang TIDAK mengajar mapel tersebut (Teacher A1 tidak mengajar B. Inggris di Class A1) -> 403 Forbidden
        $responseUnauthorizedMapel = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectEnglish])
        );
        $responseUnauthorizedMapel->assertForbidden();

        // 3. Guru yang sama sekali tidak ditugaskan di kelas ini (Teacher A3) -> 403 Forbidden
        $responseTeacherA3 = $this->actingAs($this->teacherA3)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        );
        $responseTeacherA3->assertForbidden();

        // 4. Wali kelas (Teacher A1 adalah wali kelas Class A1) -> Boleh ekspor Leger
        $responseLegerHomeroom = $this->actingAs($this->teacherA1)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        );
        $responseLegerHomeroom->assertOk();

        // 5. Bukan wali kelas (Teacher A2 bukan wali kelas Class A1) -> 403 Forbidden pada ekspor Leger
        $responseLegerNonHomeroom = $this->actingAs($this->teacherA2)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        );
        $responseLegerNonHomeroom->assertForbidden();

        // 6. Siswa dan Orang Tua -> 403 Forbidden di seluruh endpoint ekspor
        $this->actingAs($this->studentA1)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        )->assertForbidden();

        $this->actingAs($this->studentA1)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        )->assertForbidden();

        $this->actingAs($this->parentA)->get(
            route('teacher.gradebook.export-rdm', [$this->classA1, $this->subjectMath])
        )->assertForbidden();

        $this->actingAs($this->parentA)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        )->assertForbidden();

        // 7. Operator dan Kepala Sekolah -> Memiliki hak supervisi (Boleh akses semua)
        $this->actingAs($this->operatorA)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        )->assertOk();

        $this->actingAs($this->headmasterA)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        )->assertOk();
    }

    /**
     * Test 2: Isolasi Multi-Tenant yang Ketat (Cross-Tenant Rejection).
     */
    public function test_export_tenant_isolation(): void
    {
        // Guru Tenant B mencoba mengekspor e-Rapor milik Tenant A -> 404 Not Found
        $responseCrossERapor = $this->actingAs($this->teacherB1)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        );
        $responseCrossERapor->assertNotFound();

        // Guru Tenant B mencoba mengekspor RDM milik Tenant A -> 404 Not Found
        $responseCrossRDM = $this->actingAs($this->teacherB1)->get(
            route('teacher.gradebook.export-rdm', [$this->classA1, $this->subjectMath])
        );
        $responseCrossRDM->assertNotFound();

        // Guru Tenant B mencoba mengekspor Leger milik Tenant A -> 404 Not Found
        $responseCrossLeger = $this->actingAs($this->teacherB1)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        );
        $responseCrossLeger->assertNotFound();
    }

    /**
     * Test 3: Integritas Format & Konten Stream e-Rapor SP Kemendikbudristek.
     */
    public function test_erapor_streamed_content_integrity(): void
    {
        $response = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment;', (string) $disposition);
        $this->assertStringContainsString('e-Rapor_X-MIPA-1_MTK_Sem1_2026-2027.csv', (string) $disposition);

        $content = $response->streamedContent();

        // Validasi UTF-8 BOM di awal output
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Validasi Header Row e-Rapor SP
        $this->assertStringContainsString('No;NISN;NIS;"Nama Siswa";"Nilai Akhir";"Capaian Tertinggi";"Capaian Terendah"', $content);

        // Validasi Baris Siswa 1 (Ahmad Dahlan, NISN: 0012345678, Nilai: 88)
        $this->assertStringContainsString("0012345678", $content);
        $this->assertStringContainsString('"Ahmad Dahlan"', $content);
        $this->assertStringContainsString(";88;", $content);
        $this->assertStringContainsString('"Menguasai persamaan kuadrat dengan sangat baik"', $content);
        $this->assertStringContainsString('"Perlu peningkatan dalam trigonometri"', $content);

        // Validasi Baris Siswa 2 (Siti Nurhaliza, NISN: 0012345679, Nilai: 79)
        $this->assertStringContainsString("0012345679", $content);
        $this->assertStringContainsString('"Siti Nurhaliza"', $content);
        $this->assertStringContainsString(";79;", $content);
        $this->assertStringContainsString('"Memahami statistik dasar"', $content);
        $this->assertStringContainsString('"Perlu latihan aljabar"', $content);
    }

    /**
     * Test 4: Integritas Format & Konten Stream RDM Kemenag (Rapor Digital Madrasah).
     */
    public function test_rdm_streamed_content_integrity(): void
    {
        $response = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-rdm', [$this->classA1, $this->subjectMath])
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('RDM_X-MIPA-1_MTK_Sem1_2026-2027.csv', (string) $disposition);

        $content = $response->streamedContent();

        // Validasi UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Validasi Header RDM Kemenag
        $this->assertStringContainsString('NO;NISN;"NAMA SISWA";NILAI_PENGETAHUAN;DESKRIPSI_CAPAIAN', $content);

        // Validasi Konten Deskripsi Capaian Tergabung
        $this->assertStringContainsString('"Ahmad Dahlan"', $content);
        $this->assertStringContainsString(";88;", $content);
        $this->assertStringContainsString('"Tercapai optimal: Menguasai persamaan kuadrat dengan sangat baik; Perlu peningkatan: Perlu peningkatan dalam trigonometri"', $content);

        $this->assertStringContainsString('"Siti Nurhaliza"', $content);
        $this->assertStringContainsString(";79;", $content);
        $this->assertStringContainsString('"Tercapai optimal: Memahami statistik dasar; Perlu peningkatan: Perlu latihan aljabar"', $content);
    }

    /**
     * Test 5: Integritas Format & Konten Stream Leger Lengkap & Dapodik.
     */
    public function test_class_leger_export_integrity(): void
    {
        $response = $this->actingAs($this->teacherA1)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Leger_Dapodik_X-MIPA-1_Sem1_2026-2027.csv', (string) $disposition);

        $content = $response->streamedContent();

        // Validasi UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Validasi Header Leger (Kolom Dinamis Mata Pelajaran)
        $this->assertStringContainsString('No;NISN;NIS;"Nama Siswa";"Jenis Kelamin";Matematika;"Bahasa Inggris";Rata-rata;Sakit;Izin;Alpa;"Catatan Wali Kelas"', $content);

        // Validasi Baris Ahmad Dahlan: Gender L, Nilai MTK: 88.0, ING: 92.0, Rata-rata: 90.00, S: 2, I: 1, A: 0
        $this->assertStringContainsString(';0012345678;1001;"Ahmad Dahlan";L;88.0;92.0;90.00;2;1;0;"Prestasi belajar sangat memuaskan, pertahankan di semester berikutnya."', $content);

        // Validasi Baris Siti Nurhaliza: Gender P, Nilai MTK: 78.5, ING: 85.0, Rata-rata: 81.75, S: 0, I: 0, A: 1
        $this->assertStringContainsString(';0012345679;1002;"Siti Nurhaliza";P;78.5;85.0;81.75;0;0;1;"Tingkatkan kedisiplinan dan rajin berlatih."', $content);
    }

    /**
     * Test 6: Guard Tahun Ajaran Aktif (Unprocessable Entity 422 jika tidak ada TA aktif).
     */
    public function test_no_active_academic_year_guard(): void
    {
        // Nonaktifkan tahun ajaran aktif tenant A
        $this->yearA->update(['is_active' => false]);

        // 1. Coba ekspor e-Rapor -> 422
        $responseERapor = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-erapor', [$this->classA1, $this->subjectMath])
        );
        $responseERapor->assertStatus(422);

        // 2. Coba ekspor RDM -> 422
        $responseRDM = $this->actingAs($this->teacherA1)->get(
            route('teacher.gradebook.export-rdm', [$this->classA1, $this->subjectMath])
        );
        $responseRDM->assertStatus(422);

        // 3. Coba ekspor Leger -> 422
        $responseLeger = $this->actingAs($this->teacherA1)->get(
            route('homeroom.reports.export-leger', $this->classA1)
        );
        $responseLeger->assertStatus(422);
    }
}
