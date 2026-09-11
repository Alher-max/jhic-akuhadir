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
   - Selfie + Master Photo face verification.
   - Biometric Hardware Push Webhook (`/api/v1/biometric/push`) dengan auto-discovery Serial Number.
2. **Digital Certificate (Elco Variant)**:
   - Modul penerbitan sertifikat dengan penomoran unik (`ELCO/YYYY/MM/XXXX`).
   - Token-based verification route (`/verify-certificate/{token}`) untuk scan QR code publik.
3. **Role & Tenant Management**:
   - Tenant isolation via global scope & middleware.
   - Onboarding wizard, forced password change (`must_change_password`), dan verifikasi OTP.

## 5. Deployment & Gateway Architecture
- **Trafik Ingress**:
  `Client -> Cloudflare Edge (SSL Termination & WAF) -> Cloudflare Tunnel (QUIC) -> 127.0.0.1:8005 (hadiryuk.service) -> Laravel Framework`.
- **Cloudflare Tunnel**:
  - Tunnel ID: `bbd420f0-9cdc-4fb9-a174-30b8bdebb051`
  - Ingress routing: `hadiryuk.thortech.shop` dan wildcard `*.hadiryuk.thortech.shop` langsung diarahkan ke `http://127.0.0.1:8005`.
- **Reverse Proxy Status**:
  - Virtual host Nginx untuk HadirYuk telah **dinonaktifkan sepenuhnya**. Nginx tidak lagi menjadi reverse proxy HadirYuk.
- **Service Management**:
  - Dikelola via systemd service `hadiryuk.service` pada host port `8005` (lihat `deploy/systemd/hadiryuk.service.example`).
- **Trusted Proxies**:
  - Laravel mengonfigurasi `$middleware->trustProxies(at: '*')` di `bootstrap/app.php` untuk mempercayai header reverse proxy dari Cloudflare Tunnel (`X-Forwarded-Proto`, `CF-Connecting-IP`, dsb.).

