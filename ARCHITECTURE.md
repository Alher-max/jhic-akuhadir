# HadirYuk! Architecture & Technical Specification

## 1. System Overview
HadirYuk! adalah platform SaaS Presensi Multi-Tenant monolitik yang mendukung 4 segmen industri utama melalui pendekatan Monolithic Multi-Variant dinamis:
- **HadirSekolah**: Sekolah & madrasah (Siswa, Guru, Mapel, KBM, NPSN).
- **HadirElco**: Lembaga kursus & bimbel (Peserta, Tutor, Modul, Sesi, Sertifikat Digital).
- **HadirUMKM**: Usaha retail & operasional (Karyawan, Shift, Geofencing outlet).
- **HadirCorporate**: Perusahaan enterprise (Karyawan, Manager, Departemen, Proyek).

## 2. Tech Stack
- **Backend Framework**: Laravel 11/13.x (PHP 8.3+)
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
     - Environment PHP 8.3 & Node 20 pada `ubuntu-latest`.
     - Caching dependensi Composer & NPM.
     - Eksekusi build aset frontend (`npm ci && npm run build`).
     - Eksekusi test suite otomatis (`php artisan test` dengan SQLite in-memory).
  2. **Continuous Deployment (`deploy` job)**:
     - Berjalan setelah job `test` sukses pada branch `main`.
     - Menggunakan action SSH `appleboy/ssh-action@v1.0.3` dengan credentials `SSH_HOST`, `SSH_USER`, `SSH_KEY`, `SSH_PORT`.
     - Menjalankan sinkronisasi kode (`git pull origin main`), pembaruan dependensi, migrasi database (`migrate --force`), optimasi cache Laravel, dan restart service `hadiryuk.service`.
- **Panduan Setup & Konfigurasi**: Lihat `deploy/README-CICD.md`.
