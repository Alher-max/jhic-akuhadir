# Project Memory: HadirYuk

## Project Overview
HadirYuk! adalah platform **SaaS Presensi Multi-Tenant monolitik** yang mendukung 4 segmen industri utama melalui pendekatan **Monolithic Multi-Variant dinamis**:
- **HadirSekolah**: Sekolah & madrasah (Siswa, Guru, Mapel, KBM, NPSN).
- **HadirElco**: Lembaga kursus & bimbel (Peserta, Tutor, Modul, Sesi, Sertifikat Digital).
- **HadirUMKM**: Usaha retail & operasional (Karyawan, Shift, Geofencing outlet).
- **HadirCorporate**: Perusahaan enterprise (Karyawan, Manager, Departemen, Proyek).

## Dynamic Multi-Variant Subsystem
Sistem menghindari replikasi file views dengan pendekatan konfigurasi terpusat:
- **Configuration**: `config/variants.php` memetakan metadata tiap segmen (nama aplikasi, skema warna HEX, badge CSS, terminologi peran & navigasi).
- **Helper**: `app/Services/VariantHelper.php` mendeteksi segmen aktif via session, subdomain, atau field `institution_type` tenant.
- **View Composer**: `app/Providers/AppServiceProvider.php` menyuntikkan `$currentVariant`, `$isElco`, `$isSekolah`, dsb., serta menginjeksikan CSS variables `:root { --brand-primary: ...; }` ke layout utama (`app.blade.php`, `guest.blade.php`).

## System Vision & Architecture

### Multi-Tenant SaaS Structure
- **Subdomain & Column-level tenant separation** (e.g., `school1.hadiryuk.com`, `educenter.hadiryuk.com`, via `tenant_id`)
- Headmasters/Owners can manage their respective institutions from a central dashboard
- Operators/Managers manage individual schools/branches/outlets
- Students/Members, teachers/tutors, and parents have role-based access

### Backend (PHP/Laravel)
- **Framework**: Laravel 13.8
- **Database**: MySQL/MariaDB
- **ORM**: Eloquent
- **API**: RESTful API with JSON responses
- **Web Push**: minishlink/web-push for device notifications
- **Validation**: Built-in Laravel validation
- **Testing**: PHPUnit with Laravel Test framework

### Frontend (JavaScript/TypeScript)
- **Framework**: Vue.js 3 (Composition API)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with Forms plugin
- **Interactivity**: Alpine.js
- **State Management**: Vuex (if implemented) or reactive patterns
- **HTTP Client**: Axios
- **UI Components**: Custom components with Tailwind styling

### DevOps & Infrastructure
- **Version Control**: Git with structured commit messages
- **Package Management**: Composer (PHP), npm/pnpm (Node.js)
- **Environment**: .env files with APP_ENV, APP_DEBUG settings
- **Logging**: Laravel logging system
- **Queue System**: Redis/RabbitMQ for background jobs

## Current Project Status

### ✅ Completed Features
1. **Production Deployment & Cloudflare Tunnel Migration (12 September 2026)**
   - Migrasi penuh dari Nginx ke **Cloudflare Tunnel** (`cloudflared`) dengan Tunnel ID `bbd420f0-9cdc-4fb9-a174-30b8bdebb051`.
   - Ingress routing: `hadiryuk.thortech.shop` dan `*.hadiryuk.thortech.shop` langsung ke `127.0.0.1:8005`.
   - Menjalankan aplikasi via systemd service `hadiryuk.service` pada host port 8005 (template: `deploy/systemd/hadiryuk.service.example`).
   - Virtual host Nginx untuk HadirYuk telah dinonaktifkan sepenuhnya.
   - Verifikasi trafik produksi: Respons `HTTP/2 200` pada `https://hadiryuk.thortech.shop` berhasil.
   - Konfigurasi `$middleware->trustProxies(at: '*')` di `bootstrap/app.php` untuk mempercayai HTTPS headers dari Cloudflare.

2. **Multi-Tenant SaaS Infrastructure**
   - Subdomain-based tenant routing & column-level `tenant_id` isolation
   - Role-based access control (Headmaster, Operator, Teacher, Student, Parent)
   - Tenant onboarding wizard

3. **Core Management System**
   - Student Management (CRUD, import/export, password reset)
   - Teacher Management (CRUD, quick add, export CSV)
   - Class/Room Management (Jadwal Pelajaran KBM)
   - Parent-Student relationship management

4. **Attendance System**
   - Manual attendance entry
   - KBM (Kegiatan Belajar Mengajar) attendance tracking
   - Biometric device integration endpoints
   - Real-time clock-in/clock-out functionality

5. **Biometric Device Integration**
   - Database migrations for biometric devices, mappings, and logs
   - Auto-discovery API endpoints (`/api/v1/biometric/push`)
   - Device management in attendance settings

6. **Multi-Variant Architecture & Digital Certificates**
   - Centralized multi-variant config (`config/variants.php`) & helper (`VariantHelper.php`)
   - Modul Sertifikat Digital (`CertificateController`, `Certificate` model, auto numbering `ELCO/YYYY/MM/XXXX`, and public QR verification `/verify-certificate/{token}`)

### 🟡 In Progress / Next Active Checklist
1. **Validasi Modul Sertifikat Digital (Elco Variant)**
   - [ ] Testing alur upload sertifikat manual oleh operator/pengajar
   - [ ] Validasi hak akses download & verifikasi publik via QR token

