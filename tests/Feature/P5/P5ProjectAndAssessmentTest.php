<?php

declare(strict_types=1);

namespace Tests\Feature\P5;

use App\Models\AcademicYear;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\P5ProjectTarget;
use App\Models\P5StudentNote;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class P5ProjectAndAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected AcademicYear $yearA;
    protected AcademicYear $yearB;
    protected SchoolClass $classA1;
    protected SchoolClass $classA2;
    protected SchoolClass $classB1;
    protected User $teacherA1; // Wali kelas classA1
    protected User $teacherA2; // Koordinator projek classA1 (bukan wali kelas classA1)
    protected User $teacherA3; // Guru luar di tenant A
    protected User $operatorA;
    protected User $headmasterA;
    protected User $studentA1;
    protected User $studentA2;
    protected User $parentA;
    protected User $teacherB1;
    protected P5Project $projectA1;
    protected P5ProjectTarget $targetA1;
    protected P5ProjectTarget $targetA2;

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
            'name' => 'Budi Santoso, S.Pd.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Dewi Sartika, M.Pd.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherA3 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Agus Salim, S.Si.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->operatorA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'operator',
            'name' => 'Operator Sekolah',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->headmasterA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'headmaster',
            'name' => 'Dr. H. Sudirman, M.Pd.',
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
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->studentA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Siti Walidah',
            'nisn' => '0012345679',
            'nis' => '1002',
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

        // Projek P5 di Class A1: Fasilitator adalah Teacher A2
        $this->projectA1 = P5Project::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'coordinator_id' => $this->teacherA2->id,
            'theme' => 'Gaya Hidup Berkelanjutan',
            'title' => 'Pengolahan Sampah Organik Menjadi Kompos',
            'description' => 'Membangun kesadaran lingkungan dan keterampilan praktis daur ulang sampah organik di sekolah.',
        ]);

        $this->targetA1 = P5ProjectTarget::create([
            'tenant_id' => $this->tenantA->id,
            'p5_project_id' => $this->projectA1->id,
            'target_type' => 'pancasila',
            'dimension' => 'Gotong Royong',
            'element' => 'Kolaborasi',
            'sub_element' => 'Kerja Sama dan Koordinasi Positif',
            'target_description' => 'Membangun tim kerja yang kompak dan berinisiatif aktif.',
        ]);

        $this->targetA2 = P5ProjectTarget::create([
            'tenant_id' => $this->tenantA->id,
            'p5_project_id' => $this->projectA1->id,
            'target_type' => 'rahmatan_lil_alamin',
            'dimension' => 'Tawassut (Moderat)',
            'element' => 'Karakter Kemenag',
            'sub_element' => 'Menjaga Keseimbangan Ekosistem',
            'target_description' => 'Menunjukkan kepedulian terhadap kelestarian alam dan lingkungan hidup.',
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
    }

    /**
     * Test 1: Otorisasi Berjenjang Modul P5 & P5RA.
     */
    public function test_p5_authorization(): void
    {
        // 1. Wali Kelas (Teacher A1) berhak membuka form buat projek & menyimpan projek
        $this->actingAs($this->teacherA1)
            ->get(route('p5.projects.create', $this->classA1))
            ->assertOk();

        $createResponse = $this->actingAs($this->teacherA1)->post(route('p5.projects.store', $this->classA1), [
            'theme' => 'Kewirausahaan',
            'title' => 'Bazar Kuliner Nusantara Berkelanjutan',
            'description' => 'Melatih jiwa kewirausahaan siswa melalui bazar kuliner tradisional.',
            'coordinator_id' => $this->teacherA1->id,
            'targets' => [
                [
                    'target_type' => 'pancasila',
                    'dimension' => 'Mandiri',
                    'element' => 'Regulasi Diri',
                    'sub_element' => 'Percaya Diri dan Tangguh',
                    'target_description' => 'Mampu memimpin usaha kecil dengan penuh tanggung jawab.',
                ],
            ],
        ]);
        $createResponse->assertRedirect();
        $this->assertDatabaseHas('p5_projects', [
            'tenant_id' => $this->tenantA->id,
            'title' => 'Bazar Kuliner Nusantara Berkelanjutan',
        ]);

        // 2. Koordinator Projek (Teacher A2) berhak melihat show & lembar matriks penilaian projek
        $this->actingAs($this->teacherA2)
            ->get(route('p5.projects.show', $this->projectA1))
            ->assertOk();

        $this->actingAs($this->teacherA2)
            ->get(route('p5.assessments.matrix', $this->projectA1))
            ->assertOk();

        // 3. Guru Luar (Teacher A3) yang bukan fasilitator / bukan wali kelas ditolak 403 Forbidden
        $this->actingAs($this->teacherA3)
            ->get(route('p5.projects.create', $this->classA1))
            ->assertForbidden();

        $this->actingAs($this->teacherA3)
            ->get(route('p5.assessments.matrix', $this->projectA1))
            ->assertForbidden();

        // 4. Siswa dan Orang Tua ditolak dari rute manipulasi data projek (403 Forbidden)
        $this->actingAs($this->studentA1)
            ->get(route('p5.projects.create', $this->classA1))
            ->assertForbidden();

        $this->actingAs($this->studentA1)
            ->post(route('p5.assessments.batch-store', $this->projectA1), [])
            ->assertForbidden();

        $this->actingAs($this->parentA)
            ->get(route('p5.projects.show', $this->projectA1))
            ->assertForbidden();

        // 5. Operator & Kepala Sekolah memiliki hak supervisi penuh (200 OK)
        $this->actingAs($this->operatorA)
            ->get(route('p5.projects.create', $this->classA1))
            ->assertOk();

        $this->actingAs($this->headmasterA)
            ->get(route('p5.assessments.matrix', $this->projectA1))
            ->assertOk();
    }

    /**
     * Test 2: Isolasi Multi-Tenant Ketat (Cross-Tenant Access Rejection).
     */
    public function test_p5_tenant_isolation(): void
    {
        // Guru Tenant B mencoba melihat projek Tenant A -> 404 Not Found
        $this->actingAs($this->teacherB1)
            ->get(route('p5.projects.show', $this->projectA1))
            ->assertNotFound();

        // Guru Tenant B mencoba membuka matriks penilaian Tenant A -> 404 Not Found
        $this->actingAs($this->teacherB1)
            ->get(route('p5.assessments.matrix', $this->projectA1))
            ->assertNotFound();

        // Guru Tenant B mencoba menyimpan asesmen di projek Tenant A -> 404 Not Found
        $this->actingAs($this->teacherB1)
            ->post(route('p5.assessments.batch-store', $this->projectA1), [
                'assessments' => [
                    $this->studentA1->id => [
                        $this->targetA1->id => 'BSH',
                    ],
                ],
            ])
            ->assertNotFound();

        // Guru Tenant B mencoba cetak massal projek Tenant A -> 404 Not Found
        $this->actingAs($this->teacherB1)
            ->get(route('p5.reports.print-batch', $this->projectA1))
            ->assertNotFound();
    }

    /**
     * Test 3: Validasi Predikat Capaian Asesmen (Hanya MB, SB, BSH, SAB).
     */
    public function test_p5_assessment_predicate_validation(): void
    {
        // Input predikat tidak sah ('XYZ') harus ditolak dengan error validasi
        $responseInvalid = $this->actingAs($this->teacherA2)
            ->from(route('p5.assessments.matrix', $this->projectA1))
            ->post(route('p5.assessments.batch-store', $this->projectA1), [
                'assessments' => [
                    $this->studentA1->id => [
                        $this->targetA1->id => 'XYZ',
                    ],
                ],
            ]);

        $responseInvalid->assertSessionHasErrors("assessments.{$this->studentA1->id}.{$this->targetA1->id}");

        // Input predikat sah ('BSH' dan 'SAB') harus diterima dan disimpan
        $responseValid = $this->actingAs($this->teacherA2)
            ->post(route('p5.assessments.batch-store', $this->projectA1), [
                'assessments' => [
                    $this->studentA1->id => [
                        $this->targetA1->id => 'BSH',
                        $this->targetA2->id => 'SAB',
                    ],
                ],
                'notes' => [
                    $this->studentA1->id => 'Menunjukkan partisipasi aktif dalam penyusunan proposal projek.',
                ],
            ]);

        $responseValid->assertRedirect(route('p5.assessments.matrix', $this->projectA1));
        $responseValid->assertSessionHas('success');

        $this->assertDatabaseHas('p5_assessments', [
            'tenant_id' => $this->tenantA->id,
            'p5_project_id' => $this->projectA1->id,
            'p5_project_target_id' => $this->targetA1->id,
            'student_id' => $this->studentA1->id,
            'predicate' => 'BSH',
        ]);

        $this->assertDatabaseHas('p5_assessments', [
            'tenant_id' => $this->tenantA->id,
            'p5_project_id' => $this->projectA1->id,
            'p5_project_target_id' => $this->targetA2->id,
            'student_id' => $this->studentA1->id,
            'predicate' => 'SAB',
        ]);
    }

    /**
     * Test 4: Persistensi Asesmen & Bulk Upsert Tanpa Duplikasi Rekord.
     */
    public function test_p5_assessment_persistence_and_bulk_upsert(): void
    {
        // Batch 1: Simpan nilai awal siswa
        $this->actingAs($this->teacherA2)->post(route('p5.assessments.batch-store', $this->projectA1), [
            'assessments' => [
                $this->studentA1->id => [
                    $this->targetA1->id => 'SB',
                ],
                $this->studentA2->id => [
                    $this->targetA1->id => 'MB',
                ],
            ],
            'notes' => [
                $this->studentA1->id => 'Catatan awal Ahmad Dahlan',
                $this->studentA2->id => 'Catatan awal Siti Walidah',
            ],
        ])->assertRedirect();

        $this->assertEquals(2, P5Assessment::where('p5_project_id', $this->projectA1->id)->count());
        $this->assertEquals(2, P5StudentNote::where('p5_project_id', $this->projectA1->id)->count());

        // Batch 2: Simpan perubahan nilai untuk Ahmad Dahlan dari 'SB' menjadi 'SAB'
        $this->actingAs($this->teacherA2)->post(route('p5.assessments.batch-store', $this->projectA1), [
            'assessments' => [
                $this->studentA1->id => [
                    $this->targetA1->id => 'SAB', // Nilai diubah
                ],
                $this->studentA2->id => [
                    $this->targetA1->id => 'BSH', // Nilai diubah
                ],
            ],
            'notes' => [
                $this->studentA1->id => 'Catatan perkembangan akhir Ahmad Dahlan',
            ],
        ])->assertRedirect();

        // Jumlah record tetap 2 (tidak terjadi duplikasi), namun nilai ter-update
        $this->assertEquals(2, P5Assessment::where('p5_project_id', $this->projectA1->id)->count());
        $this->assertDatabaseHas('p5_assessments', [
            'p5_project_target_id' => $this->targetA1->id,
            'student_id' => $this->studentA1->id,
            'predicate' => 'SAB',
        ]);
        $this->assertDatabaseHas('p5_assessments', [
            'p5_project_target_id' => $this->targetA1->id,
            'student_id' => $this->studentA2->id,
            'predicate' => 'BSH',
        ]);
        $this->assertDatabaseHas('p5_student_notes', [
            'student_id' => $this->studentA1->id,
            'process_notes' => 'Catatan perkembangan akhir Ahmad Dahlan',
        ]);
    }

    /**
     * Test 5: Rendering Tampilan Cetak Lembar Rapor P5 (Single & Batch Print View).
     */
    public function test_p5_print_view_rendering(): void
    {
        // Siapkan data penilaian dan catatan
        P5Assessment::create([
            'tenant_id' => $this->tenantA->id,
            'p5_project_id' => $this->projectA1->id,
            'p5_project_target_id' => $this->targetA1->id,
            'student_id' => $this->studentA1->id,
            'predicate' => 'BSH',
        ]);

        P5StudentNote::create([
            'tenant_id' => $this->tenantA->id,
            'p5_project_id' => $this->projectA1->id,
            'student_id' => $this->studentA1->id,
            'process_notes' => 'Ahmad sangat proaktif dalam kegiatan daur ulang kompos.',
        ]);

        // 1. Cetak Single Rapor P5 Siswa 1 (Wali Kelas / Koordinator)
        $singlePrintResponse = $this->actingAs($this->teacherA1)->get(
            route('p5.reports.print-single', [$this->projectA1, $this->studentA1])
        );

        $singlePrintResponse->assertOk();
        $singlePrintResponse->assertSee('LAPORAN HASIL PROJEK PENGUATAN PROFIL PELAJAR');
        $singlePrintResponse->assertSee($this->studentA1->name);
        $singlePrintResponse->assertSee('Pengolahan Sampah Organik Menjadi Kompos');
        $singlePrintResponse->assertSee('Gotong Royong');
        $singlePrintResponse->assertSee('Kerja Sama dan Koordinasi Positif');
        $singlePrintResponse->assertSee('Ahmad sangat proaktif dalam kegiatan daur ulang kompos.');
        $singlePrintResponse->assertSee('✓'); // Centang predikat BSH
        $singlePrintResponse->assertSee($this->teacherA2->name); // Nama Fasilitator

        // 2. Cetak Massal Rapor P5 Rombel Kelas
        $batchPrintResponse = $this->actingAs($this->teacherA1)->get(
            route('p5.reports.print-batch', $this->projectA1)
        );

        $batchPrintResponse->assertOk();
        $batchPrintResponse->assertSee($this->studentA1->name);
        $batchPrintResponse->assertSee($this->studentA2->name);
        $batchPrintResponse->assertSee('page-break'); // CSS pemisah halaman A4
    }

    /**
     * Test 6: Publication Guard untuk Akses Rapor P5 Mandiri Siswa & Orang Tua.
     */
    public function test_p5_student_and_parent_publication_guard(): void
    {
        // 1. Sebelum Rapor dipublikasikan (Draft atau belum ada StudentReport) -> Siswa ditolak 403
        $this->actingAs($this->studentA1)->get(
            route('p5.reports.print-single', [$this->projectA1, $this->studentA1])
        )->assertForbidden();

        // 2. Buat StudentReport berstatus 'published' untuk siswa ini
        StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'status' => 'published',
            'published_at' => now(),
            'verification_hash' => hash('sha256', 'report-published-hash'),
        ]);

        // 3. Setelah Rapor dipublikasikan -> Siswa dapat melihat & mencetak rapor P5 miliknya
        $this->actingAs($this->studentA1)->get(
            route('p5.reports.print-single', [$this->projectA1, $this->studentA1])
        )->assertOk();

        // 4. Orang Tua siswa juga dapat melihat & mencetak rapor P5 anaknya
        $this->actingAs($this->parentA)->get(
            route('p5.reports.print-single', [$this->projectA1, $this->studentA1])
        )->assertOk();

        // 5. Siswa tidak dapat mengintip rapor P5 siswa lain (403 Forbidden)
        $this->actingAs($this->studentA1)->get(
            route('p5.reports.print-single', [$this->projectA1, $this->studentA2])
        )->assertForbidden();
    }
}
