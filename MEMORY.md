# Project Memory: HadirYuk

## Project Overview
HadirYuk is a **multi-tenant SaaS attendance management system** designed for educational institutions. The platform enables schools and educational organizations to manage student and teacher attendance through various methods including manual entry, biometric devices, and real-time monitoring.

## System Vision & Architecture

### Multi-Tenant SaaS Structure
- **Subdomain-based tenant separation** (e.g., `school1.hadiryuk.com`, `educenter.hadiryuk.com`)
- Each tenant has its own isolated database and configuration
- Headmasters can manage multiple schools from a central dashboard
- Operators manage individual schools
- Students, teachers, and parents have role-based access

### Core Architecture
- **Frontend**: Vue.js 3 with Vite build system, Alpine.js for interactivity
- **Backend**: Laravel 13.8 Framework with PHP 8.3+
- **Database**: MySQL/MariaDB with Eloquent ORM
- **Real-time Features**: WebSocket support, push notifications via web-push
- **Biometric Integration**: Hardware device API for fingerprint/face recognition
- **Security**: Role-based middleware, tenant isolation, OTP verification

## Technology Stack

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
1. **Multi-Tenant SaaS Infrastructure**
   - Subdomain-based tenant routing
   - Role-based access control (Headmaster, Operator, Teacher, Student, Parent)
   - Tenant onboarding wizard

2. **Core Management System**
   - Student Management (CRUD, import/export, password reset)
   - Teacher Management (CRUD, quick add, export CSV)
   - Class/Room Management (Jadwal Pelajaran KBM)
   - Parent-Student relationship management

3. **Attendance System**
   - Manual attendance entry
   - KBM (Kegiatan Belajar Mengajar) attendance tracking
   - Biometric device integration endpoints
   - Real-time clock-in/clock-out functionality

4. **Biometric Device Integration**
   - Database migrations for biometric devices, mappings, and logs
   - Auto-discovery API endpoints (`/api/v1/biometric/push`)
   - Device management in attendance settings

5. **Bug Fixes & Improvements**
   - Fixed SchoolClass dropdown display issue (full_name accessor)
   - Fixed teacher dummy emails lowercase migration

### 🟡 In Progress / Partially Completed
1. **Biometric Frontend UI**
   - Self-service setup instructions
   - Device status monitoring (Online/Offline)
   - Claim and connect functionality for pending devices
   - Copy-to-clipboard for endpoints and secret keys

2. **Testing & Verification**
   - API endpoint testing with sample payloads
   - Frontend UI functionality testing
   - Auto-discovery verification
   - Claim functionality verification

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
