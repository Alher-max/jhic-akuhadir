# HadirYuk! Architecture & Technical Specification

## 1. System Overview
HadirYuk! adalah platform SaaS Presensi Multi-Tenant monolitik yang mendukung 4 segmen industri utama melalui pendekatan Monolithic Multi-Variant dinamis:
- **HadirSekolah**: Sekolah & madrasah (Siswa, Guru, Mapel, KBM, NPSN).
- **HadirElco**: Lembaga kursus & bimbel (Peserta, Tutor, Modul, Sesi, Sertifikat Digital).
- **HadirUMKM**: Usaha retail & operasional (Karyawan, Shift, Geofencing outlet).
- **HadirCorporate**: Perusahaan enterprise (Karyawan, Manager, Departemen, Proyek).

## 2. Tech Stack
- **Backend Framework**: Laravel 11/13.x (PHP 8.3+, tested and targeted for PHP 8.4)
- **Database**: MySQL / MariaDB (Multi-tenant via `tenant_id` column-level isolation & subdomain resolution)
- **Frontend Engine**: Blade Templates, Tailwind CSS, Alpine.js, Vue 3 via Vite
- **Web Push**: `minishlink/web-push` dengan VAPID key
- **Authentication**: Custom Multi-Auth / Breeze RBAC (`headmaster/owner`, `operator/admin`, `teacher`, `student`, `parent`)
- **Hosting & Server Infrastructure**: Debian 12 (Bookworm) pada VPS Jagoan Hosting Paket Nebula (2 vCPU Cores, 2GB RAM, 2GB Swap, 40GB NVMe/SSD)
- **Security & Ingress**: Cloudflare Zero Trust, Cloudflare Tunnel (`cloudflared` via QUIC), SSL/TLS Full Strict
- **CI/CD Pipeline**: GitHub Actions (`.github/workflows/deploy.yml`) dengan remote automated SSH deployment

## 3. Dynamic Multi-Variant Subsystem
Sistem menghindari replikasi file views dengan pendekatan konfigurasi terpusat:
- **Configuration**: `config/variants.php` memetakan metadata tiap segmen (nama aplikasi, skema warna HEX, badge CSS, terminologi peran & navigasi).
- **Helper**: `app/Services/VariantHelper.php` mendeteksi segmen aktif via session, subdomain, atau field `institution_type` tenant.
- **View Composer**: `app/Providers/AppServiceProvider.php` menyuntikkan `$currentVariant`, `$isElco`, `$isSekolah`, dsb., serta menginjeksikan CSS variables `:root { --brand-primary: ...; }` ke layout utama (`app.blade.php`, `guest.blade.php`).

## 4. Key Domain Modules
1. **Attendance Engine**:
   - Geofencing (radius lat/long & accuracy checking).
   - WiFi/IP Whitelist validation.
   - KBM/Session-based schedules.
   - Fleksibilitas 7 hari: presensi datang, pulang, dan sesi KBM dapat berjalan pada hari Minggu jika Minggu
     diaktifkan pada `working_days`/jam operasional tenant atau memiliki jadwal KBM maupun aktivitas resmi.
   - Selfie + Master Photo face verification.
   - Biometric Hardware Push Webhook (`/api/v1/biometric/push`) dengan auto-discovery Serial Number.
2. **Digital Certificate (Elco Variant)**:
   - Modul penerbitan sertifikat dengan penomoran unik (`ELCO/YYYY/MM/XXXX`).
   - Token-based verification route (`/verify-certificate/{token}`) untuk scan QR code publik.
3. **Role & Tenant Management**:
   - Tenant isolation via global scope & middleware.
   - Onboarding wizard, forced password change (`must_change_password`), dan verifikasi OTP.
4. **Support Ticket & Helpdesk Subsystem**:
   - Menangani pelaporan kendala/bantuan dari berbagai role pengguna (`student`, `teacher`, `parent`) ke Operator Sekolah (`operator`).
   - Alur status tiket: `pending` (mengantre) -> `processing` (sedang ditangani) -> `resolved` (selesai) dengan catatan/solusi operator (`operator_response`).
   - Isolasi ketat multi-tenant (`tenant_id`) pada controller & model untuk mencegah cross-tenant data leakage.
   - Menggunakan query ANSI SQL standar (`CASE ... WHEN ... THEN ... ELSE ... END` untuk pengurutan prioritas status) guna memastikan kompatibilitas penuh antar database engine (MySQL, PostgreSQL, SQLite) tanpa fungsi spesifik vendor seperti `FIELD()`.
   - Null-safety pada view Blade (`?->`) untuk menangani pengguna yang terhapus atau profil tidak lengkap secara graceful tanpa memicu HTTP 500.
