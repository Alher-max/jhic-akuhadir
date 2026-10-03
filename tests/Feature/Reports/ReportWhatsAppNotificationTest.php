<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Jobs\SendReportPublishedWhatsAppNotificationJob;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Subject;
use App\Models\SubjectGrade;
use App\Models\Tenant;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReportWhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected AcademicYear $yearA;
    protected SchoolClass $classA1;
    protected SchoolClass $classB1;
    protected User $teacherA1; // Wali kelas Class A1
    protected User $teacherA2; // Guru luar di Tenant A
    protected User $operatorA;
    protected User $headmasterA;
    protected User $studentA1;
    protected User $studentA2;
    protected User $parentA1;
    protected User $teacherB1;
    protected Subject $subjectA1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant A
        $this->tenantA = Tenant::create([
            'name' => 'SMK Negeri 1 Teknologi Digital',
            'slug' => 'smkn-1-teknologi-digital',
            'code' => 'SMKTD',
            'institution_type' => 'school',
            'npsn' => '20109911',
            'address' => 'Jl. Digital Raya No. 45, Bandung',
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

        $this->teacherA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Budi Santoso, S.Kom.',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherA2 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Agus Salim, M.Pd.',
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
            'name' => 'Dr. H. Mulyadi, M.T.',
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
            'wali_kelas_id' => $this->teacherA1->id,
        ]);

        $this->studentA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Bayu Pratama',
            'nisn' => '0054321001',
            'nis' => '1201',
            'parent_phone' => '081234567890',
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
            'parent_phone' => '085712345678',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->parentA1 = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'name' => 'Joko Pratama',
            'parent_phone' => '081234567890',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
        $this->parentA1->students()->attach($this->studentA1->id);

        $this->subjectA1 = Subject::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Pemrograman Web & Perangkat Bergerak',
            'code' => 'RPL-PWPB',
        ]);

        // 2. Setup Tenant B
        $this->tenantB = Tenant::create([
            'name' => 'SMK Swasta Mandiri',
            'slug' => 'smk-swasta-mandiri',
            'code' => 'SMKSM',
            'institution_type' => 'school',
            'onboarding_completed' => true,
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
    }

    /**
     * Test 1: Queue Dispatch On Publication.
     * Saat aksi POST /homeroom/reports/{class}/publish dieksekusi,
     * SendReportPublishedWhatsAppNotificationJob wajib di-dispatch sebanyak jumlah siswa kelas.
     */
    public function test_queue_dispatch_on_publication(): void
    {
        Queue::fake();

        // 1. Wali kelas mempublikasikan rapor kelas A1
        $response = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.publish', $this->classA1));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Rapor kelas berhasil dipublikasikan dan antrean notifikasi WhatsApp telah dikirimkan ke orang tua.');

        // Pastikan job antrean di-dispatch sebanyak 2 kali (untuk studentA1 dan studentA2)
        Queue::assertPushed(SendReportPublishedWhatsAppNotificationJob::class, 2);

        // 2. Opsi penonaktifan notifikasi WhatsApp via parameter notify_whatsapp = 0
        Queue::fake();

        $responseWithoutWa = $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.publish', $this->classA1), [
                'notify_whatsapp' => 0,
            ]);

        $responseWithoutWa->assertRedirect();
        Queue::assertNotPushed(SendReportPublishedWhatsAppNotificationJob::class);
    }

    /**
     * Test 2: Job Message Formatting & Phone Sanitization.
     * Memvalidasi sanitasi nomor 08... ke 628... dan kelengkapan teks pesan WhatsApp resmi.
     */
    public function test_job_message_formatting_and_phone_sanitization(): void
    {
        $service = app(WhatsAppNotificationService::class);

        // 1. Uji Sanitasi Nomor Telepon
        $this->assertSame('6281234567890', $service->sanitizePhoneNumber('081234567890'));
        $this->assertSame('6281234567890', $service->sanitizePhoneNumber('+62 812-3456-7890'));
        $this->assertSame('6281234567890', $service->sanitizePhoneNumber('81234567890'));
        $this->assertSame('6285712345678', $service->sanitizePhoneNumber('0857-1234-5678'));
        $this->assertNull($service->sanitizePhoneNumber(''));
        $this->assertNull($service->sanitizePhoneNumber(null));
        $this->assertNull($service->sanitizePhoneNumber('12345')); // Terlalu pendek

        // 2. Siapkan data laporan rapor dan nilai mapel
        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'sick_count' => 3,
            'permission_count' => 2,
            'alpha_count' => 1,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $report->generateVerificationHash();

        SubjectGrade::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'subject_id' => $this->subjectA1->id,
            'teacher_id' => $this->teacherA1->id,
            'student_id' => $this->studentA1->id,
            'score' => 88.50,
        ]);

        $job = new SendReportPublishedWhatsAppNotificationJob($report);

        // Verifikasi helper method pada Job
        $this->assertSame('6281234567890', $job->sanitizePhone('081234567890'));

        $message = $job->formatMessage($report);

        // Memastikan teks pesan memuat informasi krusial
        $this->assertStringContainsString('Bayu Pratama', $message);
        $this->assertStringContainsString('0054321001', $message);
        $this->assertStringContainsString('SMK Negeri 1 Teknologi Digital', $message);
        $this->assertStringContainsString('XII-RPL-1', $message);
        $this->assertStringContainsString('2026/2027', $message);
        $this->assertStringContainsString('88,50', $message); // Rata-rata nilai
        $this->assertStringContainsString('Sakit (3 hari)', $message);
        $this->assertStringContainsString('Izin (2 hari)', $message);
        $this->assertStringContainsString('Alpa (1 hari)', $message);
        $this->assertStringContainsString($report->verification_url, $message);
    }

    /**
     * Test 3: Graceful Degradation On Missing Phone Number.
     * Jika siswa atau orang tua tidak memiliki nomor telepon di profilnya, job selesai tanpa exception.
     */
    public function test_graceful_degradation_on_missing_phone_number(): void
    {
        // Siswa tanpa nomor telepon sama sekali
        $studentNoPhone = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'class_id' => $this->classA1->id,
            'name' => 'Siswa Tanpa HP',
            'parent_phone' => null,
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $studentNoPhone->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $job = new SendReportPublishedWhatsAppNotificationJob($report);

        // Eksekusi job langsung tanpa antrean
        $job->handle(app(WhatsAppNotificationService::class));

        // Tidak ada exception, status di database tetap published
        $this->assertSame('published', $report->fresh()->status);
    }

    /**
     * Test 4: Gateway Failure Resilience.
     * Mocking HTTP request gateway WhatsApp dengan response timeout / 500 error.
     * Status rapor di database tetap berstatus published dan tidak ter-rollback.
     */
    public function test_gateway_failure_resilience(): void
    {
        // Mock gateway WhatsApp gagal (500 Internal Server Error)
        Http::fake([
            '*' => Http::response(['error' => 'Gateway Connection Timeout'], 500),
        ]);

        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'wali_kelas_id' => $this->teacherA1->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $report->generateVerificationHash();

        $job = new SendReportPublishedWhatsAppNotificationJob($report);

        // Eksekusi handle langsung — harus ditangkap dengan try-catch tanpa unhandled exception
        $job->handle(app(WhatsAppNotificationService::class));

        // Verifikasi integritas data database: status tetap 'published' dan hash tetap aman
        $freshReport = $report->fresh();
        $this->assertSame('published', $freshReport->status);
        $this->assertNotNull($freshReport->verification_hash);
    }

    /**
     * Test 5: Successful Gateway Dispatching.
     * Menguji bahwa request HTTP gateway terkirim dengan payload dan header yang valid saat API sukses.
     */
    public function test_successful_gateway_dispatching(): void
    {
        Http::fake([
            '*' => Http::response(['status' => true, 'message' => 'Message sent successfully'], 200),
        ]);

        $report = StudentReport::create([
            'tenant_id' => $this->tenantA->id,
            'academic_year_id' => $this->yearA->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $report->generateVerificationHash();

        $job = new SendReportPublishedWhatsAppNotificationJob($report);
        $job->handle(app(WhatsAppNotificationService::class));

        // Verifikasi bahwa request HTTP dikirim ke endpoint dengan target nomor 6281234567890
        Http::assertSent(function ($request) {
            return $request['target'] === '6281234567890'
                && str_contains($request['message'], 'Bayu Pratama');
        });
    }

    /**
     * Test 6: Authorization & Multi-Tenant Isolation on Publication & Notification Trigger.
     */
    public function test_publication_and_notification_authorization(): void
    {
        Queue::fake();

        // 1. Wali Kelas berhak mempublikasikan (200/302 & Job Pushed)
        $this->actingAs($this->teacherA1)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertRedirect();
        Queue::assertPushed(SendReportPublishedWhatsAppNotificationJob::class);

        // 2. Operator & Kepala Sekolah berhak supervisi (200/302 & Job Pushed)
        Queue::fake();
        $this->actingAs($this->operatorA)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertRedirect();
        Queue::assertPushed(SendReportPublishedWhatsAppNotificationJob::class);

        Queue::fake();
        $this->actingAs($this->headmasterA)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertRedirect();
        Queue::assertPushed(SendReportPublishedWhatsAppNotificationJob::class);

        // 3. Guru Luar ditolak (403 Forbidden)
        Queue::fake();
        $this->actingAs($this->teacherA2)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertStatus(403);
        Queue::assertNotPushed(SendReportPublishedWhatsAppNotificationJob::class);

        // 4. Siswa dan Orang Tua ditolak (403 Forbidden)
        $this->actingAs($this->studentA1)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertStatus(403);

        $this->actingAs($this->parentA1)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertStatus(403);
        Queue::assertNotPushed(SendReportPublishedWhatsAppNotificationJob::class);

        // 5. Guru Tenant B ditolak lintas tenant (404 Not Found)
        $this->actingAs($this->teacherB1)
            ->post(route('homeroom.reports.publish', $this->classA1))
            ->assertStatus(404);
        Queue::assertNotPushed(SendReportPublishedWhatsAppNotificationJob::class);
    }
}
