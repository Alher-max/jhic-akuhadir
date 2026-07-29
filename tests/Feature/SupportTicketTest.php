<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SD Negeri 1 Support Test',
            'institution_type' => 'school',
            'slug' => 'sdn-1-support-test',
            'code' => 'SDSUPP1',
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);

        $schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SD',
            'tingkat' => 1,
            'nama_kelas' => '1-A',
            'wali_kelas_id' => $this->operator->id,
        ]);

        $this->student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $schoolClass->id,
            'role' => 'student',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    /**
     * Test 1: User (Siswa/Guru) dapat membuat tiket bantuan kendala baru.
     */
    public function test_user_can_create_support_ticket(): void
    {
        $response = $this->actingAs($this->student)
            ->post(route('support-tickets.store'), [
                'category' => 'Absensi',
                'subject' => 'Kendala Lokasi GPS Presensi PWA',
                'message' => 'Saya mengalami kendala radius GPS tidak terdeteksi saat masuk aplikasi.',
            ]);

        $response->assertRedirect(route('support-tickets.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('support_tickets', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->student->id,
            'category' => 'Absensi',
            'subject' => 'Kendala Lokasi GPS Presensi PWA',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 2: Angka antrean tiket pending bertambah di Dasbor Operator.
     */
    public function test_operator_dashboard_shows_pending_ticket_count(): void
    {
        // Buat 2 tiket pending dan 1 tiket resolved
        SupportTicket::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->student->id,
            'category' => 'Absensi',
            'subject' => 'Tiket Pending 1',
            'message' => 'Detail 1',
            'status' => 'pending',
        ]);

        SupportTicket::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->student->id,
            'category' => 'Akun',
            'subject' => 'Tiket Pending 2',
            'message' => 'Detail 2',
            'status' => 'pending',
        ]);

        SupportTicket::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->student->id,
            'category' => 'Sistem',
            'subject' => 'Tiket Selesai',
            'message' => 'Detail Selesai',
            'status' => 'resolved',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('operator.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('2 permintaan bantuan mengantre');
    }

    /**
     * Test 3: Operator dapat melihat detail dan memperbarui status serta tanggapan tiket.
     */
    public function test_operator_can_view_and_update_support_ticket(): void
    {
        $ticket = SupportTicket::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->student->id,
            'category' => 'Absensi',
            'subject' => 'Kendala GPS',
            'message' => 'Presensi gagal di sekolah.',
            'status' => 'pending',
        ]);

        // Access detail
        $responseShow = $this->actingAs($this->operator)
            ->get(route('operator.support-tickets.show', $ticket->id));

        $responseShow->assertStatus(200);
        $responseShow->assertSee('Kendala GPS');

        // Update status & response
        $responseUpdate = $this->actingAs($this->operator)
            ->put(route('operator.support-tickets.update', $ticket->id), [
                'status' => 'resolved',
                'operator_response' => 'Koordinat GPS sekolah telah diperbarui. Silakan coba presensi ulang.',
            ]);

        $responseUpdate->assertRedirect();
        $responseUpdate->assertSessionHas('success');

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
            'operator_response' => 'Koordinat GPS sekolah telah diperbarui. Silakan coba presensi ulang.',
        ]);
    }
}