5. **Modul Master Data Rapor & Akademik (Sprint 1)**:
   - **Tabel `academic_years`**: Mengelola tahun ajaran (contoh: "2026/2027") dan semester ('1' untuk Ganjil, '2' untuk Genap), rentang tanggal resmi (`start_date`, `end_date`), dan status aktif (`is_active`).
   - **Single Active Year Invariant**: Menjamin bahwa dalam 1 tenant hanya ada 1 tahun ajaran aktif melalui event Eloquent `saving` dan `DB::transaction`. Mengaktifkan tahun ajaran baru secara otomatis menonaktifkan tahun ajaran aktif lainnya pada tenant bersangkutan tanpa mempengaruhi tenant lain.
   - **Pengayaan Rombel (`school_classes`)**: Menambahkan kolom `fase` (A-F untuk Kurikulum Merdeka) dan `curriculum_type` (`merdeka`, `k13`, `kemenag_merdeka`) dengan konsistensi trait `App\Traits\BelongsToTenant`.
   - **ANSI SQL Compliance**: Dilarang menggunakan `$table->enum()`, seluruh kolom status/semester menggunakan `$table->string()` dengan komposit index `['tenant_id', 'is_active']` dan `['tenant_id', 'name', 'semester']`.
   - **Otorisasi**: Dibatasi ketat hanya untuk peran `operator` dan `headmaster` (peran `student` dan `parent` ditolak dengan status 403 Forbidden).
6. **Modul Tujuan Pembelajaran (TP) & Buku Nilai Guru Mapel (Sprint 2)**:
   - **Tabel `learning_objectives`**: Menyimpan deskripsi Tujuan Pembelajaran (TP) terstruktur berbasis Kurikulum Merdeka yang terikat pada `tenant_id`, `academic_year_id`, `subject_id`, dan opsional `class_id`. Menggunakan indeks gabungan `['tenant_id', 'academic_year_id', 'subject_id']`.
   - **Tabel `subject_grades`**: Menyimpan nilai akhir mata pelajaran skala 0.00 - 100.00 (`score`), narasi capaian tertinggi (`highest_achievement`), dan narasi capaian yang perlu bimbingan (`lowest_achievement`) untuk setiap kombinasi siswa, kelas, mata pelajaran, dan tahun ajaran aktif. Dilengkapi unique composite constraint `['tenant_id', 'academic_year_id', 'class_id', 'subject_id', 'student_id']` (`uniq_sub_grade_entry`).
   - **Teacher Assignment Gate / Policy**: Validasi otorisasi ketat di mana guru hanya dapat menginput/mengubah nilai dan TP pada pasangan kelas dan mapel yang ditugaskan kepadanya di tabel `class_schedules` (HTTP 403 Forbidden bagi guru di luar jadwal atau role `student`/`parent`).
   - **Smart Narration Generator**: Penjanaan teks narasi otomatis berbasis deskripsi TP ("Menunjukkan penguasaan yang sangat baik dalam ..." dan "Perlu bimbingan dan peningkatan dalam ...") dengan integrasi reaktif Alpine.js di UI dan fleksibilitas kustomisasi teks bebas oleh guru.
   - **Bulk Upsert & Invariant**: Operasi penyimpanan nilai didukung oleh `updateOrCreate` di dalam `DB::transaction` untuk menjamin atomisitas, idempotensi, dan mencegah duplikasi data nilai per siswa.
