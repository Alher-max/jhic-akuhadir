# CODEBASE AUDIT & ARCHITECTURAL RECONNAISSANCE REPORT
**Platform:** HadirYuk! (hadir.io / akuhadir.my.id)  
**Peran Auditor:** Principal Software Architect  
**Tanggal Audit:** 03 Oktober 2026  
**Status Audit:** Faktual, Berdasarkan Kode Nyata (Verified against Codebase)  
**Tujuan:** Fondasi Desain & Implementasi "Modul Rapor Sekolah (Kurikulum Merdeka, SMK, & Madrasah)"

---

## 1. RINGKASAN ARSITEKTUR FAKTUAL

### 1.1 Profiling Stack & Dependensi
* **Backend Framework:** Laravel Framework 13.20.0 (PHP `^8.2|^8.3|^8.4`, targeted runtime PHP 8.3/8.4 LTS). File entry: [`bootstrap/app.php`](file:///c:/Users/Bagus/Documents/JHIC/bootstrap/app.php).
* **Dependensi Utama Terpasang ([`composer.json`](file:///c:/Users/Bagus/Documents/JHIC/composer.json)):**
  - `laravel/framework`: `^13.8`
  - `laravel/tinker`: `^3.0`
  - `minishlink/web-push`: `^8.0` (WebPush PWA notifications)
  - `livewire/livewire`: `^4.3` (Livewire 4 single-file components di `resources/views/components/`)
  - `laravel/breeze`: `^2.4` (auth scaffolding dasar)
* **Paket yang Dikonfirmasi TIDAK Terpasang (Belum Ada):**
  - *Tenancy Package:* Tidak menggunakan `stancl/tenancy` ataupun `spatie/laravel-multitenancy`. Multi-tenancy sepenuhnya dibangun secara *in-house*.
  - *Role/Permission Package:* Tidak menggunakan `spatie/laravel-permission`. Hak akses dikelola menggunakan kolom string `users.role` + Laravel Gate + `RoleMiddleware`.
  - *PDF Generator:* Tidak ada `barryvdh/laravel-dompdf`, `snappy`, ataupun `browsershot`. Cetak dokumen menggunakan browser-native print rendering (`window.print()`).
  - *Excel/Spreadsheet Exporter:* Tidak ada `maatwebsite/excel` ataupun `phpoffice/phpspreadsheet`. Ekspor tabular menggunakan native PHP streaming (`php://output` via `fputcsv`).
* **Frontend Stack ([`package.json`](file:///c:/Users/Bagus/Documents/JHIC/package.json)):**
  - Tailwind CSS `^3.1.0` + `@tailwindcss/forms` (`^0.5.2`) + `@tailwindcss/vite` (`^4.0.0`)
  - Alpine.js `^3.4.2` + `@alpinejs/collapse` (`^3.15.12`)
  - Vite `^8.0.0` + `laravel-vite-plugin` (`^3.1`)
  - Axios `^1.19.0`
* **Mobile Stack (Capacitor JS Wrapper):**
  - Terletak pada dua sub-project mandiri: [`mobile-student/`](file:///c:/Users/Bagus/Documents/JHIC/mobile-student/capacitor.config.ts) dan [`mobile-parent/`](file:///c:/Users/Bagus/Documents/JHIC/mobile-parent/capacitor.config.ts).
  - Menggunakan `@capacitor/android: ^8.5.2`, `@capacitor/cli: ^8.5.2`, `@capacitor/core: ^8.5.2`, `@capacitor/app: ^8.1.1`.
  - **Pola Integrasi:** *Server-Driven Remote WebView* yang mengarah langsung ke host remote (`https://akuhadir.my.id`).
  - **Distribusi Binary:** File APK hasil build Gradle diletakkan di [`public/akuhadir-siswa.apk`](file:///c:/Users/Bagus/Documents/JHIC/public/akuhadir-siswa.apk) dan [`public/akuhadir-ortu.apk`](file:///c:/Users/Bagus/Documents/JHIC/public/akuhadir-ortu.apk), disajikan via [`app/Http/Controllers/ApkDownloadController.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Http/Controllers/ApkDownloadController.php) pada rute `/download/apk/{role}`.

---

### 1.2 Implementasi Multi-Tenancy & Data Scoping
* **Model Tenancy:** *Single Database, Shared Schema, Column-level Scoping* dengan foreign key `tenant_id` pada setiap tabel data sekolah.
* **Trait Kunci:** [`app/Traits/BelongsToTenant.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Traits/BelongsToTenant.php)
  - `creating`: Mengisi otomatis `$model->tenant_id = auth()->user()->tenant_id` saat create record oleh user login.
  - `addGlobalScope('tenant')`: Secara otomatis menyuntikkan klausa `WHERE [table].tenant_id = auth()->user()->tenant_id` pada seluruh kueri model.
  - Relasi `tenant()`: `belongsTo(Tenant::class)`.
* **Model Pengguna Trait:** `User`, `Attendance`, `Subject`, `ClassSchedule`, `Schedule`, `ActivitySchedule`, `Location`, `Invitation`, `LeaveRequest`.
* **Catatan Khusus Rombel (`SchoolClass`):** Model [`app/Models/SchoolClass.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Models/SchoolClass.php) memiliki kolom `tenant_id` namun **belum** menggunakan trait `BelongsToTenant` (masih mengandalkan `scopeForTenant` dan scoping manual di controller). Modul baru wajib konsisten menggunakan scoping `tenant_id`.
* **Deteksi Tenant Aktif:**
  1. *Subdomain Routing:* [`app/Http/Middleware/ResolveTenantSubdomain.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Http/Middleware/ResolveTenantSubdomain.php) mencocokkan `{subdomain}` dengan `Tenant::where('subdomain', $subdomain)->first()`, mendaftarkannya ke container `app()->instance('tenant', $tenant)`.
  2. *Sesi Autentikasi:* Setelah login, tenant aktif diambil dari `auth()->user()->tenant_id`.
  3. *Timezone Scoping:* [`app/Http/Middleware/SetTenantTimezone.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Http/Middleware/SetTenantTimezone.php) menyetel timezone dinamis per tenant (`date_default_timezone_set` & `config(['app.timezone'])`).

---

### 1.3 Autentikasi, Otorisasi, & Hierarki 6 Peran (RBAC)
* **Penyimpanan Peran:** Disimpan langsung pada kolom `users.role` (string) dan `users.position` (string) di tabel `users`.
* **Hierarki & Mapping Peran ([`app/Http/Middleware/RoleMiddleware.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Http/Middleware/RoleMiddleware.php) & [`app/Providers/AppServiceProvider.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Providers/AppServiceProvider.php)):**
  1. **Kepala Sekolah (`headmaster`):** Alias: `kepala_sekolah`, `owner`. Memiliki *Master Bypass* via `Gate::before(fn ($user) => true)`. Akses analitik, pemantauan guru & kelas.
  2. **Operator (`operator`):** Alias: `admin_dapodik`, `admin`. Pengelola master data sekolah, rombel, guru, siswa, jadwal KBM, dan tiket dukungan.
  3. **Guru (`teacher`):** Alias: `guru`, `guru_mapel`, `manager_teacher`. Pengampu KBM, pencatat presensi manual & KBM.
  4. **Wali Kelas (`wali_kelas`):**
     - Memiliki dashboard khusus di `/homeroom/dashboard` via [`app/Http/Controllers/AdminDashboardController.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Http/Controllers/AdminDashboardController.php#L318).
     - Relasi data: Kolom `school_classes.wali_kelas_id` merujuk ke `users.id`.
     - Relasi Eloquent:
       * `SchoolClass::waliKelas()` -> `belongsTo(User::class, 'wali_kelas_id')`
       * `User::homeroomClass()` -> `hasOne(SchoolClass::class, 'wali_kelas_id')`
       * `User::homeroomClasses()` -> `hasMany(SchoolClass::class, 'wali_kelas_id')`
  5. **Orang Tua (`parent`):**
     - Dashboard `/parent/dashboard`.
     - Relasi ke anak: Relasi langsung `User::parent()` (`parent_id`), dan relasi many-to-many melalui tabel pivot `parent_student` (`parent_id`, `student_id`, `relationship`).
  6. **Siswa (`student`):**
     - Alias: `member`.
     - Implementasi Model: Single Table Inheritance (STI) di [`app/Models/Student.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Models/Student.php) (`class Student extends User`) dengan global scope `where('role', 'student')`.
     - Seluruh atribut identitas siswa (NISN, NIS, NIK, tanggal lahir, nama ortu, dsb.) berada langsung pada tabel `users`.
* **Profil Entitas Guru/Staf:** Tabel [`user_profiles`](file:///c:/Users/Bagus/Documents/JHIC/app/Models/UserProfile.php) menyimpan detail NUPTK, NIP (`employee_id`), status kepegawaian, golongan/pangkat (`rank_group`), jabatan fungsional, dan pendidikan terakhir.

---

## 2. PETA SKEMA DATABASE & MODEL UTAMA

Berikut adalah peta struktur tabel eksisting yang relevan langsung dengan modul penilaian dan rapor:

```
+---------------------------------------------------------------------------------------------------+
|                                            tenants                                                |
| id | name | npsn | institution_type | attendance_mode | working_days | timezone | logo_path | ... |
+---------------------------------------------------------------------------------------------------+
        ^                                 ^                                       ^
        | 1:N                             | 1:N                                   | 1:N
+--------------------+           +----------------------+               +---------------------------+
|   school_classes   |           |       subjects       |               |           users           |
| id                 |           | id                   |               | id                        |
| tenant_id          |           | tenant_id            |               | tenant_id                 |
| nama_kelas         |           | code                 |               | role (operator/teacher/   |
| jenjang            |           | name                 |               |       wali_kelas/student) |
| tingkat            |           | is_preset            |               | name, email, nisn, nis    |
| wali_kelas_id (FK) |----+      | preset_type          |               | class_id (FK) ------------+
+--------------------+    |      +----------------------+               +---------------------------+
        ^                 |                 ^                                     ^   ^
        | 1:N             |                 | 1:N                                 |   |
        |                 +-------------> [User]                                  |   |
        |                                   ^                                     |   |
+------------------------------------+      | 1:N (teacher_id)                    |   |
|          class_schedules           |------+                                     |   |
| id                                 |                                            |   |
| tenant_id                          |                                            |   |
| class_id (FK)                      |                                            |   |
| subject_id (FK)                    |                                            |   |
| teacher_id (FK)                    |                                            |   |
| day_name, period_number            |                                            |   |
| start_time, end_time               |                                            |   |
+------------------------------------+                                            |   |
        ^                                                                         |   |
        | 1:N (class_schedule_id)                                                 |   |
+-----------------------------------------------------------------------------+   |   |
|                                 attendances                                 |   |   |
| id | tenant_id | user_id (FK) | attendance_type (school|class)              |---+   |
| class_schedule_id (FK nullable) | date | clock_in | clock_out               |       |
| status (present | late | sick | permission | alpha | duty_trip)             |       |
+-----------------------------------------------------------------------------+       |
                                                                                      |
+-----------------------------------------------------------------------------+       |
|                                parent_student                               |       |
| parent_id (FK to users) | student_id (FK to users) | relationship           |-------+
+-----------------------------------------------------------------------------+
```

### Rincian Kolom Relasi Kunci:
1. **`users.class_id` -> `school_classes.id`:** Menentukan kelas aktif seorang siswa.
2. **`school_classes.wali_kelas_id` -> `users.id`:** Menentukan guru yang bertugas sebagai Wali Kelas.
3. **`class_schedules.class_id` -> `school_classes.id`:** Rombel target pembelajaran.
4. **`class_schedules.subject_id` -> `subjects.id`:** Mata pelajaran yang diajarkan.
5. **`class_schedules.teacher_id` -> `users.id`:** Guru mata pelajaran yang mengampu.
6. **`attendances.user_id` -> `users.id`:** Siswa/pengguna yang melakukan presensi.
7. **`attendances.class_schedule_id` -> `class_schedules.id`:** Keterkaitan presensi KBM dengan mata pelajaran tertentu.

---

## 3. ANALISIS TEMUAN & KONDISI EKSISTING

### 3.1 Status Tahun Ajaran & Semester
* **Temuan Faktual:** Di database saat ini **BELUM DITEMUKAN** tabel master `academic_years`, `school_years`, ataupun `semesters`.
* **Implementasi Berjalan Saat Ini:**
  - `StudentCardController.php` (baris 58): Menghitung tahun ajaran secara dinamis berbasis tahun kalender berjalan:
    ```php
    'academic_year' => date('Y') . '/' . (date('Y') + 1)
    ```
  - `ReportController.php` (baris 23-32) & `HeadmasterDashboardService.php` (baris 4-7): Menghitung semester secara dinamis berbasis bulan kalender Masehi:
    ```php
    // Semester 1 (Ganjil Masehi): Januari - Juni (Bulan 1 - 6)
    // Semester 2 (Genap Masehi): Juli - Desember (Bulan 7 - 12)
    $startDate = $month <= 6 ? Carbon::create(date('Y'), 1, 1) : Carbon::create(date('Y'), 7, 1);
    ```
  - *Catatan Kurikulum:* Dalam kalender pendidikan Indonesia, Semester Ganjil umumnya adalah Juli–Desember, dan Semester Genap adalah Januari–Juni tahun berikutnya. Modul rapor memerlukan tabel master tahun ajaran & semester resmi agar riwayat rapor siswa tidak tertimpa saat berganti tahun.

### 3.2 Status Kurikulum & Capaian Pembelajaran
* **Temuan Faktual:**
  - Tabel `school_classes` memiliki kolom `jenjang` dan `tingkat` (angka kelas), tetapi **belum memiliki** kolom `fase` (Fase A s.d. F untuk Kurikulum Merdeka) maupun status kurikulum (`kurikulum` = 'merdeka' | 'k13' | 'kemenag').
  - **Belum ditemukan** tabel untuk:
    * Capaian Pembelajaran (CP) / Tujuan Pembelajaran (TP)
    * Bobot Penilaian Formatif, Sumatif Lingkup Materi, dan Sumatif Akhir Semester (SAS)
    * Nilai Ekstrakurikuler
    * Catatan Perkembangan Karakter / Dimensi Profil Pelajar Pancasila (P5 / P2RA)

### 3.3 Logika Presensi yang Siap Diintegrasikan
* **Tabel Sumber Data:** [`attendances`](file:///c:/Users/Bagus/Documents/JHIC/app/Models/Attendance.php).
* **Klasifikasi Status Kehadiran Eksisting:**
  - `present` -> Hadir (Tepat Waktu)
  - `late` -> Terlambat (dalam rapor dihitung Hadir)
  - `sick` -> Sakit (S)
  - `permission` -> Izin (I)
  - `alpha` -> Tanpa Keterangan / Alpa (A)
* **Integritas Sinkronisasi Izin/Sakit:** Setiap pengajuan izin/sakit pada `leave_requests` yang di-approve oleh admin/wali kelas di [`app/Http/Controllers/Admin/AdminLeaveController.php`](file:///c:/Users/Bagus/Documents/JHIC/app/Http/Controllers/Admin/AdminLeaveController.php#L37-L50) secara otomatis membuat/memperbarui record di tabel `attendances` dengan `status = $leave->type`. Sehingga query rekap kehadiran rapor **cukup mengambil data dari tabel `attendances` saja**.

---

## 4. POIN EKSTENSI UNTUK MODUL RAPOR SEKOLAH

Agar Modul Rapor menyatu (*seamless*) dengan arsitektur HadirYuk tanpa merusak fitur presensi yang berjalan, rancangan modul harus mengikuti titik integrasi berikut:

### 4.1 Desain Skema Tabel Tambahan (Rapor Subsystem)
Semua tabel baru **wajib menyertakan kolom `tenant_id`** dan memanfaatkan trait `App\Traits\BelongsToTenant`.

1. **Master Tahun Ajaran & Semester (`academic_years` / `academic_terms`):**
   - Kolom: `id`, `tenant_id`, `name` (misal: '2026/2027'), `semester` ('ganjil' / 'genap'), `start_date`, `end_date`, `is_active` (boolean).
   - Menghubungkan rentang tanggal resmi semester dengan penarikan data kehadiran presensi.

2. **Metadata Kurikulum Rombel:**
   - Tambahkan kolom nullable pada `school_classes`:
     * `fase` (string, misal: 'A', 'B', 'C', 'D', 'E', 'F')
     * `curriculum_type` (string, default: 'merdeka', pilihan: 'merdeka', 'k13', 'kemenag')
     * `program_keahlian` / `konsentrasi_keahlian` (string, nullable, khusus varian SMK)

3. **Tujuan Pembelajaran / Kompetensi (`learning_objectives`):**
   - Kolom: `id`, `tenant_id`, `subject_id`, `class_id`, `teacher_id`, `academic_year_id`, `code` (misal: 'TP.1'), `description`, `quarter` (1 atau 2).

4. **Penilaian Siswa (`student_grades`):**
   - Kolom: `id`, `tenant_id`, `student_id` (FK `users.id`), `subject_id`, `class_id`, `academic_year_id`, `formatif_scores` (JSON/decimal), `sumatif_materi_scores` (JSON/decimal), `sumatif_akhir` (decimal), `final_score` (decimal), `achievement_notes` (teks deskripsi capaian tertinggi/terendah khas Kurikulum Merdeka).

5. **Rekap Rapor Akhir Siswa (`report_cards`):**
   - Kolom: `id`, `tenant_id`, `student_id`, `class_id`, `academic_year_id`, `sick_count`, `permission_count`, `alpha_count`, `extracurricular_notes` (JSON), `homeroom_notes` (catatan wali kelas), `promotion_status` (naik kelas/tinggal/lulus), `status` ('draft', 'locked', 'published').

---

### 4.2 Auto-Pull Rekap Presensi ke Nilai Kehadiran Rapor
Data absensi pada blangko rapor (Sakit, Izin, Alpa) dapat ditarik secara otomatis menggunakan query agregasi ANSI SQL standar tanpa perulangan lambat:

```php
$attendanceSummary = Attendance::withoutGlobalScopes()
    ->where('tenant_id', $tenantId)
    ->where('user_id', $studentId)
    ->where('attendance_type', 'school') // Gunakan level sekolah untuk kehadiran umum
    ->whereBetween('date', [$semesterStartDate, $semesterEndDate])
    ->selectRaw("
        SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as total_sick,
        SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as total_permission,
        SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as total_alpha,
        SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as total_present
    ")
    ->first();
```
*Keunggulan:* Menggunakan klausa `SUM(CASE ...)` yang 100% kompatibel di PostgreSQL, SQLite, dan MySQL tanpa ketergantungan driver.

---

### 4.3 Pola Rendering Cetak Rapor (Mematuhi Konvensi Codebase)
* **Keterbatasan Server:** VPS produksi berjalan pada paket Nebula dengan 2 vCPU Core dan 2GB RAM. Pemasangan library seperti Chrome Headless, Puppeteer, Browsershot, atau dompdf berukuran masif berisiko memicu OOM (*Out of Memory*) dan membekukan service background daemon `hadiryuk.service`.
* **Konvensi Terbukti:** Ikuti pola cetak [`resources/views/admin/reports/pdf.blade.php`](file:///c:/Users/Bagus/Documents/JHIC/resources/views/admin/reports/pdf.blade.php):
  1. Buat view Blade cetak rapor formal: `resources/views/reports/rapor-merdeka-print.blade.php`.
  2. Gunakan CSS standard cetak:
     ```css
     @media print {
         @page {
             size: A4 portrait;
             margin: 1.5cm 1.5cm 1.5cm 1.5cm;
         }
         .no-print { display: none !important; }
         .page-break { page-break-after: always; }
     }
     ```
  3. Sediakan tombol *"Cetak Rapor"* dan *"Download PDF"* yang memicu `window.print()` (di mana browser desktop/mobile menyediakan fitur bawaan *Save as PDF* dengan layout presisi tinggi).
  4. Manfaat: Eksekusi 0 MB beban memori PHP server, proses instan, dan kompatibel 100% saat dibuka di WebView mobile.

---

## 5. TEMUAN KHUSUS & REKOMENDASI TEKNIS (DEVELOPER GUIDELINES)

Sebelum pengembang menulis kode untuk Modul Rapor, ketentuan berikut **wajib dipatuhi**:

1. **Hindari `$table->enum(...)` pada Migrasi Baru:**
   - *Penyebab:* PostgreSQL membuat *check constraint* kaku untuk tipe enum. Di codebase ini, beberapa migrasi sebelumnya harus menggunakan raw DDL untuk men-drop check constraint (`DROP CONSTRAINT IF EXISTS ...`).
   - *Aturan:* Selalu gunakan `$table->string('status', 50)` atau `$table->string('curriculum_type', 30)` disertai validasi di form request (`Rule::in(...)`).

2. **Dukungan PostgreSQL dan SQLite:**
   - Jangan gunakan fungsi spesifik vendor MySQL seperti `FIELD()`, `TIME_TO_SEC()`, atau `IFNULL()`.
   - Gunakan standar ANSI SQL: `CASE WHEN ... THEN ... ELSE ... END` dan `COALESCE(...)`.
   - Untuk filter case-insensitive, gunakan parameterized binding: `whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($search) . '%'])`.

3. **Otorisasi Rapor Berbasis Peran:**
   - *Input Nilai:* Guru Mata Pelajaran (`teacher`) hanya berhak menginput nilai pada mapel dan kelas yang sesuai dengan penugasannya di `class_schedules`.
   - *Validasi & Catatan Rapor:* Wali Kelas (`wali_kelas`) berhak mengisi catatan karakter, ekstrakurikuler, dan mencetak rapor satu rombel penuh yang diasuhnya (`school_classes.wali_kelas_id = auth()->id()`).
   - *Penguncian & Otoritas Sekolah:* Kepala Sekolah (`headmaster`) dan Operator (`operator`) memiliki otoritas penuh untuk menerbitkan (*publish*) atau mengunci (*lock*) rapor sekolah.
   - *Akses Baca Siswa & Wali Murid:* Siswa (`student`) dan Orang Tua (`parent`) hanya dapat membaca rapor setelah status rapor berstatus `'published'` oleh pihak sekolah.

4. **Kompatibilitas Tampilan Mobile Capacitor:**
   - Karena aplikasi mobile orang tua dan siswa berjalan di atas *Server-Driven WebView* (`mobile-parent/` & `mobile-student/`), tampilan rapor hasil cetak/lihat wajib responsif dan ramah layar sentuh (menggunakan Tailwind CSS dengan touch target minimal 44x44px).

---

*Laporan ini disusun secara faktual berdasarkan hasil audit repositori `Alher-max/jhic-akuhadir` per 03 Oktober 2026.*
