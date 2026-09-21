# Dokumentasi & Matriks Pengujian Presensi Siswa (HadirYuk)

Dokumen ini memuat daftar skenario uji kritis, matriks pengujian untuk adaptasi PWA ke mobile WebView (Capacitor), serta panduan stabilitas pengujian lintas database (SQLite vs PostgreSQL).

---

## 1. Audit Alur Sesi & Autentikasi PWA Clock-In

### 1.1 Mekanisme Sesi & Autentikasi Saat Ini
- **Route**:
  - `GET /pwa/clock-in` (`pwa.clock-in`) &rarr; Menampilkan antarmuka kamera liveness, kalkulasi geolokasi, dan tombol clock-in.
  - `POST /pwa/clock-in` (`pwa.store`) &rarr; Menerima payload presensi (`image_snapshot`, `face_match_score`, `latitude`, `longitude`).
- **Middleware Group**: `web`
  - Terbungkus dalam grup middleware `['auth', 'otp.verified']` dan `RoleMiddleware:student`.
  - **Sesi Web**: Bergantung penuh pada **cookie sesi web Laravel** (`auth:web`), default cookie name: `hadiryuk-session` (atau `[app_name]-session`), diserialisasi menggunakan JSON.
  - **CSRF Token**:
    - Request dikirim via AJAX `fetch()` dari form yang menyertakan input tersembunyi `_token` (`@csrf`) dan header `'X-Requested-With': 'XMLHttpRequest'`.
    - Dilindungi oleh middleware bawaan Laravel `ValidateCsrfToken`.
  - **Otentikasi Token (Sanctum/API)**: Saat ini **belum digunakan** pada alur `/pwa/clock-in` karena halaman dan endpoint tersebut masih beroperasi murni dalam ekosistem route web Laravel berbasis session cookie.

### 1.2 Konfigurasi CORS & Domain Sesi
- **`config/cors.php`**:
  - `paths`: `['api/*', 'sanctum/csrf-cookie']`. Endpoint `/pwa/*` **belum masuk** dalam path CORS karena ditargetkan untuk *same-origin* request di browser.
  - `supports_credentials`: `true`.
  - `allowed_origins`: Diambil dari `env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')`.
- **`config/session.php`**:
  - `driver`: `env('SESSION_DRIVER', 'database')`.
  - `domain`: `env('SESSION_DOMAIN')` (default `null`, mengikat ke root domain/subdomain pemanggil).
  - `same_site`: `'lax'` (default).
  - `http_only`: `true`.
  - `secure`: `env('SESSION_SECURE_COOKIE')`.

> [!IMPORTANT]
> **Implikasi untuk Capacitor JS**:
> Capacitor di Android secara bawaan berjalan pada skema lokal (`http://localhost` atau `https://localhost` atau `capacitor://localhost`).
> Ketika WebView Capacitor mengakses halaman remote server (`https://[subdomain].hadiryuk.id/pwa/clock-in`) secara langsung (Server-Driven WebView):
> - Cookie sesi web dan CSRF token akan bekerja secara native layaknya browser Chrome selama WebView tidak memblokir third-party cookie atau cross-origin policy.
> - Jika menggunakan Single Page App (SPA) lokal Capacitor yang melakukan `fetch()` ke remote API, domain CORS, Sanctum CSRF cookie, dan `SESSION_DOMAIN` harus dikonfigurasi secara eksplisit.

---

## 2. Matriks Pengujian Kritis (Critical Test Matrix)