7. **Modul Dasbor Wali Kelas, Kompilasi Leger, Auto-Pull Presensi HadirYuk, & Ekstrakurikuler (Sprint 3)**:
   - **Tabel `student_reports`**: Lembar Rapor Siswa per semester yang mencakup `tenant_id`, `academic_year_id`, `class_id`, `student_id`, `wali_kelas_id`, rekap ketidakhadiran (`sick_count`, `permission_count`, `alpha_count`), catatan motivasi dan karakter (`homeroom_notes`), status kenaikan/kelulusan (`promotion_status`), serta status verifikasi (`status`: 'draft', 'submitted', 'locked'). Dilengkapi unique composite constraint `['tenant_id', 'academic_year_id', 'class_id', 'student_id']` (`uniq_student_report_entry`).
   - **Tabel `extracurricular_grades`**: Menyimpan nilai capaian ekstrakurikuler terstruktur (`tenant_id`, `student_report_id`, `activity_name`, `predicate`, `description`) dengan relasi `belongsTo` ke lembar rapor siswa dan indeks gabungan `['tenant_id', 'student_report_id']`.
   - **Auto-Pull Engine Presensi HadirYuk**: Mesin agregasi presensi otomatis dari log tabel `attendances` memanfaatkan query ANSI SQL murni (`SUM(CASE WHEN status = ... THEN 1 ELSE 0 END)`) yang terisolasi per tenant, difilter khusus pada presensi sekolah (`attendance_type = 'school'`), dan dibatasi strictly pada rentang tanggal semester aktif (`start_date` s.d. `end_date`).
   - **Kompilasi Matriks Leger (`HomeroomReportService`)**: Mengompilasi nilai seluruh siswa terhadap seluruh mata pelajaran aktif di kelas dari `subject_grades`, menghitung rata-rata siswa, rata-rata mata pelajaran rombel, rata-rata keseluruhan rombel, dan rekapitulasi kehadiran secara realtime dan performan.
   - **Homeroom Authorization Gate**: Proteksi otorisasi berbasis rombel bimbingan (`wali_kelas_id == auth()->id()`) dengan hak supervisi tenant penuh untuk peran `operator` dan `headmaster`. Guru yang bukan wali kelas dan pengguna non-staf (`student`, `parent`) ditolak dengan HTTP 403 Forbidden.
   - **Isolasi Multi-Tenant**: Seluruh model menggunakan `App\Traits\BelongsToTenant` dengan proteksi query `tenant_id` konsisten untuk menjamin nol kebocoran data antar tenant (zero cross-tenant data leakage).
8. **Modul Cetak Rapor Standar A4, Verifikasi QR Code, Publikasi, & Akses Siswa/Ortu (Sprint 4)**:
   - **Pola Cetak Zero Server Load (Browser `window.print()` + CSS `@media print`)**: Menggunakan layout HTML/CSS A4 portrait kaku (`@page { size: A4 portrait; margin: 1.2cm; }`) dengan pemisah halaman cetak massal satu rombel via `.page-break { page-break-after: always; break-after: page; }`. Didesain tanpa dependensi server-side rendering berat (DomPDF, Puppeteer, Chromium), menjaga beban CPU dan RAM server tetap 0 MB.
   - **QR Code & Public Verification Subsystem**: Kolom `verification_hash` (string 64 unik SHA-256) dan `published_at` pada tabel `student_reports`. Endpoint publik `GET /verify-report/{hash}` (`report.verify`) bebas autentikasi untuk memverifikasi keaslian dan integritas dokumen secara instan saat QR discan via kamera smartphone. Menampilkan status *"DOKUMEN RESMI TERVERIFIKASI"*, data identitas siswa, sekolah, kelas, semester, tanggal publikasi, serta ringkasan capaian belajar dan presensi HadirYuk.
   - **Publication Guard (Portal Siswa & Orang Tua)**: Rute portal siswa (`GET /student/report-card`) dan orang tua (`GET /parent/report-card`) dilindungi publication guard: hanya dapat melihat rincian nilai dan mencetak lembar rapor jika status rapor telah `'published'` atau `'locked'`. Saat berstatus `'draft'`, sistem menyajikan pesan informatif penahanan rilis rapor dan akses rute cetak langsung diblokir dengan HTTP 403 Forbidden.
   - **Workflow Publikasi & Kunci Rapor Rombel**: Wali Kelas, Operator, dan Kepala Sekolah dapat mempublikasikan seluruh rapor rombel dalam 1 transaksi atomik (`POST /homeroom/reports/{class}/publish`), otomatis memperbarui status menjadi `'published'`, mencatat timestamp `published_at`, dan menghasilkan `verification_hash` unik. Fitur kunci rapor (`POST /homeroom/reports/{class}/lock`) mengamankan integritas nilai dari modifikasi guru mapel.
   - **Mobile Capacitor JS WebView Ready**: Tampilan portal rapor siswa dan orang tua dirancang responsif Tailwind CSS dan terintegrasi mulus dengan aplikasi Android HadirYuk Siswa (`com.thortech.hadiryuk.student`) dan HadirYuk Orang Tua (`shop.thortech.hadiryuk.parent`).
