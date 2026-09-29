<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HadirYuk - Sistem Manajemen & Presensi Sekolah No. 1 di Indonesia</title>
    <meta name="description"
        content="Solusi presensi sekolah terlengkap dengan 5 metode absensi, dasbor multi-role, dan fitur otomatisasi tercanggih dengan harga termurah.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Material Symbols -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'brand-primary': 'var(--brand-primary)',
                        'brand-primary-hover': 'var(--brand-primary-hover)',
                        'brand-on-primary': 'var(--brand-on-primary)',
                        'brand-surface': 'var(--brand-surface)',
                        'brand-surface-variant': 'var(--brand-surface-variant)',
                        'brand-border': 'var(--brand-border)',
                        'brand-dark': 'var(--brand-text-main)',
                        'brand-muted': 'var(--brand-text-muted)',
                        'brand-bg': 'var(--brand-bg)',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .bg-grid-pattern {
            background-image: radial-gradient(#EAE2E3 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
</head>

<body class="bg-brand-bg text-brand-dark antialiased font-sans selection:bg-brand-primary selection:text-white">

    <!-- Navbar -->
    <nav x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)"
        :class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm border-b' : 'bg-transparent'"
        class="fixed w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="HadirYuk" class="h-10 w-auto">
                    <span class="font-black text-2xl tracking-tighter text-brand-dark">Hadir<span
                            class="text-brand-primary">Yuk</span></span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur" class="text-sm font-semibold hover:text-brand-primary transition-colors">Fitur</a>
                    <a href="#keunggulan"
                        class="text-sm font-semibold hover:text-brand-primary transition-colors">Keunggulan</a>
                    <a href="#faq" class="text-sm font-semibold hover:text-brand-primary transition-colors">FAQ</a>

                    <div class="flex items-center gap-3 pl-4 border-l">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-brand-primary">Dasbor Saya</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-sm font-bold border border-[#EAE2E3] text-[#1A1516] px-5 py-2.5 rounded-full hover:bg-[#F4EFEB] transition-colors">Masuk</a>
                            <a href="/register?role=owner"
                                class="bg-[#B81D24] text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-sm hover:bg-[#9B181E] hover:scale-105 transition-all">
                                Daftar Gratis
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Mobile Toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-brand-dark">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-b p-4 space-y-4">
            <a href="#fitur" @click="mobileMenuOpen = false" class="block font-bold">Fitur</a>
            <a href="#keunggulan" @click="mobileMenuOpen = false" class="block font-bold">Keunggulan</a>
            <a href="#faq" @click="mobileMenuOpen = false" class="block font-bold">FAQ</a>
            <hr>
            <a href="{{ route('login') }}"
                class="block font-bold text-[#1A1516] border border-[#EAE2E3] p-3 rounded-xl text-center hover:bg-[#F4EFEB]">Masuk
                ke Aplikasi</a>
            <a href="/register?role=owner"
                class="block bg-[#B81D24] text-white text-center py-3 rounded-xl font-bold hover:bg-[#9B181E]">Daftar
                Sekolah Gratis</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-24 pb-16 lg:pt-28 lg:pb-24 overflow-hidden">
        <div class="absolute inset-0 bg-grid-pattern opacity-40"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <!-- Competition Ecosystem Card -->
                <div class="mb-6 flex justify-center">
                    <div
                        class="inline-flex flex-col items-center gap-2.5 px-4 sm:px-6 py-2.5 sm:py-3 rounded-2xl bg-white/90 backdrop-blur-sm border border-slate-200/80 shadow-sm transition-all hover:shadow-md hover:border-slate-300 max-w-full">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                            <span class="text-[10px] sm:text-[11px] font-bold tracking-wider uppercase text-slate-500 text-center">
                                OFFICIAL PARTICIPANT — JAGOAN HOSTING INNOVATION COMPETITION (JHIC) 2.0
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                            <!-- 1. LOGO JHIC 2.0 -->
                            <img src="{{ asset('images/1. LOGO JHIC 2.0.png') }}"
                                alt="JHIC 2.0" class="h-6 sm:h-7 w-auto object-contain transition-transform hover:scale-105">

                            <!-- 2. Logo Jagoan Hosting -->
                            <img src="{{ asset('images/2. Logo Jagoan Hosting.png') }}"
                                alt="Jagoan Hosting" class="h-5 sm:h-6 w-auto object-contain transition-transform hover:scale-105">

                            <!-- 3. KOMDIGI -->
                            <img src="{{ asset('images/3. KOMDIGI.png') }}"
                                alt="KOMDIGI" class="h-6 sm:h-7 w-auto object-contain transition-transform hover:scale-105">

                            <!-- 4. Garuda Spark -->
                            <img src="{{ asset('images/4. Garuda Spark Full Color.png') }}"
                                alt="Garuda Spark" class="h-5 sm:h-6 w-auto object-contain transition-transform hover:scale-105">

                            <!-- 5. LOGO NGALUP -->
                            <img src="{{ asset('images/5. LOGO NGALUP.png') }}"
                                alt="Ngalup.co" class="h-4 sm:h-5 w-auto object-contain transition-transform hover:scale-105">
                        </div>
                    </div>
                </div>

                <h1
                    class="text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-tight mb-5 sm:mb-6 text-brand-dark">
                    Sistem Sekolah <span class="text-[#B81D24]">Lengkap & Canggih</span>. <br>
                    <span class="text-[#B81D24]">Harga Termurah</span> se-Indonesia.
                </h1>

                <p class="text-lg sm:text-xl text-brand-muted font-medium mb-8 leading-relaxed max-w-3xl mx-auto">
                    Solusi presensi 5 metode, dasbor 6 peran terpadu (Operator, Kepala Sekolah, Guru, Wali Kelas, Orang Tua & Siswa), cetak kartu siswa,
                    hingga notifikasi WhatsApp otomatis tanpa biaya server mahal.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/register?role=owner"
                        class="w-full sm:w-auto px-8 py-4 bg-[#B81D24] text-white hover:bg-[#9B181E] rounded-xl font-bold text-lg shadow-sm transition-all text-center">
                        Daftarkan Sekolah Sekarang
                    </a>
                    <a href="#fitur"
                        class="w-full sm:w-auto px-8 py-4 bg-white border border-[#EAE2E3] text-[#1A1516] hover:bg-[#F4EFEB] hover:border-slate-300 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-2">
                        <span>Pelajari Fitur</span>
                        <span class="material-symbols-outlined text-xl">arrow_downward</span>
                    </a>
                </div>

                <div
                    class="mt-16 flex flex-wrap justify-center items-center gap-8 opacity-50 grayscale hover:grayscale-0 transition-all">
                    <p class="w-full text-center text-xs font-bold uppercase tracking-widest mb-2">Dipercaya oleh
                        berbagai jenjang pendidikan</p>
                    <span class="font-black text-xl italic">PAUD/TK • SD/MI • SMP/MTs • SMA/SMK/MA • PONDOK PESANTREN •
                        PKBM & SKB</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Grid -->
    <section class="py-12 bg-white border-y">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-brand-bg border border-[#EAE2E3]">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">payments</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#1A1516]">Harga Termurah</h4>
                        <p class="text-xs text-[#6B5E60]">Investasi sistem sekolah paling efisien di Indonesia.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-brand-bg border border-[#EAE2E3]">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">fingerprint</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#1A1516]">5 Metode Absensi</h4>
                        <p class="text-xs text-[#6B5E60]">RFID, QR, Geofencing, Face AI, & Manual.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-brand-bg border border-[#EAE2E3]">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">hub</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#1A1516]">All-in-One Ecosystem</h4>
                        <p class="text-xs text-[#6B5E60]">Terintegrasi penuh untuk semua peran di sekolah.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
        $demoAccounts = [
            ['role' => 'Operator / Admin Sekolah', 'email' => 'operator.demo@hadiryuk.id', 'icon' => 'admin_panel_settings'],
            ['role' => 'Kepala Sekolah', 'email' => 'kepala.demo@hadiryuk.id', 'icon' => 'school'],
            ['role' => 'Guru', 'email' => 'guru.demo@hadiryuk.id', 'icon' => 'person'],
            ['role' => 'Wali Kelas', 'email' => 'walikelas.demo@hadiryuk.id', 'icon' => 'groups'],
            ['role' => 'Orang Tua', 'email' => 'orangtua.demo@hadiryuk.id', 'icon' => 'family_restroom'],
            ['role' => 'Siswa', 'email' => 'siswa.demo@hadiryuk.id', 'icon' => 'backpack'],
        ];
    @endphp

    <!-- Demo Accounts -->
    <section id="akun-demo" class="py-20 bg-[#FBF8F6] border-y border-[#EAE2E3]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#EAE2E3] text-xs font-bold uppercase tracking-wider text-[#B81D24] mb-4">
                    <span class="material-symbols-outlined text-base">visibility</span>
                    Demo interaktif
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1A1516] mb-4">
                    Coba Akun Demo (Dewan Juri &amp; Pengunjung)
                </h2>
                <p class="text-brand-muted font-medium leading-relaxed">
                    Jelajahi pengalaman HadirYuk dari enam peran berbeda. Klik salin untuk menyalin kredensial lengkap,
                    atau masuk untuk mengisi kode sekolah dan email secara otomatis.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($demoAccounts as $account)
                    <article x-data="{ copied: false }"
                        class="bg-white border border-[#EAE2E3] rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-[#D9C8C8] transition-all">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-11 h-11 rounded-xl bg-[#FDF1F0] text-[#B81D24] flex items-center justify-center">
                                <span class="material-symbols-outlined">{{ $account['icon'] }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-brand-muted">Akun demo</p>
                                <h3 class="font-bold text-[#1A1516]">{{ $account['role'] }}</h3>
                            </div>
                        </div>

                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold text-brand-muted mb-1">Email</dt>
                                <dd class="font-semibold text-[#1A1516] break-all">{{ $account['email'] }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-xs font-semibold text-brand-muted mb-1">Kode sekolah</dt>
                                    <dd class="font-mono font-semibold text-[#1A1516]">JHIC2026</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-brand-muted mb-1">Password</dt>
                                    <dd class="font-mono font-semibold text-[#1A1516]">password123</dd>
                                </div>
                            </div>
                        </dl>

                        <div class="grid grid-cols-2 gap-3 mt-6">
                            <button type="button"
                                data-credentials="{{ "Kode sekolah: JHIC2026\nEmail: {$account['email']}\nPassword: password123" }}"
                                @click="if (navigator.clipboard?.writeText) { navigator.clipboard.writeText($el.dataset.credentials).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => window.prompt('Salin kredensial demo:', $el.dataset.credentials)); } else { window.prompt('Salin kredensial demo:', $el.dataset.credentials); }"
                                class="inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-[#EAE2E3] text-sm font-bold text-[#1A1516] hover:bg-[#F8F5F3] transition-colors">
                                <span class="material-symbols-outlined text-lg" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
                                <span x-text="copied ? 'Tersalin' : 'Salin Kredensial'">Salin Kredensial</span>
                            </button>
                            <a href="{{ route('login', ['school_code' => 'JHIC2026', 'email' => $account['email']]) }}"
                                class="inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-[#B81D24] text-sm font-bold text-white hover:bg-[#9B181E] transition-colors">
                                Masuk
                                <span class="material-symbols-outlined text-lg">arrow_forward</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <p class="mt-6 text-center text-xs text-brand-muted">
                Data demo ditujukan untuk eksplorasi fitur; perubahan pada akun dapat terlihat oleh pengguna demo lainnya.
            </p>
        </div>
    </section>

    <!-- Fitur Utama -->
    <section id="fitur" class="py-24 bg-brand-bg relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-2xl sm:text-4xl font-bold text-[#1A1516] tracking-tight mb-3">Fitur Dahsyat Untuk <span class="text-[#B81D24]">Sekolah Modern</span>
                </h2>
                <p class="text-brand-muted font-medium">Satu platform untuk menjawab semua kebutuhan operasional sekolah
                    Anda.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Pilar 1 -->
                <div
                    class="bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-8 transition-transform group-hover:scale-105">
                        <span class="material-symbols-outlined text-4xl">sensors</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#1A1516] mb-6">Presensi Fleksibel & Multi-Mode</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            IoT RFID Scanner (Tap Kartu Tanpa PC/Operator)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            QR Code Dynamic (Anti-Fraud & Cepat)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Geofencing GPS (Presensi via HP di Area Sekolah)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Face Recognition AI (Verifikasi Liveness)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Presensi Harian, Per-Mapel, & Kegiatan Ekstra
                        </li>
                    </ul>
                </div>

                <!-- Pilar 2 -->
                <div
                    class="bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-8 transition-transform group-hover:scale-105">
                        <span class="material-symbols-outlined text-4xl">dashboard</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#1A1516] mb-6">Dasbor Terintegrasi 6 Peran</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Operator: Kelola Data Guru, Siswa, Jam & Jadwal Sekolah
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Kepala Sekolah: Pantau Kehadiran, Analitik & Laporan Real-time
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Guru: Input Presensi KBM, Jurnal Mengajar & Agenda Belajar
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Wali Kelas: Rekap Absensi Kelas, Catatan Sikap & Pengumuman
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Orang Tua: Pantau Kehadiran & Agenda Belajar Anak via HP
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Siswa: Akses Jadwal, Tugas, Kartu Digital & Presensi Mandiri
                        </li>
                    </ul>
                </div>

                <!-- Pilar 3 -->
                <div
                    class="bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-8 transition-transform group-hover:scale-105">
                        <span class="material-symbols-outlined text-4xl">send_and_archive</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#1A1516] mb-6">Komunikasi Otomatis</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            WhatsApp Notification Ready (Integrasi Mudah)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Pengumuman Digital (Broadcast ke Ortu/Siswa)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Pengingat Barang Bawaan (Reminder Perlengkapan)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Manajemen Izin & Sakit Online (Digital Approval)
                        </li>
                    </ul>
                </div>

                <!-- Pilar 4 -->
                <div
                    class="bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-8 transition-transform group-hover:scale-105">
                        <span class="material-symbols-outlined text-4xl">badge</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#1A1516] mb-6">Cetak Kartu & Laporan Instan</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Generate Kartu Siswa Otomatis (QR/RFID Ready)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Cetak Kartu Massal (Layout Profesional & Keren)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Ekspor Laporan Kehadiran (Excel & PDF Terformat)
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Statistik & Grafik Kehadiran Bulanan Otomatis
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Komparasi -->
    <section id="keunggulan" class="py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-2xl sm:text-4xl font-bold text-[#1A1516] tracking-tight mb-3">Mengapa Harus <span class="text-[#B81D24]">HadirYuk?</span></h2>
                <p class="text-brand-muted font-medium">Bandingkan dan tentukan masa depan digital sekolah Anda.</p>
            </div>

            <div class="overflow-hidden rounded-[40px] border border-brand-border shadow-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="p-6 sm:p-8 text-lg font-black bg-[#F4EFEB] text-[#1A1516]">Fitur / Perbandingan
                            </th>
                            <th class="p-6 sm:p-8 text-lg font-black bg-[#F4EFEB] text-[#6B5E60]">Sistem Konvensional /
                                Lain</th>
                            <th class="p-6 sm:p-8 text-lg font-black bg-[#B81D24] text-white">HadirYuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-bold text-sm sm:text-base">
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Metode Absensi</td>
                            <td class="p-6 sm:p-8">Terbatas (Hanya 1-2)</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">5 Metode (Lengkap!)</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Biaya Server</td>
                            <td class="p-6 sm:p-8">Mahal (Jutaan/Bulan)</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Sangat Murah & Efisien</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Akses Orang Tua</td>
                            <td class="p-6 sm:p-8">Seringkali Tidak Ada</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Dasbor Khusus Ortu</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Notifikasi WA</td>
                            <td class="p-6 sm:p-8">Bayar Per Pesan / Mahal</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Tersedia & Terjangkau</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Kecepatan Tap RFID</td>
                            <td class="p-6 sm:p-8">Butuh PC & Operator</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Stand-alone IoT (Tap & Go!)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-24 bg-brand-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-2xl sm:text-4xl font-bold text-[#1A1516] tracking-tight mb-8">Paling Sering Ditanyakan</h2>
            </div>

            <div class="space-y-4" x-data="{ active: null }">
                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 0 ? null : 0"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Apakah benar harga HadirYuk termurah di Indonesia?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 0 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 0" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        Ya, benar. Kami merancang HadirYuk dengan arsitektur cloud yang sangat efisien sehingga bisa
                        menekan biaya server seminimal mungkin. Fokus kami adalah membantu sekolah mendigitalisasi
                        operasionalnya tanpa beban biaya bulanan yang mencekik.
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 1 ? null : 1"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Apakah kami harus membeli alat RFID khusus?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 1 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 1" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        HadirYuk mendukung berbagai metode. Jika sekolah ingin menggunakan RFID, kami menyediakan skema
                        IoT yang sangat murah. Namun jika sekolah ingin GRATIS tanpa alat, Anda bisa menggunakan metode
                        QR Code atau Geofencing GPS yang hanya membutuhkan smartphone.
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 2 ? null : 2"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Bagaimana jika guru atau admin kami gaptek?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 2 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 2" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        HadirYuk didesain dengan antarmuka yang sangat modern dan user-friendly. Kami mengadopsi prinsip
                        "Sekali Lihat Langsung Paham". Selain itu, tim kami menyediakan dokumentasi lengkap dan
                        konsultasi via WhatsApp jika dibutuhkan.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-24 bg-[#1A1516] relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-grid-pattern"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h2 class="text-2xl sm:text-4xl font-bold text-white tracking-tight leading-snug mb-8">
                Mulai Digitalisasi Sekolah Anda <br> Bersama <span class="text-[#FF4D4D]">HadirYuk</span> Hari Ini.
            </h2>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mt-12">
                <a href="/register?role=owner"
                    class="w-full sm:w-auto px-8 py-4 bg-[#B81D24] text-white hover:bg-[#9B181E] rounded-xl font-semibold shadow-md active:scale-95 transition-all text-center">
                    Daftar Sekolah & Mulai Sekarang
                </a>
            </div>
            <p class="mt-8 text-xs text-zinc-400 tracking-wider uppercase">Tanpa biaya pendaftaran awal •
                Batalkan kapan saja</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-white border-t border-[#EAE2E3]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="HadirYuk" class="h-8 w-auto">
                    <span class="font-black text-xl tracking-tighter">Hadir<span
                            class="text-brand-primary">Yuk</span></span>
                </div>
                <div class="flex gap-8">
                    <a href="#" class="text-[#6B5E60] hover:text-[#B81D24] text-sm font-medium transition-colors">Tentang Kami</a>
                    <a href="#" class="text-[#6B5E60] hover:text-[#B81D24] text-sm font-medium transition-colors">Syarat & Ketentuan</a>
                    <a href="#" class="text-[#6B5E60] hover:text-[#B81D24] text-sm font-medium transition-colors">Kebijakan Privasi</a>
                </div>
                <p class="text-xs font-medium text-[#6B5E60]">© 2026 Almas Alfatih, CTO PT Thortech. Hak Cipta
                    Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WA -->
    <a href="https://wa.me/6281345557567?text=Halo%20HadirYuk%2C%20saya%20ingin%20konsultasi%20sistem%20presensi%20sekolah"
        target="_blank" rel="noopener noreferrer"
        class="fixed bottom-6 right-6 z-[60] bg-emerald-600 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl hover:bg-emerald-700 hover:scale-110 active:scale-95 transition-all group"
        aria-label="Konsultasi via WhatsApp">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
        <span
            class="absolute right-20 bg-emerald-600 px-4 py-2 rounded-xl text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl">
            Tanya Admin via WhatsApp
        </span>
    </a>

</body>

</html>