2. **Validasi Alur Multi-Varian**
   - [ ] Verifikasi konsistensi branding & terminologi pada 4 segmen: HadirSekolah, HadirElco, HadirUMKM, HadirCorporate
   - [ ] Pengujian registrasi tenant dan member untuk tiap varian

3. **Hardware Biometrik Frontend UI**
   - [ ] Selesaikan instruksi self-service setup pada tab alat
   - [ ] Tampilkan status indikator Online/Offline mesin (ping < 5 min)
   - [ ] Fitur klaim & hubungkan untuk pending biometric devices


### 🔴 Pending / Not Started
1. **Advanced Features**
   - Comprehensive reporting system
   - Leave request management
   - Support ticket system
   - Student card management

2. **Frontend Components**
   - Complete UI for attendance settings
   - Device status indicators
   - Real-time device monitoring

## Task/roadmap

### High Priority (Next 2-4 weeks)
1. **Biometric Device Frontend UI**
   - Implement self-service setup section
   - Complete device claim functionality
   - Add device status monitoring
   - Implement copy-to-clipboard features

2. **Testing & Quality Assurance**
   - Complete API endpoint testing
   - Implement frontend UI testing
   - Verify all core functionality
   - Performance optimization

### Medium Priority (1-2 months)
1. **Advanced Attendance Features**
   - Biometric device management interface
   - Real-time attendance analytics
   - Bulk attendance operations

2. **Reporting & Analytics**
   - Daily/Monthly attendance reports
   - Export functionality (Excel, PDF)
   - Dashboard analytics

### Low Priority (3+ months)
1. **Mobile Application**
   - iOS/Android native app
   - Offline attendance sync
   - Push notifications

2. **Advanced Integration**
   - Third-party system integrations
   - API marketplace
   - Custom integrations

## Deployment & Environment

### Development Environment
```bash
# Project setup
composer install
php artisan key:generate
php artisan migrate --force
npm install --ignore-scripts
npm run build

# Run development server
php artisan serve
npm run dev

# Test suite
php artisan test
```

### VPS Deployment Instructions
1. **Prerequisites**
   - Ubuntu/Debian server
   - PHP 8.3+
   - MySQL 8.0+
   - Node.js 18+
   - Nginx/Apache

2. **Installation Steps**
   ```bash
   # Clone repository
   git clone <repository-url>
   cd hadiryuk
   
   # Environment setup
   cp .env.example .env
   php artisan key:generate
   
   # Database setup
   php artisan migrate --force
   
   # Dependencies
   composer install --no-interaction --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   
   # Server configuration
   # Configure nginx with proper server blocks
   # Set up cron jobs for maintenance tasks
   ```

3. **Asset Management**
   - Frontend assets: compiled to `public/assets/`
   - Images: stored in `storage/app/public`
   - Backup: regular database and storage backups

4. **Environment Variables**
   ```env
   APP_NAME=HadirYuk
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://hadiryuk.com
   
   APP_DOMAIN=hadiryuk.com
   APP_SCHEME=https
   
   # Database
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hadiryuk
   DB_USERNAME=hadiryuk
   DB_PASSWORD=securepassword
   
   # Redis for queues/sessions
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   
   # Web Push
   VAPID_PUBLIC_KEY=your_vapid_public_key
   VAPID_PRIVATE_KEY=your_vapid_private_key
   ```

### Maintenance Tasks
1. **Daily**
   - Monitor server health
   - Check biometric device connectivity
   - Backup database

2. **Weekly**
   - Update dependencies
   - Clear cache
   - Monitor performance

3. **Monthly**
   - Complete database backups
   - Review system logs
   - Update documentation

### Troubleshooting

#### Common Issues
1. **Biometric Device Not Found**
   - Check device network connectivity
   - Verify secret key configuration
   - Restart device if necessary

2. **Database Connection Issues**
   ```bash
   php artisan db:console
   # Test connection
   ```

3. **Frontend Build Errors**
   ```bash
   npm run build
   # Check for missing dependencies
   ```

4. **Memory Issues**
   - Increase PHP memory limit
   - Optimize database queries
   - Clear application cache

## Future Roadmap

### Phase 1 (Q1 2025)
- [ ] Complete biometric device frontend
- [ ] Implement comprehensive testing
- [ ] Add attendance analytics dashboard
- [ ] Mobile app MVP

### Phase 2 (Q2 2025)
- [ ] Advanced reporting features
- [ ] Third-party integrations
- [ ] API marketplace
- [ ] Multi-language support

### Phase 3 (Q3-Q4 2025)
- [ ] Advanced AI features
- [ ] Blockchain for attendance verification
- [ ] IoT device integrations
- [ ] Enterprise features

## Notes & Conventions

### Code Quality
- **PHP**: PSR-2 coding standards
- **JavaScript**: ES6+ with Vue.js conventions
- **Documentation**: Markdown format
- **Commit Messages**: Structured with type(scope): description

### Backup & Recovery
- Database backups: Daily automated
- File backups: Weekly
- Point-in-time recovery: Available

### Support & Documentation
- Primary documentation: This file (MEMORY.md)
- API documentation: Swagger/OpenAPI (if implemented)
- User guides: Will be added as needed
- Troubleshooting: This section above