9. **Modul Ekspor Data Kompatibilitas e-Rapor SP, Dapodik, & RDM Kemenag (Sprint 5)**:
   - **Format 1: Format Impor Nilai e-Rapor SP Kemendikbudristek (Per Rombel & Mapel)**:
     - Format Header: `No;NISN;NIS;Nama Siswa;Nilai Akhir;Capaian Tertinggi;Capaian Terendah`
     - Mengalirkan nilai akhir (`score` terformat) beserta narasi capaian tertinggi dan terendah dari tabel `subject_grades` yang difilter berdasarkan tahun ajaran aktif, kelas, dan mata pelajaran.
   - **Format 2: Format Impor Nilai RDM Kemenag (Rapor Digital Madrasah)**:
     - Format Header: `NO;NISN;NAMA SISWA;NILAI_PENGETAHUAN;DESKRIPSI_CAPAIAN`
     - Menggabungkan narasi capaian belajar (*"Tercapai optimal: [capaian tertinggi]; Perlu peningkatan: [capaian terendah]"*) secara terstandarisasi untuk sinkronisasi pangkalan data madrasah Kementerian Agama.
   - **Format 3: Leger Lengkap Rombel & Rekapitulasi Dapodik**:
     - Format Header Dinamis: `No;NISN;NIS;Nama Siswa;Jenis Kelamin;[Daftar Seluruh Mapel];Rata-rata;Sakit;Izin;Alpa;Catatan Wali Kelas`
     - Menghimpun matriks nilai seluruh mata pelajaran, rata-rata capaian belajar siswa, rekapitulasi presensi semester dari HadirYuk (`sick_count`, `permission_count`, `alpha_count`), konversi gender Dapodik (`L`/`P`), dan catatan motivasi wali kelas.
   - **Pola Arsitektur Streaming Zero Memory Bloat ($O(1)$ RAM)**:
     - Memanfaatkan **Native PHP Output Stream** (`response()->stream()` dengan pointer `fopen('php://output', 'w')` dan `fputcsv()`) melalui `ReportExportService`.
     - Menghindari penggunaan dependensi spreadsheet berat (`phpspreadsheet` / `maatwebsite/excel`), menjamin konsumsi RAM server tetap konstan pada VPS 2GB RAM sekalipun mengekspor data ribuan siswa.
   - **Kompatibilitas Microsoft Excel & Standar Regional Indonesia**:
     - Menginjeksi **UTF-8 Byte Order Mark (BOM)** (`\xEF\xBB\xBF`) di awal stream dan menggunakan delimiter titik koma (`;`) sesuai konvensi CSV Microsoft Excel region Indonesia, mencegah karakter teracak dan memastikan kolom langsung terpisah sempurna tanpa perlu Text-to-Columns wizard.
   - **Otorisasi Berjenjang & Isolasi Multi-Tenant Ketat**:
     - Guru mata pelajaran dibatasi hanya dapat mengunduh nilai e-Rapor dan RDM untuk rombel dan mapel yang ditugaskan pada `class_schedules`.
     - Wali kelas memiliki hak unduh leger kompilasi kelas bimbingannya.
     - Operator, Admin Dapodik, dan Kepala Sekolah memegang hak supervisi untuk mengekspor seluruh rombel di tenant bersangkutan.
     - Pengguna peran `student` dan `parent` diblokir total dengan HTTP 403 Forbidden.
     - Percobaan unduh data lintas tenant (`cross-tenant`) ditolak langsung dengan status HTTP 404/403.
     - Validasi guard tahun ajaran aktif memastikan status HTTP 422 Unprocessable Entity disajikan jika tidak ada kalender akademik aktif.
