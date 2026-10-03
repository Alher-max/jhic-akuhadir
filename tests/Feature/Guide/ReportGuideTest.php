<?php

declare(strict_types=1);

namespace Tests\Feature\Guide;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportGuideTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Public Guest Accessibility.
     * Tamu (guest / tanpa login) dapat mengakses rute panduan rapor dengan status HTTP 200 OK.
     */
    public function test_public_guest_accessibility(): void
    {
        // Akses via nama rute panduan.rapor (/panduan/rapor)
        $response = $this->get(route('panduan.rapor'));
        $response->assertOk();
        $response->assertViewIs('guide.rapor');

        // Akses via alias guide.rapor (/guide/rapor)
        $responseAlias = $this->get(route('guide.rapor'));
        $responseAlias->assertOk();
        $responseAlias->assertViewIs('guide.rapor');
    }

    /**
     * Test 2: Content & Role Coverage.
     * Memastikan konten panduan memuat instruksi lengkap untuk seluruh 8 peran/kategori.
     */
    public function test_content_and_role_coverage(): void
    {
        $response = $this->get(route('panduan.rapor'));
        $response->assertOk();

        // 1. Header & Identitas Panduan
        $response->assertSee('Pusat Panduan');
        $response->assertSee('Modul Rapor Sekolah HadirYuk');

        // 2. Cakupan 8 Peran Utama
        $response->assertSee('Operator');
        $response->assertSee('Guru Mata Pelajaran');
        $response->assertSee('Wali Kelas');
        $response->assertSee('Fasilitator P5');
        $response->assertSee('Vokasi SMK');
        $response->assertSee('Panduan Siswa');
        $response->assertSee('Panduan Orang Tua');
        $response->assertSee('Verifikasi QR Code');

        // 3. Kata Kunci & Fitur Unggulan Khas HadirYuk
        $response->assertSee('Tujuan Pembelajaran');
        $response->assertSee('Auto-Pull');
        $response->assertSee('Leger Nilai');
        $response->assertSee('WhatsApp');
        $response->assertSee('Dapodik');
        $response->assertSee('Zero Server Load');
    }

    /**
     * Test 3: Landing Page Link Integration.
     * Memastikan tautan menuju /panduan/rapor terpasang pada landing page utama.
     */
    public function test_landing_page_link_integration(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        // Memastikan navbar / landing page memuat tautan menuju panduan rapor
        $response->assertSee(route('panduan.rapor'));
        $response->assertSee('Panduan Rapor');
    }

    /**
     * Test 4: Zero Authentication Barrier.
     * Memastikan tidak ada pengalihan (redirect) ke /login saat rute panduan diakses oleh siapa pun.
     */
    public function test_zero_authentication_barrier(): void
    {
        // 1. Pengujian untuk Guest (tanpa session / autentikasi)
        $this->assertGuest();
        $guestResponse = $this->get(route('panduan.rapor'));
        $guestResponse->assertOk();
        $this->assertFalse($guestResponse->isRedirect(route('login')));

        // 2. Pengujian untuk Pengguna Terautentikasi (Guru, Wali Kelas, Siswa)
        $tenant = Tenant::create([
            'name' => 'SMK Maju Bersama',
            'slug' => 'smk-maju-bersama',
            'code' => 'SMKMB',
            'institution_type' => 'school',
            'onboarding_completed' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $authResponse = $this->actingAs($user)->get(route('panduan.rapor'));
        $authResponse->assertOk();
    }
}
