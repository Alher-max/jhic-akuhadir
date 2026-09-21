<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperatorSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $operatorA;
    protected User $operatorB;
    protected User $studentA;
    protected User $teacherA;
    protected User $parentA;

    protected function setUp(): void
    {
        parent::setUp();

        // Tenant A
        $this->tenantA = Tenant::create([
            'name' => 'SMK Negeri 1 Solusi Digital',
            'institution_type' => 'school',
            'slug' => 'smkn-1-solusi-digital',
            'code' => 'SMK01SD',
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        $this->operatorA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'operator',
            'name' => 'Operator Sekolah A',
            'email' => 'operator.a@smkn1.sch.id',
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);

        $schoolClassA = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'jenjang' => 'SMK',
            'tingkat' => 10,
            'nama_kelas' => '10-RPL',
            'wali_kelas_id' => $this->operatorA->id,
        ]);

        $this->studentA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'class_id' => $schoolClassA->id,
            'role' => 'student',
            'name' => 'Ahmad Siswa',
            'email' => 'ahmad@smkn1.sch.id',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->teacherA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'teacher',
            'name' => 'Budi Guru',
            'email' => 'budi.guru@smkn1.sch.id',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->parentA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'name' => 'Citra Orang Tua',
            'email' => 'citra.parent@smkn1.sch.id',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        // Tenant B
        $this->tenantB = Tenant::create([
            'name' => 'SMA Swasta Sebelah',
            'institution_type' => 'school',
            'slug' => 'sma-swasta-sebelah',
            'code' => 'SMAS02',
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        $this->operatorB = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'operator',
            'name' => 'Operator Sekolah B',
            'email' => 'operator.b@smas.sch.id',
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);
    }

    /**
     * Test 1: Empty state pada halaman antrean tiket operator (200 OK & pesan ramah).
     */
    public function test_operator_can_view_support_tickets_empty_state(): void
    {
        $response = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Tiket Bantuan Kendala Operator');
        $response->assertSee('Belum ada tiket bantuan kendala yang mengantre.');
        $response->assertViewHas('pendingCount', 0);
        $response->assertViewHas('processingCount', 0);
        $response->assertViewHas('resolvedCount', 0);
    }

    /**
     * Test 2: Operator dapat melihat tiket dari berbagai role (Siswa, Guru, Orang Tua).
     */
    public function test_operator_can_view_tickets_from_various_roles(): void
    {
        SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA->id,
            'category' => 'Absensi',
            'subject' => 'Kendala GPS Siswa',
            'message' => 'Radius GPS tidak terbaca di ponsel siswa.',
            'status' => 'pending',
        ]);

        SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->teacherA->id,
            'category' => 'Jadwal',
            'subject' => 'Jadwal KBM Bentrok',
            'message' => 'Jam mengajar bentrok di kelas 10-RPL.',
            'status' => 'processing',
        ]);

        SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->parentA->id,
            'category' => 'Akun',
            'subject' => 'Lupa PIN Notifikasi Wali',
            'message' => 'Tidak bisa login akun wali murid.',
            'status' => 'resolved',
        ]);

        $response = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('Kendala GPS Siswa');
        $response->assertSee('Ahmad Siswa');
        $response->assertSee('Role: student');

        $response->assertSee('Jadwal KBM Bentrok');
        $response->assertSee('Budi Guru');
        $response->assertSee('Role: teacher');

        $response->assertSee('Lupa PIN Notifikasi Wali');
        $response->assertSee('Citra Orang Tua');
        $response->assertSee('Role: parent');

        $response->assertViewHas('pendingCount', 1);
        $response->assertViewHas('processingCount', 1);
        $response->assertViewHas('resolvedCount', 1);
    }

    /**
     * Test 3: Null-safety - Penanganan tiket jika relasi pengguna bernilai null (User Terhapus).
     */
    public function test_operator_support_tickets_handles_null_or_missing_user_safely(): void
    {
        $ticketWithNullUser = new SupportTicket([
            'tenant_id' => $this->tenantA->id,
            'user_id' => 999999,
            'category' => 'Sistem',
            'subject' => 'Tiket Pengguna Terhapus',
            'message' => 'Pesan dari akun yang sudah tidak ada.',
            'status' => 'pending',
        ]);
        $ticketWithNullUser->id = 999;
        $ticketWithNullUser->created_at = now();
        $ticketWithNullUser->setRelation('user', null);

        // Index page should render gracefully without error when user is null
        $viewIndex = $this->actingAs($this->operatorA)->withViewErrors([])->view('operator.support-tickets.index', [
            'tickets' => new \Illuminate\Pagination\LengthAwarePaginator([$ticketWithNullUser], 1, 15),
            'pendingCount' => 1,
            'processingCount' => 0,
            'resolvedCount' => 0,
        ]);

        $viewIndex->assertSee('Tiket Pengguna Terhapus');
        $viewIndex->assertSee('User Terhapus');

        // Show page should also render gracefully without error when user is null
        $viewShow = $this->actingAs($this->operatorA)->withViewErrors([])->view('operator.support-tickets.show', [
            'ticket' => $ticketWithNullUser,
        ]);

        $viewShow->assertSee('Tiket Pengguna Terhapus');
        $viewShow->assertSee('Pengguna');
    }

    /**
     * Test 4: Isolasi Multi-Tenancy - Operator Tenant A tidak dapat melihat atau mengedit tiket Tenant B.
     */
    public function test_cross_tenant_isolation_operator_cannot_access_other_tenant_tickets(): void
    {
        $ticketTenantB = SupportTicket::create([
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->operatorB->id,
            'category' => 'Keamanan',
            'subject' => 'Tiket Rahasia Tenant B',
            'message' => 'Data sensitif Tenant B.',
            'status' => 'pending',
        ]);

        $ticketTenantA = SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA->id,
            'category' => 'Umum',
            'subject' => 'Tiket Terbuka Tenant A',
            'message' => 'Data Tenant A.',
            'status' => 'pending',
        ]);

        // Operator A visits index: sees Tenant A ticket, does NOT see Tenant B ticket
        $responseIndex = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.index'));

        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Tiket Terbuka Tenant A');
        $responseIndex->assertDontSee('Tiket Rahasia Tenant B');

        // Operator A tries to view Tenant B's ticket detail: 404 Not Found
        $responseShow = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.show', $ticketTenantB->id));

        $responseShow->assertStatus(404);

        // Operator A tries to update Tenant B's ticket: 404 Not Found
        $responseUpdate = $this->actingAs($this->operatorA)
            ->put(route('operator.support-tickets.update', $ticketTenantB->id), [
                'status' => 'resolved',
                'operator_response' => 'Unauthorized update attempt',
            ]);

        $responseUpdate->assertStatus(404);

        // Verify ticket in Tenant B was not modified
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticketTenantB->id,
            'status' => 'pending',
            'operator_response' => null,
        ]);
    }

    /**
     * Test 5: Filter dan pencarian tiket di halaman Operator.
     */
    public function test_operator_can_filter_and_search_tickets(): void
    {
        SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA->id,
            'category' => 'Absensi',
            'subject' => 'GPS Bermasalah Saat Hujan',
            'message' => 'Ponsel tidak dapat lock sinyal GPS.',
            'status' => 'pending',
        ]);

        SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->teacherA->id,
            'category' => 'Jadwal',
            'subject' => 'Perubahan Jadwal Pelajaran Fisika',
            'message' => 'Minta geser jadwal ke hari Selasa.',
            'status' => 'resolved',
        ]);

        // Filter status: pending
        $responsePending = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.index', ['status' => 'pending']));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('GPS Bermasalah Saat Hujan');
        $responsePending->assertDontSee('Perubahan Jadwal Pelajaran Fisika');

        // Filter status: resolved
        $responseResolved = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.index', ['status' => 'resolved']));
        $responseResolved->assertStatus(200);
        $responseResolved->assertSee('Perubahan Jadwal Pelajaran Fisika');
        $responseResolved->assertDontSee('GPS Bermasalah Saat Hujan');

        // Search by subject
        $responseSearch = $this->actingAs($this->operatorA)
            ->get(route('operator.support-tickets.index', ['search' => 'Fisika']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Perubahan Jadwal Pelajaran Fisika');
        $responseSearch->assertDontSee('GPS Bermasalah Saat Hujan');
    }

    /**
     * Test 6: Operator dapat memperbarui status tiket dan mencatat balasan/solusi.
     */
    public function test_operator_can_update_status_and_response(): void
    {
        $ticket = SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->studentA->id,
            'category' => 'Absensi',
            'subject' => 'Kamera PWA Tidak Terbuka',
            'message' => 'Izin kamera tertutup di browser Chrome mobile.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->operatorA)
            ->put(route('operator.support-tickets.update', $ticket->id), [
                'status' => 'resolved',
                'operator_response' => 'Silakan bersihkan izin situs pada browser Chrome: Pengaturan Situs -> Kamera -> Izinkan.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
            'operator_response' => 'Silakan bersihkan izin situs pada browser Chrome: Pengaturan Situs -> Kamera -> Izinkan.',
        ]);
    }
}