10. **Modul Kokurikuler P5 & P5RA (Madrasah) (Sprint 6)**:
    - **Skema Database & Relasi Eloquent**:
      - Tabel `p5_projects`: Mengelola data projek profil rombel (`theme`, `title`, `description`, `coordinator_id`, `academic_year_id`, `class_id`) dengan indeks `['tenant_id', 'academic_year_id', 'class_id']`.
      - Tabel `p5_project_targets`: Menyimpan target capaian berjenjang (`target_type`: 'pancasila' / 'rahmatan_lil_alamin', `dimension`, `element`, `sub_element`, `target_description`).
      - Tabel `p5_assessments`: Menyimpan predikat capaian kualitatif per sub-elemen siswa (`MB`, `SB`, `BSH`, `SAB`) dengan unique constraint ANSI `uniq_p5_assess_target_student`.
      - Tabel `p5_student_notes`: Menyimpan catatan perkembangan proses siswa dari fasilitator dengan unique constraint ANSI `uniq_p5_student_notes`.
    - **Preset Dimensi & Nilai P5 / P5RA (`P5PresetService`)**:
      - Menyediakan bank data tema resmi, 6 Dimensi Profil Pelajar Pancasila Kemendikbudristek (*Beriman & Bertakwa, Berkebhinekaan Global, Gotong Royong, Mandiri, Bernalar Kritis, Kreatif*), serta 10 Nilai Rahmatan Lil 'Alamin Kemenag (*Ta’addub, Qudwah, Muwatanah, Tawassut, Tawazun, I’tidal, Musawah, Syura, Tasamuh, Tatawwur wa Ibtikar*).
      - Dilengkapi *Quick Picker Modal* reaktif Alpine.js pada UI pembuatan projek.
    - **Matriks Penilaian Interaktif & Bulk Upsert Idempoten**:
      - Antarmuka kisi-kisi siswa × target sub-elemen dengan selector radio pill interaktif (`MB`, `SB`, `BSH`, `SAB`) serta input catatan proses fasilitator.
      - Penyimpanan massal dalam `DB::transaction` menggunakan operasi atomik `updateOrCreate` guna mencegah redundansi data penilaian.
    - **Pola Cetak Rapor Projek Standar A4 (Zero Server Load)**:
      - Menggunakan layout HTML/CSS A4 portrait presisi (`@page { size: A4 portrait; margin: 1.2cm; }`) dengan pemisah halaman massal `.page-break`.
      - Memuat Kop Satuan Pendidikan, identitas siswa, ringkasan projek, tabel rubrik capaian berpenanda centang (✓) otomatis, catatan proses fasilitator, dan tanda tangan resmi tiga pihak (Orang Tua, Fasilitator/Wali Kelas, Kepala Sekolah).
    - **Otorisasi Berjenjang & Publication Guard**:
      - Fasilitator/Koordinator projek (`coordinator_id`), Wali Kelas rombel, Operator, dan Kepala Sekolah memegang hak akses kelola projek dan asesmen.
      - Guru di luar penugasan fasilitator/wali kelas ditolak dengan HTTP 403 Forbidden.
      - Siswa dan Orang Tua dapat mengakses/mencetak lembar Rapor P5 mandiri jika rapor kelas telah berstatus publikasi (`published` atau `locked`). Akses sebelum publikasi ditahan dengan HTTP 403 Forbidden.
      - Isolasi multi-tenant terjamin via trait `BelongsToTenant` dan penolakan cross-tenant (HTTP 404).
