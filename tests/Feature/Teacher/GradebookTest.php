<?php

declare(strict_types=1);

namespace Tests\Feature\Teacher;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\LearningObjective;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectGrade;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradebookTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected AcademicYear $yearA;
    protected AcademicYear $yearB;
    protected SchoolClass $classA;
    protected SchoolClass $classB;
    protected Subject $subjectA;
    protected Subject $subjectB;
    protected User $teacherA;
    protected User $teacherB;
    protected User $studentA1;
    protected User $studentA2;
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

        $this->classA = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'X-MIPA-1',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
        ]);

        $this->subjectA = Subject::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'MTK',
            'name' => 'Matematika',
        ]);

        $this->subjectB = Subject::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'BIN',
            'name' => 'Bahasa Indonesia',
        ]);

        $this->teacherA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherB = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->studentA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA->id,
            'name' => 'Ahmad Dahlan',
            'nisn' => '0012345678',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->studentA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA->id,
            'name' => 'Budi Santoso',
            'nisn' => '0012345679',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        // Penugasan Guru A -> Kelas A + Mapel A (Matematika)
        ClassSchedule::create([
            'tenant_id' => $this->tenantA->id,
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectA->id,
            'teacher_id' => $this->teacherA->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);

        // Penugasan Guru B -> Kelas A + Mapel B (Bahasa Indonesia)
        ClassSchedule::create([
            'tenant_id' => $this->tenantA->id,
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectB->id,
            'teacher_id' => $this->teacherB->id,
            'day_name' => 'Selasa',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);

        // 2. Setup Tenant B
        $this->tenantB = Tenant::create([
            'name' => 'SMK Bina Karya',
            'slug' => 'smk-bina-karya',
            'code' => 'SMKBK',
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

        $this->classB = SchoolClass::create([
            'tenant_id' => $this->tenantB->id,
            'nama_kelas' => 'X-TKJ-1',
            'jenjang' => 'SMK',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
        ]);

        $this->studentB1 = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'student',
            'class_id' => $this->classB->id,
            'name' => 'Citra Lestari',
            'nisn' => '0098765432',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    /**
     * KASUS UJI 1: TEACHER AUTHORIZATION TEST
     * - Guru A dapat mengakses dan menginput nilai pada kelas & mapel yang ditugaskan kepadanya.
     * - Guru A ditolak dengan HTTP 403 saat mengakses kelas & mapel milik Guru B.
     * - Siswa dan Orang Tua ditolak dengan HTTP 403 pada seluruh rute buku nilai.
     */
    public function test_teacher_authorization_for_gradebook(): void
    {
        // 1. Guru A mengakses buku nilai untuk jadwalnya sendiri (Sukses 200)
        $responseA = $this->actingAs($this->teacherA)
            ->get(route('teacher.gradebook.show', [$this->classA->id, $this->subjectA->id]));
        $responseA->assertStatus(200);
        $responseA->assertSee('Ahmad Dahlan');

        // 2. Guru A menginput nilai untuk jadwalnya sendiri (Sukses)
        $postDataA = [
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectA->id,
            'grades' => [
                [
                    'student_id' => $this->studentA1->id,
                    'score' => 85.50,
                    'highest_achievement' => 'Menunjukkan penguasaan yang sangat baik dalam aljabar',
                    'lowest_achievement' => null,
                ],
            ],
        ];

        $storeResponseA = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), $postDataA);
        $storeResponseA->assertRedirect(route('teacher.gradebook.show', [$this->classA->id, $this->subjectA->id]));

        // 3. Guru A mencoba mengakses buku nilai Mapel B yang diampu oleh Guru B (Ditolak 403)
        $responseUnauthorizedGet = $this->actingAs($this->teacherA)
            ->get(route('teacher.gradebook.show', [$this->classA->id, $this->subjectB->id]));
        $responseUnauthorizedGet->assertStatus(403);

        // 4. Guru A mencoba menginput nilai Mapel B milik Guru B (Ditolak 403)
        $postDataUnauthorized = [
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectB->id,
            'grades' => [
                [
                    'student_id' => $this->studentA1->id,
                    'score' => 90.00,
                ],
            ],
        ];

        $storeResponseUnauthorized = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), $postDataUnauthorized);
        $storeResponseUnauthorized->assertStatus(403);

        // 5. Siswa dan Orang Tua ditolak dengan 403
        $parent = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'is_active' => true,
        ]);

        $this->actingAs($this->studentA1)
            ->get(route('teacher.gradebook.index'))
            ->assertStatus(403);

        $this->actingAs($parent)
            ->get(route('teacher.gradebook.index'))
            ->assertStatus(403);

        $this->actingAs($this->studentA1)
            ->post(route('teacher.gradebook.store'), $postDataA)
            ->assertStatus(403);
    }

    /**
     * KASUS UJI 2: TENANT ISOLATION TEST
     * Data TP dan nilai siswa terisolasi ketat per tenant; tidak ada kebocoran data antar-sekolah.
     */
    public function test_tenant_isolation_on_learning_objectives_and_grades(): void
    {
        // Buat TP di Tenant A
        $tpA = LearningObjective::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'subject_id' => $this->subjectA->id,
            'class_id' => $this->classA->id,
            'code' => 'TP 1',
            'description' => 'Memahami fungsi kuadrat',
        ]);

        // Buat TP di Tenant B
        $tpB = LearningObjective::create([
            'tenant_id' => $this->tenantB->id,
            'academic_year_id' => $this->yearB->id,
            'subject_id' => $this->subjectA->id, // ID sama atau berbeda
            'class_id' => $this->classB->id,
            'code' => 'TP 1',
            'description' => 'Merakit jaringan komputer LAN',
        ]);

        // Guru Tenant A mengambil daftar TP via JSON: hanya melihat milik Tenant A
        $responseTp = $this->actingAs($this->teacherA)
            ->getJson(route('teacher.learning-objectives.index', ['subject_id' => $this->subjectA->id]));

        $responseTp->assertStatus(200);
        $responseTp->assertJsonFragment(['description' => 'Memahami fungsi kuadrat']);
        $responseTp->assertJsonMissing(['description' => 'Merakit jaringan komputer LAN']);

        // Guru Tenant A mencoba mengakses kelas milik Tenant B (Ditolak 404)
        $responseCrossTenant = $this->actingAs($this->teacherA)
            ->get(route('teacher.gradebook.show', [$this->classB->id, $this->subjectA->id]));

        $responseCrossTenant->assertStatus(404);
    }

    /**
     * KASUS UJI 3: SCORE RANGE VALIDATION TEST
     * Menolak input nilai < 0, > 100, atau format non-numerik.
     */
    public function test_score_range_and_numeric_validation(): void
    {
        // 1. Nilai di bawah 0 (< 0)
        $responseUnder = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), [
                'class_id' => $this->classA->id,
                'subject_id' => $this->subjectA->id,
                'grades' => [
                    [
                        'student_id' => $this->studentA1->id,
                        'score' => -10,
                    ],
                ],
            ]);
        $responseUnder->assertSessionHasErrors(['grades.0.score']);

        // 2. Nilai di atas 100 (> 100)
        $responseOver = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), [
                'class_id' => $this->classA->id,
                'subject_id' => $this->subjectA->id,
                'grades' => [
                    [
                        'student_id' => $this->studentA1->id,
                        'score' => 105.5,
                    ],
                ],
            ]);
        $responseOver->assertSessionHasErrors(['grades.0.score']);

        // 3. Nilai non-numerik (string)
        $responseNonNumeric = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), [
                'class_id' => $this->classA->id,
                'subject_id' => $this->subjectA->id,
                'grades' => [
                    [
                        'student_id' => $this->studentA1->id,
                        'score' => 'sembilan-puluh',
                    ],
                ],
            ]);
        $responseNonNumeric->assertSessionHasErrors(['grades.0.score']);

        // 4. Nilai valid (0 dan 100 batas inklusif)
        $responseValid = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), [
                'class_id' => $this->classA->id,
                'subject_id' => $this->subjectA->id,
                'grades' => [
                    [
                        'student_id' => $this->studentA1->id,
                        'score' => 0.0,
                    ],
                    [
                        'student_id' => $this->studentA2->id,
                        'score' => 100.0,
                    ],
                ],
            ]);
        $responseValid->assertSessionHasNoErrors();
    }

    /**
     * KASUS UJI 4: BULK UPSERT & INVARIANT TEST
     * Memasukkan nilai pertama kali membuat record baru.
     * Memasukkan nilai ulang memperbarui nilai tanpa membuat record duplikat.
     */
    public function test_bulk_upsert_and_no_duplicate_records(): void
    {
        // 1. Bulk insert pertama untuk 2 siswa
        $payloadFirst = [
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectA->id,
            'grades' => [
                [
                    'student_id' => $this->studentA1->id,
                    'score' => 78.0,
                    'highest_achievement' => 'Capaian awal siswa 1',
                    'lowest_achievement' => 'Perlu peningkatan awal',
                ],
                [
                    'student_id' => $this->studentA2->id,
                    'score' => 88.0,
                    'highest_achievement' => 'Capaian awal siswa 2',
                    'lowest_achievement' => null,
                ],
            ],
        ];

        $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), $payloadFirst)
            ->assertRedirect();

        // Pastikan ada tepat 2 record di tabel subject_grades
        $this->assertEquals(2, SubjectGrade::where('tenant_id', $this->tenantA->id)->count());

        $gradeA1 = SubjectGrade::where('student_id', $this->studentA1->id)->first();
        $this->assertEquals(78.0, $gradeA1->score);
        $this->assertEquals('Capaian awal siswa 1', $gradeA1->highest_achievement);

        // 2. Bulk upsert kedua (update) untuk siswa yang sama dengan nilai & narasi baru
        $payloadSecond = [
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectA->id,
            'grades' => [
                [
                    'student_id' => $this->studentA1->id,
                    'score' => 92.5,
                    'highest_achievement' => 'Menunjukkan penguasaan yang sangat baik dalam trigonometri',
                    'lowest_achievement' => 'Perlu bimbingan dalam vektor',
                ],
                [
                    'student_id' => $this->studentA2->id,
                    'score' => 95.0,
                    'highest_achievement' => 'Menunjukkan penguasaan sangat tinggi',
                    'lowest_achievement' => null,
                ],
            ],
        ];

        $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), $payloadSecond)
            ->assertRedirect();

        // Pastikan jumlah record TETAP 2 (tidak ada duplikasi)
        $this->assertEquals(2, SubjectGrade::where('tenant_id', $this->tenantA->id)->count());

        // Verifikasi isi nilai telah terupdate
        $gradeA1Updated = SubjectGrade::where('student_id', $this->studentA1->id)->first();
        $this->assertEquals(92.5, $gradeA1Updated->score);
        $this->assertEquals('Menunjukkan penguasaan yang sangat baik dalam trigonometri', $gradeA1Updated->highest_achievement);
        $this->assertEquals('Perlu bimbingan dalam vektor', $gradeA1Updated->lowest_achievement);
    }

    /**
     * KASUS UJI 5: NO ACTIVE ACADEMIC YEAR PREVENTION TEST
     * Memastikan sistem menolak penyimpanan nilai jika tidak ada tahun ajaran aktif di tenant.
     */
    public function test_no_active_academic_year_prevention(): void
    {
        // Nonaktifkan semua tahun ajaran di Tenant A
        AcademicYear::where('tenant_id', $this->tenantA->id)->update(['is_active' => false]);

        $payload = [
            'class_id' => $this->classA->id,
            'subject_id' => $this->subjectA->id,
            'grades' => [
                [
                    'student_id' => $this->studentA1->id,
                    'score' => 85.0,
                ],
            ],
        ];

        // 1. HTML request dialihkan kembali dengan pesan error
        $response = $this->actingAs($this->teacherA)
            ->post(route('teacher.gradebook.store'), $payload);

        $response->assertSessionHas('error');
        $this->assertEquals(0, SubjectGrade::where('tenant_id', $this->tenantA->id)->count());

        // 2. JSON request mengembalikan 422 Unprocessable Entity
        $responseJson = $this->actingAs($this->teacherA)
            ->postJson(route('teacher.gradebook.store'), $payload);

        $responseJson->assertStatus(422);
        $responseJson->assertJsonFragment([
            'message' => 'Tidak ada tahun ajaran aktif. Penyimpanan nilai dibatalkan.',
        ]);
        $this->assertEquals(0, SubjectGrade::where('tenant_id', $this->tenantA->id)->count());
    }
}