| Kategori | Skenario Uji | Kondisi / Input | Ekspektasi Hasil | Berkas Uji Terkait |
| :--- | :--- | :--- | :--- | :--- |
| **Otentikasi & Sesi** | Siswa Login via Web/PWA | Kredensial valid (NISN/Email + Password) + School Code | Redirect ke `/student/dashboard`, session cookie tersimpan, bypass OTP jika role student | `tests/Feature/StudentAuthAndPasswordPolicyTest.php` |
| **Otentikasi & Sesi** | Siswa Login via WebView Mobile | User-Agent mobile / Capacitor WebView, session cookie persistence | Sesi tersimpan lintas pembukaan aplikasi; tidak logout saat WebView di-pause | Diuji pada tahap integrasi Capacitor |
| **Otentikasi & Sesi** | Proteksi Role Non-Siswa | Akun Guru/Operator/Orang Tua membuka `/pwa/clock-in` | HTTP 403 Forbidden (dicegah oleh `RoleMiddleware:student`) | `tests/Feature/RoleMiddlewareTest.php` |
| **Geolokasi** | Presensi di Dalam Radius | Siswa berada &le; `radius_meters` dari koordinat sekolah | Presensi diterima (HTTP 200, `success: true`) | `tests/Feature/WifiIpVerificationTest.php` |
| **Geolokasi** | Presensi di Luar Radius | Jarak GPS siswa > `radius_meters` pengaturan tenant | HTTP 400 Bad Request (`"Anda berada di luar radius sekolah."`) | `PwaAttendanceController::store` |
| **Geolokasi** | Izin Lokasi Ditolak / Null | Request `POST /pwa/clock-in` tanpa `latitude` / `longitude` | HTTP 400 Bad Request (`"Akses lokasi wajib diaktifkan."`) | `PwaAttendanceController::store` |
| **Kamera & Snapshot** | Upload Foto Snapshot Base64 | String Base64 format JPEG/PNG (~30-50 KB) | File disimpan ke `storage/app/public/attendances/{tenant_id}/{date}/` | `PwaAttendanceController::store`, `tests/Feature/ArchiveAttendancePhotosTest.php` |
| **Kamera & Snapshot** | Liveness Detection (Blink) | Landmark mata EAR < 0.22 terdeteksi di kamera | State `livenessVerified` berubah true, tombol clock-in aktif | Pengujian Browser/PWA Client (`clock-in.blade.php`) |
| **Jadwal & Waktu** | Presensi Sesuai Jam Operasional | Jam presensi di dalam `time_in` s/d `time_out` (Daily Arrival) | Status `present` atau `late` tercatat dengan benar | `tests/Feature/StudentClockInButtonLogicTest.php` |
| **Jadwal & Waktu** | Presensi di Luar Jam Operasional | Jam presensi di luar rentang jadwal harian | Tombol disabled / ditolak (`"Batas Waktu Presensi Habis"`) | `tests/Feature/StudentClockInButtonLogicTest.php` |
| **Jadwal & Waktu** | Presensi Berbasis Sesi KBM | Tenant mode `session_based`, presensi pada sesi mata pelajaran aktif | `attendance_type = 'class'`, tercatat `class_schedule_id` | `tests/Feature/KbmClassAttendanceTest.php`, `tests/Feature/NonFormalAttendanceTest.php` |
| **Jadwal & Waktu** | Presensi Hari Minggu | Hari Minggu tanpa jadwal resmi vs dengan jadwal resmi | Ditolak (HTTP 422) jika tidak ada jadwal; Diterima jika ada jadwal aktif | `tests/Feature/SundayAttendanceTest.php` |
| **Integritas Data** | Pencegahan Presensi Ganda | Request clock-in kedua pada hari yang sama / sesi yang sama | HTTP 200 dengan `already_attended: true`, tidak membuat duplikat | `tests/Feature/StudentClockInButtonLogicTest.php`, `PwaAttendanceController::store` |
| **Verifikasi Wi-Fi** | Deteksi IP Jaringan Sekolah | IP client cocok dengan subnet / IP whitelist tenant | `is_wifi_verified = true` | `tests/Feature/WifiIpVerificationTest.php`, `tests/Feature/MultiWifiIpSettingTest.php` |

---

## 3. Stabilitas Pengujian: SQLite (CI/CD) vs PostgreSQL (Produksi)

Aplikasi HadirYuk menggunakan **SQLite in-memory** (`:memory:`) untuk pengujian otomatis di lingkungan pengembang lokal dan GitHub Actions (`.github/workflows/deploy.yml`), sementara lingkungan produksi menggunakan **PostgreSQL**.

### 3.1 Perbedaan & Titik Rawan (Gotchas)
1. **Fungsi Tanggal dan Waktu**:
   - SQLite tidak memiliki fungsi bawaan seperti `TO_CHAR()`, `EXTRACT(DOW FROM ...)`, atau `NOW() AT TIME ZONE`.
   - *Solusi*: Selalu gunakan **Carbon / Eloquent datetime casting** di level PHP (`now('Asia/Jakarta')`, `$date->format('Y-m-d')`) sebelum menyusun query builder.
2. **Boolean Type Handling**:
   - SQLite menyimpan boolean sebagai integer (`0` atau `1`), sedangkan PostgreSQL memiliki tipe data native `BOOLEAN` (`true` atau `false`).
   - *Solusi*: Selalu gunakan casting `(bool)` atau `$casts = ['is_active' => 'boolean']` pada Model Eloquent agar asersi pengujian (`assertTrue`/`assertFalse`) konsisten.