11. **Modul Vokasi SMK (Penilaian PKL, Presensi Geofence Industri, & UKK) (Sprint 7)**:
    - **Skema Database & Relasi Eloquent**:
      - Tabel `internship_placements`: Data penempatan magang/PKL siswa di Dunia Usaha & Dunia Industri (DUDI), mencakup `tenant_id`, `academic_year_id`, `class_id`, `student_id`, `teacher_supervisor_id`, `industry_location_id` (nullable FK ke `locations.id`), `company_name`, `company_address`, `mentor_name`, `mentor_position`, `start_date`, dan `end_date`. Dilengkapi composite unique constraint `uniq_internship_placement` `['tenant_id', 'academic_year_id', 'student_id']`.
      - Tabel `internship_assessments`: Evaluasi kinerja magang terstruktur, mencakup `tenant_id`, `internship_placement_id`, `technical_score`, `softskill_score`, `attendance_score`, `final_score`, `predicate` ('Sangat Baik', 'Baik', 'Cukup'), `technical_notes`, dan `softskill_notes`. Dilengkapi composite unique constraint `uniq_internship_assess` `['tenant_id', 'internship_placement_id']`.
      - Tabel `vocational_competency_assessments`: Penilaian Uji Kompetensi Keahlian (UKK) / LSP, mencakup `tenant_id`, `academic_year_id`, `class_id`, `student_id`, `scheme_name`, `assessor_name`, `institution_name`, `theory_score`, `practice_score`, `final_score`, `predicate` ('Sangat Kompeten', 'Kompeten', 'Belum Kompeten'), dan `certificate_number`. Dilengkapi composite unique constraint `uniq_vocational_ukk` `['tenant_id', 'academic_year_id', 'student_id']`.
      - Kolom `location_id` pada tabel `attendances`: Menghubungkan log presensi HadirYuk dengan lokasi GPS/geofence kantor mitra industri.
    - **Integrasi Presensi HadirYuk & Auto-Pull Real-Time (`VocationalReportService`)**:
      - Menghitung rasio kehadiran siswa (`calculatePlacementAttendanceScore`) dalam rentang tanggal PKL (`start_date` s.d. `end_date`).
      - Filter presensi otomatis jika penempatan ditautkan ke lokasi industri (`industry_location_id`), sehingga log presensi di lokasi lain tidak mendistorsi nilai kehadiran PKL.
      - Log status `present` dan `late` dihitung sebagai kehadiran resmi industri.
    - **Formula Pembobotan & Konversi Predikat**:
      - PKL: Bobot resmi 50% Teknis + 30% Budaya Kerja (Softskill) + 20% Kehadiran Industri. Predikat kualitatif: $\ge 85$: 'Sangat Baik', $75 - 84.99$: 'Baik', $< 75$: 'Cukup'.
      - UKK: Pembobotan 30% Teori + 70% Praktik jika ada ujian teori, atau 100% Praktik jika tanpa ujian tertulis. Predikat kelulusan: $\ge 85$: 'Sangat Kompeten', $\ge 70$: 'Kompeten', $< 70$: 'Belum Kompeten'.
    - **Pola Cetak Zero Server Load (A4 Standar Dokumen Resmi)**:
      - Cetak Sertifikat/Laporan Nilai PKL (`print-single`) memuat Kop Sekolah, identitas siswa, nama industri mitra, periode PKL, tabel komponen nilai terbobot, catatan budaya kerja/teknis, dan tanda tangan resmi 3 pihak (Pembimbing Industri/DUDI, Guru Pembimbing Sekolah, Kepala Sekolah).
      - Cetak Transkrip UKK (`print-single`) memuat Skema Sertifikasi, Lembaga Penguji (LSP/DUDI), Asesor Eksternal, rincian teori & praktik, nomor sertifikat BNSP/LSP, predikat kelulusan, dan tanda tangan 3 pihak (Asesor Eksternal, Penguji Internal/Wali Kelas, Kepala Sekolah).
      - Menjaga prinsip Zero Server Load via layout HTML/CSS `@media print` A4 murni tanpa dependensi rendering berat.
    - **Otorisasi Berjenjang & Publication Guard**:
      - Guru Pembimbing PKL dan Wali Kelas memegang hak akses input dan evaluasi nilai siswa bimbingannya.
      - Operator dan Kepala Sekolah memegang hak supervisi penuh seluruh rombel SMK.
      - Siswa dan Orang Tua dapat mengakses/mencetak dokumen PKL & UKK secara mandiri hanya setelah rapor semester aktif berstatus `published` atau `locked`. Akses sebelum publikasi atau lintas siswa diblokir dengan HTTP 403 Forbidden.
      - Isolasi multi-tenant terjamin via trait `BelongsToTenant` dan penolakan cross-tenant (HTTP 404).
