<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    /**
     * Test landing page renders successfully with status 200.
     */
    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test hero section copywriting, line-break nowrap, and value badges.
     */
    public function test_hero_section_contains_report_card_ecosystem_copy(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Satu sistem untuk seluruh operasional sekolah: Presensi 5 metode, Modul Rapor Resmi (Kurikulum Merdeka, SMK &amp; Madrasah), integrasi ekspor Dapodik/e-Rapor, hingga notifikasi WhatsApp otomatis ke orang tua tanpa biaya server mahal.', false)
            ->assertSee('Harga Termurah')
            ->assertSee('<span class="inline-block whitespace-nowrap">se&#8209;Indonesia.</span>', false)
            ->assertSee('Investasi sistem sekolah paling efisien se-Indonesia.')
            ->assertSee('Presensi 5 Metode')
            ->assertSee('RFID, QR, Geofencing GPS, Face AI, &amp; Manual.', false)
            ->assertSee('Rapor Resmi Terintegrasi')
            ->assertSee('Auto-pull presensi, Smart Narasi TP, &amp; Cetak A4 Zero Server Load.', false);
    }

    /**
     * Test demo accounts section microcopy.
     */
    public function test_demo_accounts_microcopy_highlights_roles_and_report_features(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Coba Akun Demo')
            ->assertSee('Jelajahi pengalaman 6 peran berbeda. Masuk sebagai Guru untuk mencoba Smart Narasi Rapor, atau sebagai Wali Kelas untuk melihat Auto-Pull Presensi &amp; Cetak Rapor Resmi A4.', false);
    }

    /**
     * Test 6 structured feature cards in the feature grid and links to panduan rapor.
     */
    public function test_featured_grid_contains_all_six_structured_cards_and_guide_links(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            // Kartu 1: Presensi Fleksibel Multi-Mode
            ->assertSee('Presensi Fleksibel Multi-Mode')
            ->assertSee('RFID, QR, Geofencing, Face AI, Presensi Guru &amp; Staf.', false)
            // Kartu 2: Dasbor Terpadu 6 Peran
            ->assertSee('Dasbor Terpadu 6 Peran')
            ->assertSee('Operator, Kepala Sekolah, Guru, Wali Kelas, Siswa, Orang Tua.')
            // Kartu 3: Modul Rapor Kurikulum Merdeka & K13 (links to panduan)
            ->assertSee('Modul Rapor Kurikulum Merdeka &amp; K13', false)
            ->assertSee('Smart Auto-Narasi TP, Leger Nilai, Cetak A4 Zero Server Load, QR Code Verifikasi SHA-256.')
            ->assertSee('Panduan Lengkap Rapor')
            // Kartu 4: Kokurikuler P5/P5RA & Vokasi SMK (links to panduan)
            ->assertSee('Kokurikuler P5/P5RA &amp; Vokasi SMK', false)
            ->assertSee('Rubrik Projek P5 &amp; P5RA Kemenag, Penilaian PKL terintegrasi Geofence industri, &amp; Transkrip UKK.', false)
            ->assertSee('Panduan Projek P5 &amp; Kurikulum', false)
            // Kartu 5: Auto-Pull Presensi & Ekspor Siap Setor
            ->assertSee('Auto-Pull Presensi &amp; Ekspor Siap Setor', false)
            ->assertSee('Rekap Sakit/Izin/Alpa otomatis tanpa hitung manual, Ekspor 1-Klik e-Rapor SP, RDM Kemenag, &amp; Leger Dapodik.', false)
            // Kartu 6: Notifikasi WhatsApp & Hemat Sumber Daya
            ->assertSee('Notifikasi WhatsApp &amp; Hemat Sumber Daya', false)
            ->assertSee('Notifikasi penerbitan rapor otomatis ke orang tua &amp; arsitektur lean tanpa server mahal.', false);
    }

    /**
     * Test comparison table contains report module, value admin, and data integration rows.
     */
    public function test_comparison_table_contains_integrated_report_and_export_comparisons(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Mengapa Harus')
            ->assertSee('Modul Rapor')
            ->assertSee('Harus sewa aplikasi e-rapor terpisah &amp; rawan server down', false)
            ->assertSee('Terintegrasi penuh dengan absensi &amp; Zero Server Load', false)
            ->assertSee('Administrasi Nilai')
            ->assertSee('Ketik narasi manual &amp; hitung absensi manual', false)
            ->assertSee('Smart Narasi otomatis &amp; Auto-Pull presensi HadirYuk', false)
            ->assertSee('Integrasi Data')
            ->assertSee('Ketik ulang ke aplikasi dinas')
            ->assertSee('Ekspor 1-klik kompatibel e-Rapor SP, RDM, &amp; Dapodik', false)
            ->assertSee('✓')
            ->assertSee('✗');
    }

    /**
     * Test FAQ section contains answers for Kurikulum Merdeka/SMK/Madrasah, Dapodik/e-Rapor export, and Zero Server Load.
     */
    public function test_faq_section_contains_report_merdeka_and_zero_server_load(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Apakah HadirYuk mendukung Rapor Kurikulum Merdeka, SMK, dan Madrasah?')
            ->assertSee('Kurikulum Merdeka Fase A sampai F')
            ->assertSee('Projek P5 &amp; P5RA Kemenag', false)
            ->assertSee('Praktik Kerja Lapangan (PKL)')
            ->assertSee('Transkrip Uji Kompetensi Keahlian (UKK)')
            ->assertSee('Apakah operator perlu menginput ulang nilai ke e-Rapor atau Dapodik?')
            ->assertSee('e-Rapor SP Kemendikbudristek')
            ->assertSee('RDM (Rapor Digital Madrasah)')
            ->assertSee('Leger Dapodik')
            ->assertSee('Apakah server akan lambat saat seluruh guru mencetak rapor bersamaan?')
            ->assertSee('Zero Server Load Print Engine');
    }

    /**
     * Test official brand identity and competition badge persist.
     */
    public function test_brand_identity_and_competition_badges_persist(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Hadir')
            ->assertSee('Yuk')
            ->assertSee('JHIC 2.0')
            ->assertSee('JAGOAN HOSTING INNOVATION COMPETITION');
    }

    /**
     * Test there is no duplicate navbar and exactly one floating WhatsApp button with proper positioning.
     */
    public function test_no_duplicate_navbar_and_clean_single_floating_whatsapp(): void
    {
        $response = $this->get('/');
        $content = $response->getContent();

        // Exactly one <nav tag
        $this->assertSame(1, substr_count($content, '<nav '));

        // Exactly one floating WhatsApp button
        $this->assertSame(1, substr_count($content, 'https://wa.me/'));
        $this->assertStringContainsString('fixed bottom-6 right-6 z-50', $content);
    }
}
