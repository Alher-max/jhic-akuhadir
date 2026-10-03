<?php

declare(strict_types=1);

namespace Tests\Feature\Vocational;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\InternshipAssessment;
use App\Models\InternshipPlacement;
use App\Models\Location;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VocationalCompetencyAssessment;
use App\Services\VocationalReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VocationalModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected AcademicYear $yearA;
    protected AcademicYear $yearB;
    protected SchoolClass $classA1;
    protected SchoolClass $classA2;
    protected SchoolClass $classB1;
    protected User $teacherHomeroomA;
    protected User $teacherSupervisorA;
    protected User $teacherOtherA;
    protected User $operatorA;
    protected User $headmasterA;
    protected User $studentA1;
    protected User $studentA2;
    protected User $parentA1;
    protected User $studentB1;
    protected User $teacherB1;
    protected Location $locationA;
    protected Location $locationAOther;
    protected InternshipPlacement $placementA1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant A (SMK Negeri 1 Maju Bersama)
        $this->tenantA = Tenant::create([
            'name' => 'SMK Negeri 1 Maju Bersama',
            'slug' => 'smkn-1-maju-bersama',
            'code' => 'SMKN1MB',
            'institution_type' => 'school',
            'npsn' => '20108877',
            'address' => 'Jl. Industri Vokasi No. 10, Bandung',
            'city' => 'Bandung',
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

        $this->teacherHomeroomA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Ir. Hendra Gunawan, M.T.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherSupervisorA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Rina Wijaya, S.Kom.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherOtherA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Bambang Sudibyo, S.Pd.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->operatorA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'operator',
            'name' => 'Operator SMK',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->headmasterA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'headmaster',
            'name' => 'Drs. H. Mulyadi, M.M.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->classA1 = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'XII-RPL-1',
            'jenjang' => 'SMK',
            'tingkat' => 12,
            'fase' => 'F',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->teacherHomeroomA->id,
        ]);

        $this->classA2 = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'XII-TKJ-1',
            'jenjang' => 'SMK',
            'tingkat' => 12,
            'fase' => 'F',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->teacherOtherA->id,
        ]);

        $this->studentA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Bayu Pratama',
            'nisn' => '0054321001',
            'nis' => '1201',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->studentA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Citra Lestari',
            'nisn' => '0054321002',
            'nis' => '1202',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->parentA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'name' => 'Joko Pratama',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
        $this->parentA1->students()->attach($this->studentA1->id);

        $this->locationA = Location::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'PT Solusi Teknologi Indonesia',
            'type' => 'client',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
            'radius' => 100,
        ]);

        $this->locationAOther = Location::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Bengkel Inovasi Mandiri',
            'type' => 'client',
            'latitude' => -6.920000,
            'longitude' => 107.610000,
            'radius' => 100,
        ]);

        $this->placementA1 = InternshipPlacement::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'teacher_supervisor_id' => $this->teacherSupervisorA->id,
            'industry_location_id' => $this->locationA->id,
            'company_name' => 'PT Solusi Teknologi Indonesia',
            'company_address' => 'Kawasan Industri Gedebage, Bandung',
            'mentor_name' => 'Andi Susanto, S.T.',
            'mentor_position' => 'Lead Software Engineer',
            'start_date' => '2026-08-01',
            'end_date' => '2026-11-30',
        ]);

        // 2. Setup Tenant B (SMK Swasta Mandiri)
        $this->tenantB = Tenant::create([
            'name' => 'SMK Swasta Mandiri',
            'slug' => 'smk-swasta-mandiri',
            'code' => 'SMKSM',
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
            'nama_kelas' => 'XII-MM-1',
            'jenjang' => 'SMK',
            'tingkat' => 12,
            'wali_kelas_id' => $this->teacherB1->id,
        ]);

        $this->studentB1 = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'student',
            'class_id' => $this->classB1->id,
            'name' => 'Dani Ramadhan',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    /**
     * Test 1: Otorisasi Berjenjang Modul Vokasi (PKL & UKK).
     */
    public function test_vocational_authorization(): void
    {
        // 1. Wali Kelas (teacherHomeroomA) berhak membuka daftar PKL & form pendaftaran PKL
        $this->actingAs($this->teacherHomeroomA)
            ->get(route('vocational.internships.index', $this->classA1))
            ->assertOk();

        $this->actingAs($this->teacherHomeroomA)
            ->get(route('vocational.internships.create', $this->classA1))
            ->assertOk();

        // 2. Guru Pembimbing (teacherSupervisorA) berhak membuka lembar input penilaian PKL
        $this->actingAs($this->teacherSupervisorA)
            ->get(route('vocational.internships.assessment.show', $this->placementA1))
            ->assertOk();

        // 3. Guru Luar (teacherOtherA) yang bukan wali kelas atau pembimbing ditolak (403)
        $this->actingAs($this->teacherOtherA)
            ->get(route('vocational.internships.index', $this->classA1))
            ->assertStatus(403);

        $this->actingAs($this->teacherOtherA)
            ->get(route('vocational.internships.assessment.show', $this->placementA1))
            ->assertStatus(403);

        // 4. Siswa dan Orang Tua ditolak dari halaman manajemen & input PKL (403)
        $this->actingAs($this->studentA1)
            ->get(route('vocational.internships.index', $this->classA1))
            ->assertStatus(403);

        $this->actingAs($this->parentA1)
            ->get(route('vocational.internships.assessment.show', $this->placementA1))
            ->assertStatus(403);

        // 5. Operator dan Kepala Sekolah memiliki hak supervisi penuh (200)
        $this->actingAs($this->operatorA)
            ->get(route('vocational.internships.index', $this->classA1))
            ->assertOk();

        $this->actingAs($this->headmasterA)
            ->get(route('vocational.internships.assessment.show', $this->placementA1))
            ->assertOk();

        // 6. Wali Kelas berhak mengakses modul UKK (index & batch store)
        $this->actingAs($this->teacherHomeroomA)
            ->get(route('vocational.ukk.index', $this->classA1))
            ->assertOk();

        // Guru lain ditolak dari modul UKK rombel ini
        $this->actingAs($this->teacherOtherA)
            ->get(route('vocational.ukk.index', $this->classA1))
            ->assertStatus(403);

        // Siswa dan orang tua ditolak dari modul UKK
        $this->actingAs($this->studentA1)
            ->get(route('vocational.ukk.index', $this->classA1))
            ->assertStatus(403);
    }

    /**
     * Test 2: Isolasi Multi-Tenant Vokasi (Anti Cross-Tenant Breach).
     */
    public function test_vocational_tenant_isolation(): void
    {
        // Pendidik Tenant B mencoba mengakses kelas atau penempatan milik Tenant A -> 404
        $this->actingAs($this->teacherB1)
            ->get(route('vocational.internships.index', $this->classA1))
            ->assertStatus(404);

        $this->actingAs($this->teacherB1)
            ->get(route('vocational.internships.create', $this->classA1))
            ->assertStatus(404);

        $this->actingAs($this->teacherB1)
            ->get(route('vocational.internships.assessment.show', $this->placementA1))
            ->assertStatus(404);

        $this->actingAs($this->teacherB1)
            ->post(route('vocational.internships.assessment.store', $this->placementA1), [
                'technical_score' => 85,
                'softskill_score' => 85,
                'attendance_score' => 90,
            ])
            ->assertStatus(404);

        $this->actingAs($this->teacherB1)
            ->get(route('vocational.ukk.index', $this->classA1))
            ->assertStatus(404);

        $this->actingAs($this->teacherB1)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertStatus(404);

        $this->actingAs($this->teacherB1)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]))
            ->assertStatus(404);
    }

    /**
     * Test 3: Auto-Pull Log Presensi HadirYuk & Filter Geofence Lokasi Industri.
     */
    public function test_internship_attendance_autopull_calculation(): void
    {
        $service = app(VocationalReportService::class);

        // 1. Tanpa log presensi apa pun -> skor default 100.00
        $scoreEmpty = $service->calculatePlacementAttendanceScore($this->placementA1);
        $this->assertEquals(100.00, $scoreEmpty);

        // 2. Buat 10 log presensi di lokasi industri DUDI (locationA)
        // 7 hadir (present), 1 terlambat (late - dihitung hadir), 1 sakit (sick), 1 alpha (alpha)
        // Expected present = 8, total = 10 -> (8/10) * 100 = 80.00%
        for ($i = 1; $i <= 7; $i++) {
            Attendance::create([
                'tenant_id' => $this->tenantA->id,
                'user_id' => $this->studentA1->id,
                'location_id' => $this->locationA->id,
                'attendance_type' => 'school',
                'date' => sprintf('2026-08-%02d', $i),
                'status' => 'present',
            ]);
        }

        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'location_id' => $this->locationA->id,
            'attendance_type' => 'school',
            'date' => '2026-08-08',
            'status' => 'late',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'location_id' => $this->locationA->id,
            'attendance_type' => 'school',
            'date' => '2026-08-09',
            'status' => 'sick',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'location_id' => $this->locationA->id,
            'attendance_type' => 'school',
            'date' => '2026-08-10',
            'status' => 'alpha',
        ]);

        $scoreWithAttendances = $service->calculatePlacementAttendanceScore($this->placementA1);
        $this->assertEquals(80.00, $scoreWithAttendances);

        // 3. Tambahkan 2 log presensi lain di luar industri DUDI (misal bengkel lain / sekolah)
        // Karena placementA1 mengunci 'industry_location_id' ke locationA, log lokasi lain tidak boleh mempengaruhi skor
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'location_id' => $this->locationAOther->id,
            'attendance_type' => 'school',
            'date' => '2026-08-11',
            'status' => 'present',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA1->id,
            'location_id' => $this->locationAOther->id,
            'attendance_type' => 'school',
            'date' => '2026-08-12',
            'status' => 'present',
        ]);

        $scoreFiltered = $service->calculatePlacementAttendanceScore($this->placementA1);
        $this->assertEquals(80.00, $scoreFiltered);

        // 4. Jika penempatan tidak memiliki industry_location_id spesifik, seluruh log dalam rentang tanggal dihitung
        $placementWithoutLocation = InternshipPlacement::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA2->id,
            'teacher_supervisor_id' => $this->teacherSupervisorA->id,
            'industry_location_id' => null,
            'company_name' => 'Kantor Desa Mandiri',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-30',
        ]);

        // Beri studentA2 4 log: 3 present, 1 alpha -> 75%
        for ($i = 1; $i <= 3; $i++) {
            Attendance::create([
                'tenant_id' => $this->tenantA->id,
                'user_id' => $this->studentA2->id,
                'location_id' => null,
                'attendance_type' => 'school',
                'date' => sprintf('2026-08-%02d', $i),
                'status' => 'present',
            ]);
        }
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA2->id,
            'location_id' => null,
            'attendance_type' => 'school',
            'date' => '2026-08-04',
            'status' => 'alpha',
        ]);

        $scoreUnrestricted = $service->calculatePlacementAttendanceScore($placementWithoutLocation);
        $this->assertEquals(75.00, $scoreUnrestricted);
    }

    /**
     * Test 4: Kalkulasi Nilai Terbobot PKL, Predikat Kualitatif, & Penilaian Massal UKK.
     */
    public function test_final_score_and_predicate_calculation(): void
    {
        $service = app(VocationalReportService::class);

        // 1. Verifikasi kalkulasi service: 50% Teknis + 30% Softskill + 20% Kehadiran
        // 90 * 0.5 = 45; 80 * 0.3 = 24; 100 * 0.2 = 20 -> 45 + 24 + 20 = 89.00
        $finalA = $service->calculateFinalScore(90.0, 80.0, 100.0);
        $this->assertEquals(89.00, $finalA);
        $this->assertEquals('Sangat Baik', $service->determinePredicate($finalA));

        // 76 * 0.5 = 38; 75 * 0.3 = 22.5; 74 * 0.2 = 14.8 -> 75.30
        $finalB = $service->calculateFinalScore(76.0, 75.0, 74.0);
        $this->assertEquals(75.30, $finalB);
        $this->assertEquals('Baik', $service->determinePredicate($finalB));

        // 60 * 0.5 = 30; 70 * 0.3 = 21; 60 * 0.2 = 12 -> 63.00
        $finalC = $service->calculateFinalScore(60.0, 70.0, 60.0);
        $this->assertEquals(63.00, $finalC);
        $this->assertEquals('Cukup', $service->determinePredicate($finalC));

        // 2. Verifikasi predikat UKK
        $this->assertEquals('Sangat Kompeten', $service->determineUKKPredicate(95.0));
        $this->assertEquals('Sangat Kompeten', $service->determineUKKPredicate(85.0));
        $this->assertEquals('Kompeten', $service->determineUKKPredicate(84.9));
        $this->assertEquals('Kompeten', $service->determineUKKPredicate(70.0));
        $this->assertEquals('Belum Kompeten', $service->determineUKKPredicate(69.9));

        // 3. Simpan Penilaian PKL via HTTP Controller
        $response = $this->actingAs($this->teacherSupervisorA)
            ->post(route('vocational.internships.assessment.store', $this->placementA1), [
                'technical_score' => 92,
                'softskill_score' => 88,
                'attendance_score' => 95,
                'technical_notes' => 'Sangat menguasai framework Laravel dan arsitektur database modern.',
                'softskill_notes' => 'Komunikatif, disiplin, dan memiliki integritas tinggi saat bertugas.',
            ]);

        $response->assertRedirect(route('vocational.internships.assessment.show', $this->placementA1));
        $response->assertSessionHas('success');

        // Verifikasi tersimpan di database dengan nilai akhir terbobot dan predikat
        // (92 * 0.5 = 46) + (88 * 0.3 = 26.4) + (95 * 0.2 = 19) = 91.40
        $assessment = InternshipAssessment::where('internship_placement_id', $this->placementA1->id)->first();
        $this->assertNotNull($assessment);
        $this->assertEquals(91.40, $assessment->final_score);
        $this->assertEquals('Sangat Baik', $assessment->predicate);
        $this->assertStringContainsString('Laravel', $assessment->technical_notes);

        // 4. Simpan Massal Penilaian UKK via HTTP Controller
        $ukkResponse = $this->actingAs($this->teacherHomeroomA)
            ->post(route('vocational.ukk.batch-store', $this->classA1), [
                'assessments' => [
                    $this->studentA1->id => [
                        'scheme_name' => 'Rekayasa Perangkat Lunak - Pemrograman Web',
                        'assessor_name' => 'Ir. Hartono (LSP Telematika)',
                        'institution_name' => 'LSP Telematika Indonesia',
                        'theory_score' => 80,
                        'practice_score' => 90,
                        'certificate_number' => 'LSP-TEL-2026-00123',
                    ],
                    $this->studentA2->id => [
                        'scheme_name' => 'Rekayasa Perangkat Lunak - Pemrograman Web',
                        'assessor_name' => 'Ir. Hartono (LSP Telematika)',
                        'institution_name' => 'LSP Telematika Indonesia',
                        'theory_score' => null, // Tanpa ujian teori (100% praktik)
                        'practice_score' => 75,
                        'certificate_number' => 'LSP-TEL-2026-00124',
                    ],
                ],
            ]);

        $ukkResponse->assertRedirect(route('vocational.ukk.index', $this->classA1));
        $ukkResponse->assertSessionHas('success');

        // Student A1: (80 * 0.3 = 24) + (90 * 0.7 = 63) = 87.00 -> 'Sangat Kompeten'
        $ukk1 = VocationalCompetencyAssessment::where('student_id', $this->studentA1->id)->first();
        $this->assertNotNull($ukk1);
        $this->assertEquals(87.00, $ukk1->final_score);
        $this->assertEquals('Sangat Kompeten', $ukk1->predicate);
        $this->assertEquals('LSP-TEL-2026-00123', $ukk1->certificate_number);

        // Student A2: 100% praktik = 75.00 -> 'Kompeten'
        $ukk2 = VocationalCompetencyAssessment::where('student_id', $this->studentA2->id)->first();
        $this->assertNotNull($ukk2);
        $this->assertEquals(75.00, $ukk2->final_score);
        $this->assertEquals('Kompeten', $ukk2->predicate);
    }

    /**
     * Test 5: Rendering Cetak Lembar Nilai PKL & Transkrip UKK Resmi A4 (Zero Server Load).
     */
    public function test_vocational_print_view_rendering(): void
    {
        // 1. Siapkan penilaian PKL
        InternshipAssessment::create([
            'tenant_id' => $this->tenantA->id,
            'internship_placement_id' => $this->placementA1->id,
            'technical_score' => 90,
            'softskill_score' => 85,
            'attendance_score' => 100,
            'final_score' => 90.5,
            'predicate' => 'Sangat Baik',
            'technical_notes' => 'Keahlian teknis back-end sangat memuaskan.',
            'softskill_notes' => 'Kedisiplinan kerja sangat tinggi.',
        ]);

        // Wali Kelas mencetak lembar PKL
        $printPKL = $this->actingAs($this->teacherHomeroomA)
            ->get(route('vocational.internships.print', $this->placementA1));

        $printPKL->assertOk();
        $printPKL->assertSee('PENILAIAN PRAKTIK KERJA LAPANGAN');
        $printPKL->assertSee('PT Solusi Teknologi Indonesia');
        $printPKL->assertSee('Andi Susanto, S.T.'); // Pembimbing Industri
        $printPKL->assertSee('Rina Wijaya, S.Kom.'); // Guru Pembimbing
        $printPKL->assertSee('Bayu Pratama'); // Siswa
        $printPKL->assertSee('90.5'); // Nilai Akhir
        $printPKL->assertSee('Sangat Baik'); // Predikat
        $printPKL->assertSee('@media print'); // Pure CSS Print Engine

        // 2. Siapkan penilaian UKK
        VocationalCompetencyAssessment::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'scheme_name' => 'Rekayasa Perangkat Lunak',
            'assessor_name' => 'Ir. Bambang Suryono',
            'institution_name' => 'LSP Informatika Nasional',
            'theory_score' => 85,
            'practice_score' => 92,
            'final_score' => 89.9,
            'predicate' => 'Sangat Kompeten',
            'certificate_number' => 'BNSP-LSP-2026-9901',
        ]);

        // Wali Kelas mencetak transkrip UKK
        $printUKK = $this->actingAs($this->teacherHomeroomA)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]));

        $printUKK->assertOk();
        $printUKK->assertSee('UJI KOMPETENSI KEAHLIAN (UKK)');
        $printUKK->assertSee('BNSP-LSP-2026-9901');
        $printUKK->assertSee('LSP Informatika Nasional');
        $printUKK->assertSee('Ir. Bambang Suryono');
        $printUKK->assertSee('Sangat Kompeten');
        $printUKK->assertSee('Drs. H. Mulyadi, M.M.'); // Kepala Sekolah
    }

    /**
     * Test 6: Publication Guard untuk Siswa & Orang Tua.
     */
    public function test_vocational_student_and_parent_publication_guard(): void
    {
        // 1. Buat penilaian PKL & UKK
        InternshipAssessment::create([
            'tenant_id' => $this->tenantA->id,
            'internship_placement_id' => $this->placementA1->id,
            'technical_score' => 90,
            'softskill_score' => 85,
            'attendance_score' => 100,
            'final_score' => 90.5,
            'predicate' => 'Sangat Baik',
        ]);

        VocationalCompetencyAssessment::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'scheme_name' => 'Rekayasa Perangkat Lunak',
            'assessor_name' => 'Ir. Bambang',
            'institution_name' => 'LSP Informatika',
            'theory_score' => 85,
            'practice_score' => 90,
            'final_score' => 88.5,
            'predicate' => 'Sangat Kompeten',
        ]);

        // 2. KONDISI 1: Belum ada StudentReport atau status masih 'draft'
        // Siswa dan orang tua wajib ditolak (403 Forbidden)
        $this->actingAs($this->studentA1)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertStatus(403);

        $this->actingAs($this->parentA1)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertStatus(403);

        $this->actingAs($this->studentA1)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]))
            ->assertStatus(403);

        $this->actingAs($this->parentA1)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]))
            ->assertStatus(403);

        // Buat StudentReport dengan status 'draft' -> tetap 403
        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->studentA1)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertStatus(403);

        // 3. KONDISI 2: StudentReport berstatus 'published'
        $report->update(['status' => 'published']);

        $this->actingAs($this->studentA1)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertOk();

        $this->actingAs($this->parentA1)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertOk();

        $this->actingAs($this->studentA1)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]))
            ->assertOk();

        $this->actingAs($this->parentA1)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]))
            ->assertOk();

        // 4. KONDISI 3: Isolasi antar-siswa (Student A2 tidak boleh melihat rapor PKL/UKK milik Student A1)
        $this->actingAs($this->studentA2)
            ->get(route('vocational.internships.print', $this->placementA1))
            ->assertStatus(403);

        $this->actingAs($this->studentA2)
            ->get(route('vocational.ukk.print', ['class' => $this->classA1, 'student' => $this->studentA1]))
            ->assertStatus(403);
    }

    /**
     * Test 7: CRUD Lifecycle Pendaftaran Penempatan PKL.
     */
    public function test_internship_placement_management(): void
    {
        // Pendaftaran penempatan PKL baru untuk studentA2 oleh Wali Kelas
        $response = $this->actingAs($this->teacherHomeroomA)
            ->post(route('vocational.internships.store', $this->classA1), [
                'class_id' => $this->classA1->id,
                'student_id' => $this->studentA2->id,
                'teacher_supervisor_id' => $this->teacherSupervisorA->id,
                'industry_location_id' => $this->locationA->id,
                'company_name' => 'PT Mitra Integrasi Mandiri',
                'company_address' => 'Jl. Soekarno Hatta No. 500, Bandung',
                'mentor_name' => 'Dewi Anggraeni, S.T.',
                'mentor_position' => 'Senior QA Specialist',
                'start_date' => '2026-08-01',
                'end_date' => '2026-11-30',
            ]);

        $response->assertRedirect(route('vocational.internships.index', $this->classA1));
        $response->assertSessionHas('success');

        $newPlacement = InternshipPlacement::where('student_id', $this->studentA2->id)->first();
        $this->assertNotNull($newPlacement);
        $this->assertEquals('PT Mitra Integrasi Mandiri', $newPlacement->company_name);

        // Hapus penempatan PKL
        $deleteResponse = $this->actingAs($this->teacherHomeroomA)
            ->delete(route('vocational.internships.destroy', $newPlacement));

        $deleteResponse->assertRedirect(route('vocational.internships.index', $this->classA1));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('internship_placements', [
            'id' => $newPlacement->id,
        ]);
    }
}