12. **Modul Notifikasi WhatsApp Otomatis Penerbitan Rapor ke Orang Tua & Siswa (Sprint 8)**:
    - **Infrastruktur Layanan Notifikasi (`WhatsAppNotificationService`)**:
      - Terintegrasi secara terpusat dengan API Gateway WhatsApp HadirYuk (`services.wa` / `services.whatsapp` seperti Fonnte atau custom HTTP gateway).
      - Sanitasi nomor telepon standar internasional: mengonversi format lokal (`08...`, `8...`, `+62 ...`) secara otomatis menjadi format standar Indonesia (`628...`).
      - Penyusunan template pesan resmi yang memuat identitas satuan pendidikan (Tenant), nama siswa, NISN, rombel kelas, semester, tahun ajaran aktif, ringkasan rata-rata nilai akademik, rekapitulasi kehadiran HadirYuk (Sakit, Izin, Alpa), serta tautan langsung verifikasi dan cetak rapor digital (`verification_url`).
    - **Asynchronous Queue Job Processing (`SendReportPublishedWhatsAppNotificationJob`)**:
      - Mengimplementasikan antrean Laravel (`ShouldQueue`, `Queueable`, `SerializesModels`) agar publikasi rapor kelas tidak memblokir respon HTTP pengguna (*zero request latency*).
      - Mengumpulkan nomor telepon tujuan secara multi-kanal: `parent_phone` siswa, relasi `parents` (pivot `parent_student`), relasi `parent` tunggal (`parent_id`), dan kontak profil siswa jika terdaftar.
      - **Graceful Degradation & Fault Tolerance**: Setiap pengiriman pesan terisolasi dalam blok `try-catch` terpisah. Kegagalan gateway eksternal (misal: timeout, quota habis, 500 error) dicatat melalui `Log::warning(...)` tanpa menggagalkan pengiriman antrean lain atau melempar *unhandled exception*.
      - **Integritas Status Basis Data**: Kegagalan pengiriman WhatsApp tidak membatalkan status publikasi rapor (`published`) maupun merusak hash verifikasi yang telah terbentuk.
    - **Integrasi Alur Kerja Publikasi Rapor Kelas (`HomeroomReportController`)**:
      - Pada aksi publikasi rapor kelas (`POST /homeroom/reports/{class}/publish`), sistem secara atomik mempublikasikan seluruh lembar rapor siswa dalam transaksi database, kemudian otomatis men-dispatch job notifikasi WhatsApp untuk setiap siswa.
      - Dilengkapi opsi parameter `notify_whatsapp` (default: `true`) dan notifikasi flash session informatif.
13. **Halaman Pusat Panduan & Tutorial Modul Rapor Interaktif (Akses Publik / Guest-Friendly)**:
    - **Aksesibilitas Terbuka (Zero Auth Barrier)**:
      - Rute publik `/panduan/rapor` (nama rute: `panduan.rapor`, alias: `guide.rapor`) dikelola oleh `PublicGuideController::rapor` tanpa proteksi autentikasi, memberikan transparansi penuh bagi dewan juri kompetisi, pengunjung umum, dan calon pengguna sekolah.
      - Terintegrasi langsung pada bilah navigasi utama (desktop & mobile) serta footer landing page HadirYuk (`welcome.blade.php`).
    - **Arsitektur Antarmuka & Reaktivitas Alpine.js**:
      - Menggunakan *Alpine.js Role Switcher* interaktif untuk 8 peran pengguna (Operator & Kepala Sekolah, Guru Mapel, Wali Kelas, Fasilitator P5/P5RA, Pembimbing Vokasi SMK, Siswa, Orang Tua, dan Verifikasi Dokumen Publik).
      - Dilengkapi *Instant Filter Search Bar* reaktif (`x-model="searchQuery"`) untuk menyaring topik panduan dan fitur unggulan secara *real-time*.
      - Menyajikan kartu tanggung jawab peran, langkah-langkah praktis bernomor, *callout* keunggulan otomasi HadirYuk, serta tips praktis operasional.
    - **Print-Friendly Styling**:
      - Diformat dengan CSS `@media print` yang mengoptimalkan pencetakan buku panduan ke kertas fisik atau arsip PDF bersih bebas gangguan (*zero-distraction*).