3. **Foreign Key Enforcement**:
   - Pada SQLite, foreign keys harus diaktifkan secara eksplisit (`PRAGMA foreign_keys = ON;`). Laravel Migration menangani hal ini, namun pembersihan data via `truncate` sering kali gagal di SQLite tanpa `Schema::disableForeignKeyConstraints()`.
   - *Solusi*: Gunakan trait `RefreshDatabase` pada setiap test class feature.
4. **Session Driver pada Testing**:
   - Di `phpunit.xml`, `SESSION_DRIVER` disetel ke `array`. Hal ini mengisolasi sesi dalam memori pengujian tanpa memerlukan tabel `sessions` fisik di SQLite.
   - Pada pengujian yang melibatkan persistent session cookie (seperti WebView / Browser testing via Laravel Dusk atau Playwright), session driver `database` atau `file` harus digunakan jika menguji lifecycle session lintas request.

---

## 4. Rencana Verifikasi Berkelanjutan untuk Capacitor

Saat mengintegrasikan Capacitor JS:
1. **Verifikasi Izin Hardware (Android Manifest)**:
   - `android.permission.CAMERA`
   - `android.permission.ACCESS_FINE_LOCATION`
   - `android.permission.ACCESS_COARSE_LOCATION`
2. **Verifikasi WebView Settings**:
   - Memastikan `mixedContentMode` dan `domStorageEnabled` aktif pada `MainActivity.java` / `capacitor.config.json` agar kamera WebRTC (`getUserMedia`) dan Geolocation API berfungsi di dalam WebView.
3. **Verifikasi Safe Area & Viewport**:
   - Memastikan notch / status bar Android tidak menutupi tombol kembali dan header presensi.

---

## 5. Mobile Capacitor Setup & SOP Verification

### 5.1 Perintah Operasional
- **Sinkronisasi Web & Konfigurasi ke Android**:
  ```bash
  cd mobile-student && npx cap sync android
  ```
- **Membuka Proyek di Android Studio**:
  ```bash
  cd mobile-student && npx cap open android
  ```
- **Kompilasi Debug APK via Command Line**:
  ```bash
  cd mobile-student/android && ./gradlew assembleDebug
  ```

### 5.2 Standar Operasional Prosedur (SOP) Verifikasi di Perangkat Fisik / Emulator
1. **Verifikasi Izin Runtime (Kamera & GPS)**:
   - Saat aplikasi pertama kali dibuka dan masuk ke halaman `/pwa/clock-in`, sistem Android harus memunculkan dialog persetujuan:
     - *"Izinkan HadirYuk Siswa mengakses kamera?"* &rarr; Pilih **Saat aplikasi digunakan**.
     - *"Izinkan HadirYuk Siswa mengakses lokasi perangkat ini?"* &rarr; Pilih **Saat aplikasi digunakan (Akurat / Precise)**.
   - Pastikan tag `<uses-permission>` dan `<uses-feature>` di [`AndroidManifest.xml`](file:///c:/Users/Bagus/Documents/HadirYuk/mobile-student/android/app/src/main/AndroidManifest.xml) sudah terdaftar.
2. **Verifikasi WebRTC Camera Stream & Liveness Detection**:
   - Kamera depan aktif otomatis tanpa layar hitam (*black screen*).
   - Indikator oval mendeteksi wajah siswa.
   - Gerakan kedipan mata (*blink detection* dengan threshold EAR < 0.22) berhasil memvalidasi status liveness (*hijau*).
3. **Verifikasi Geofencing**:
   - Koordinat GPS siswa terbaca akurat.
   - Jika berada di dalam radius sekolah, tombol **Clock In Sekarang** dapat ditekan dan presensi tercatat sukses.
   - Jika berada di luar radius sekolah, muncul notifikasi error yang ramah: *"Anda berada di luar radius sekolah."*
4. **Verifikasi Hardware Back Button (`MainActivity.java`)**:
   - Tekan tombol kembali / gesture back di Android dari halaman `/pwa/clock-in`: WebView harus mundur ke halaman `/student/dashboard` tanpa langsung keluar dari aplikasi.
   - Tekan tombol kembali dari halaman `/student/dashboard`: Aplikasi memicu `super.onBackPressed()` dan meminimalkan/menutup aplikasi dengan aman.
5. **Verifikasi Persistensi Sesi**:
   - Tutup aplikasi dari *recent apps* (kill process) lalu buka kembali.
   - Siswa harus tetap dalam keadaan terautentikasi (*logged in*) tanpa diminta login ulang, mengonfirmasi cookie sesi web disimpan dengan baik oleh WebView.