## 5. Deployment & Gateway Architecture
- **Server VPS Host**: Debian 12 (Bookworm) pada VPS Jagoan Hosting Paket Nebula (Spesifikasi: 2 Core vCPU, 2GB RAM, 2GB Swap, 40GB Storage) di path `/var/www/thortech/hadiryuk`.
- **Trafik Ingress & Security**:
  `Client -> Cloudflare Edge & Zero Trust (SSL/WAF/Access Policies) -> Cloudflare Tunnel (QUIC) -> 127.0.0.1:8005 (hadiryuk.service) -> Laravel Framework`.
- **Cloudflare Tunnel & Zero Trust**:
  - Tunnel ID: `bbd420f0-9cdc-4fb9-a174-30b8bdebb051`
  - Ingress routing: `hadiryuk.thortech.shop` dan wildcard `*.hadiryuk.thortech.shop` langsung diarahkan ke `http://127.0.0.1:8005`.
  - Terintegrasi dengan Cloudflare Zero Trust untuk perimeter keamanan, proteksi endpoint sensitif, dan enkripsi end-to-end tanpa port publik terbuka (No Public Open Ports).
- **Reverse Proxy Status**:
  - Virtual host Nginx untuk HadirYuk telah **dinonaktifkan sepenuhnya**. Nginx tidak lagi menjadi reverse proxy HadirYuk.
- **Service Management**:
  - Dikelola via systemd service `hadiryuk.service` pada host port `8005` (lihat `deploy/systemd/hadiryuk.service.example`).
- **Trusted Proxies**:
  - Laravel mengonfigurasi `$middleware->trustProxies(at: '*')` di `bootstrap/app.php` untuk mempercayai header reverse proxy dari Cloudflare Tunnel (`X-Forwarded-Proto`, `CF-Connecting-IP`, dsb.).

## 6. CI/CD Pipeline Architecture
- **Alur Deployment Otomatis**:
  `Push to main -> GitHub Actions (Lint, Test, Build) -> SSH Deploy to VPS -> Restart Service`.
- **Workflow File**: `.github/workflows/deploy.yml`
- **Tahapan Pipeline**:
  1. **Continuous Integration (`test` job)**:
     - Environment PHP 8.3 & Node 20 pada `ubuntu-latest`; kompatibilitas PHP 8.4 telah divalidasi dan menjadi target runtime berikutnya.
     - Caching dependensi Composer & NPM.
     - Eksekusi build aset frontend (`npm ci && npm run build`).
     - Eksekusi test suite otomatis (`php artisan test` dengan SQLite in-memory).
  2. **Continuous Deployment (`deploy` job)**:
     - Berjalan setelah job `test` sukses pada branch `main`.
     - Menggunakan action SSH `appleboy/ssh-action@v1.0.3` dengan credentials `SSH_HOST`, `SSH_USER`, `SSH_KEY`, `SSH_PORT`.
     - Menjalankan sinkronisasi kode (`git pull origin main`), pembaruan dependensi, migrasi database (`migrate --force`), optimasi cache Laravel, dan restart service `hadiryuk.service`.
- **Panduan Setup & Konfigurasi**: Lihat `deploy/README-CICD.md`.

## 7. Mobile Applications (Capacitor JS)
HadirYuk menyediakan aplikasi mobile Android native berbasis wrapper Capacitor JS mandiri yang terisolasi dari root Laravel Vite:
1. **HadirYuk Siswa (`mobile-student/`)**:
   - **App ID**: `com.thortech.hadiryuk.student`
   - **Target URL**: `https://hadiryuk.thortech.shop/pwa/clock-in`
   - **Fitur Utama**: Presensi selfie mandiri, GPS geofencing, integrasi kamera native, dan hardware back button handling.
2. **HadirYuk Orang Tua (`mobile-parent/`)**:
   - **App ID**: `shop.thortech.hadiryuk.parent`
   - **Target URL**: `https://hadiryuk.thortech.shop/parent/dashboard`
   - **Fitur Utama**: Pemantauan presensi dan riwayat kehadiran anak secara real-time, izin jaringan aman (`usesCleartextTraffic="false"`), branding resmi HadirYuk, dan navigasi WebView dengan hardware back button handling.
   - **Build Toolchain**: CLI-only build via Gradle Wrapper (`gradlew.bat assembleDebug`) memanfaatkan JDK 21 LTS (`.jdks/jbr-21.0.11`) dan Android SDK tanpa dependensi GUI Android Studio.

