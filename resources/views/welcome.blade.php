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
                        'brand-primary': '#B81D24',
                        'brand-secondary': '#10B981',
                        'brand-dark': '#1A1516',
                        'brand-muted': '#6B5E60',
                        'brand-bg': '#FBF9F9',
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

                    <div class="flex items-center gap-4 pl-4 border-l">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-brand-primary">Dasbor Saya</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-sm font-bold hover:text-brand-primary transition-colors">Masuk</a>
                            <a href="https://wa.me/6281234567890" target="_blank"
                                class="bg-brand-secondary text-white px-5 py-2.5 rounded-full text-sm font-bold shadow-sm hover:scale-105 transition-transform flex items-center gap-2">
                                <i class="fa-brands fa-whatsapp"></i> Konsultasi WA
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
            <a href="{{ route('login') }}" class="block font-bold text-brand-primary">Masuk ke Aplikasi</a>
            <a href="https://wa.me/6281234567890"
                class="block bg-brand-secondary text-white text-center py-3 rounded-xl font-bold">Konsultasi
                WhatsApp</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-52 lg:pb-40 overflow-hidden">
        <div class="absolute inset-0 bg-grid-pattern opacity-40"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs sm:text-sm font-bold mb-8 animate-bounce">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-primary"></span>
                    </span>
                    🚀 Platform Manajemen & Presensi Sekolah No. 1 di Indonesia
                </div>

                <h1
                    class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tighter leading-[1.1] mb-8 text-brand-dark">
                    Sistem Sekolah Lengkap & Canggih. <br>
                    <span class="text-brand-primary">Harga Termurah</span> se-Indonesia.
                </h1>

                <p class="text-lg sm:text-xl text-brand-muted font-medium mb-10 leading-relaxed max-w-3xl mx-auto">
                    Solusi presensi 5 metode, dasbor multi-role (Kepsek, Guru, Orang Tua & Siswa), cetak kartu siswa,
                    hingga notifikasi WhatsApp otomatis tanpa biaya server mahal.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/register?role=owner"
                        class="w-full sm:w-auto px-10 py-5 bg-brand-primary text-white rounded-2xl font-black text-lg shadow-xl shadow-brand-primary/20 hover:scale-105 active:scale-95 transition-all">
                        Daftarkan Sekolah Sekarang
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank"
                        class="w-full sm:w-auto px-10 py-5 bg-brand-secondary text-white rounded-2xl font-black text-lg shadow-xl shadow-brand-secondary/20 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <i class="fa-brands fa-whatsapp text-2xl"></i> Konsultasi via WhatsApp
                    </a>
                </div>

                <div
                    class="mt-16 flex flex-wrap justify-center items-center gap-8 opacity-50 grayscale hover:grayscale-0 transition-all">
                    <p class="w-full text-center text-xs font-bold uppercase tracking-widest mb-2">Dipercaya oleh
                        berbagai jenjang pendidikan</p>
                    <span class="font-black text-xl italic">PAUD/TK</span>
                    <span class="font-black text-xl italic">SD/MI</span>
                    <span class="font-black text-xl italic">SMP/MTs</span>
                    <span class="font-black text-xl italic">SMA/SMK/MA</span>
                    <span class="font-black text-xl italic">PONDOK PESANTREN</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Grid -->
    <section class="py-12 bg-white border-y">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-center gap-5 p-6 rounded-3xl bg-brand-bg">
                    <div
                        class="w-16 h-16 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-4xl font-bold">payments</span>
                    </div>
                    <div>
                        <h4 class="font-black text-lg">Harga Termurah</h4>
                        <p class="text-sm text-brand-muted font-medium">Investasi sistem sekolah paling efisien di
                            Indonesia.</p>
                    </div>
                </div>
                <div class="flex items-center gap-5 p-6 rounded-3xl bg-brand-bg">
                    <div
                        class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-4xl font-bold">fingerprint</span>
                    </div>
                    <div>
                        <h4 class="font-black text-lg">5 Metode Absensi</h4>
                        <p class="text-sm text-brand-muted font-medium">RFID, QR, Geofencing, Face AI, & Manual.</p>
                    </div>
                </div>
                <div class="flex items-center gap-5 p-6 rounded-3xl bg-brand-bg">
                    <div
                        class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-4xl font-bold">hub</span>
                    </div>
                    <div>
                        <h4 class="font-black text-lg">All-in-One Ecosystem</h4>
                        <p class="text-sm text-brand-muted font-medium">Terintegrasi penuh untuk semua peran di sekolah.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Utama -->
    <section id="fitur" class="py-24 bg-brand-bg relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-3xl sm:text-5xl font-black tracking-tighter mb-4">Fitur Dahsyat Untuk Sekolah Modern
                </h2>
                <p class="text-brand-muted font-medium">Satu platform untuk menjawab semua kebutuhan operasional sekolah
                    Anda.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Pilar 1 -->
                <div
                    class="bg-white p-8 sm:p-12 rounded-[40px] shadow-sm border border-brand-border/50 group hover:shadow-xl transition-all duration-500">
                    <div
                        class="w-20 h-20 rounded-3xl bg-brand-primary text-white flex items-center justify-center mb-8 shadow-lg shadow-brand-primary/20 group-hover:rotate-6 transition-transform">
                        <span class="material-symbols-outlined text-4xl">sensors</span>
                    </div>
                    <h3 class="text-3xl font-black mb-6">Presensi Fleksibel & Multi-Mode</h3>
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
                    class="bg-white p-8 sm:p-12 rounded-[40px] shadow-sm border border-brand-border/50 group hover:shadow-xl transition-all duration-500">
                    <div
                        class="w-20 h-20 rounded-3xl bg-blue-600 text-white flex items-center justify-center mb-8 shadow-lg shadow-blue-600/20 group-hover:rotate-6 transition-transform">
                        <span class="material-symbols-outlined text-4xl">dashboard</span>
                    </div>
                    <h3 class="text-3xl font-black mb-6">Dasbor Terintegrasi Multi-Role</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Kepala Sekolah: Pantau Kehadiran & Laporan Real-time
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Operator: Kelola Data Guru, Siswa, & Jadwal
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Guru/Wali Kelas: Kelola Absensi KBM & Pengumuman
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Orang Tua: Cek Kehadiran & Agenda Anak via HP
                        </li>
                        <li class="flex items-start gap-3 font-bold text-brand-dark">
                            <span class="material-symbols-outlined text-brand-secondary">check_circle</span>
                            Siswa: Akses Jadwal, Tugas, & Presensi Mandiri
                        </li>
                    </ul>
                </div>

                <!-- Pilar 3 -->
                <div
                    class="bg-white p-8 sm:p-12 rounded-[40px] shadow-sm border border-brand-border/50 group hover:shadow-xl transition-all duration-500">
                    <div
                        class="w-20 h-20 rounded-3xl bg-brand-secondary text-white flex items-center justify-center mb-8 shadow-lg shadow-brand-secondary/20 group-hover:rotate-6 transition-transform">
                        <span class="material-symbols-outlined text-4xl">send_and_archive</span>
                    </div>
                    <h3 class="text-3xl font-black mb-6">Komunikasi Otomatis</h3>
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
                    class="bg-white p-8 sm:p-12 rounded-[40px] shadow-sm border border-brand-border/50 group hover:shadow-xl transition-all duration-500">
                    <div
                        class="w-20 h-20 rounded-3xl bg-amber-500 text-white flex items-center justify-center mb-8 shadow-lg shadow-amber-500/20 group-hover:rotate-6 transition-transform">
                        <span class="material-symbols-outlined text-4xl">badge</span>
                    </div>
                    <h3 class="text-3xl font-black mb-6">Cetak Kartu & Laporan Instan</h3>
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
                <h2 class="text-3xl sm:text-5xl font-black tracking-tighter mb-4">Mengapa Harus HadirYuk?</h2>
                <p class="text-brand-muted font-medium">Bandingkan dan tentukan masa depan digital sekolah Anda.</p>
            </div>

            <div class="overflow-hidden rounded-[40px] border border-brand-border shadow-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-dark text-white">
                            <th class="p-6 sm:p-8 text-xl font-black">Aspek / Fitur</th>
                            <th class="p-6 sm:p-8 text-xl font-black">Sistem Lain / Manual</th>
                            <th class="p-6 sm:p-8 text-xl font-black bg-brand-primary">HadirYuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-bold text-sm sm:text-base">
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Metode Absensi</td>
                            <td class="p-6 sm:p-8">Terbatas (Hanya 1-2)</td>
                            <td class="p-6 sm:p-8 text-brand-primary italic">5 Metode (Lengkap!)</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Biaya Server</td>
                            <td class="p-6 sm:p-8">Mahal (Jutaan/Bulan)</td>
                            <td class="p-6 sm:p-8 text-brand-primary italic">Sangat Murah & Efisien</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Akses Orang Tua</td>
                            <td class="p-6 sm:p-8">Seringkali Tidak Ada</td>
                            <td class="p-6 sm:p-8 text-brand-primary italic">Dasbor Khusus Ortu</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Notifikasi WA</td>
                            <td class="p-6 sm:p-8">Bayar Per Pesan / Mahal</td>
                            <td class="p-6 sm:p-8 text-brand-primary italic">Tersedia & Terjangkau</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Kecepatan Tap RFID</td>
                            <td class="p-6 sm:p-8">Butuh PC & Operator</td>
                            <td class="p-6 sm:p-8 text-brand-primary italic">Stand-alone IoT (Tap & Go!)</td>
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
                <h2 class="text-3xl sm:text-5xl font-black tracking-tighter mb-4">Paling Sering Ditanyakan</h2>
            </div>

            <div class="space-y-4" x-data="{ active: null }">
                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden">
                    <button @click="active = active === 0 ? null : 0"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-black">Apakah benar harga HadirYuk termurah di Indonesia?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 0 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 0" x-cloak class="p-6 pt-0 text-brand-muted font-medium border-t">
                        Ya, benar. Kami merancang HadirYuk dengan arsitektur cloud yang sangat efisien sehingga bisa
                        menekan biaya server seminimal mungkin. Fokus kami adalah membantu sekolah mendigitalisasi
                        operasionalnya tanpa beban biaya bulanan yang mencekik.
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden">
                    <button @click="active = active === 1 ? null : 1"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-black">Apakah kami harus membeli alat RFID khusus?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 1 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 1" x-cloak class="p-6 pt-0 text-brand-muted font-medium border-t">
                        HadirYuk mendukung berbagai metode. Jika sekolah ingin menggunakan RFID, kami menyediakan skema
                        IoT yang sangat murah. Namun jika sekolah ingin GRATIS tanpa alat, Anda bisa menggunakan metode
                        QR Code atau Geofencing GPS yang hanya membutuhkan smartphone.
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-brand-border overflow-hidden">
                    <button @click="active = active === 2 ? null : 2"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-black">Bagaimana jika guru atau admin kami gaptek?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 2 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 2" x-cloak class="p-6 pt-0 text-brand-muted font-medium border-t">
                        HadirYuk didesain dengan antarmuka yang sangat modern dan user-friendly. Kami mengadopsi prinsip
                        "Sekali Lihat Langsung Paham". Selain itu, tim kami menyediakan dokumentasi lengkap dan
                        konsultasi via WhatsApp jika dibutuhkan.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-24 bg-brand-dark relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-grid-pattern"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h2 class="text-4xl sm:text-6xl font-black text-white tracking-tighter mb-8 leading-tight">
                Mulai Digitalisasi Sekolah Anda <br> Bersama <span class="text-brand-primary">HadirYuk</span> Hari Ini.
            </h2>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mt-12">
                <a href="/register?role=owner"
                    class="w-full sm:w-auto px-12 py-6 bg-brand-primary text-white rounded-3xl font-black text-xl hover:scale-105 active:scale-95 transition-all shadow-2xl shadow-brand-primary/40">
                    Daftar Sekolah & Mulai Sekarang
                </a>
            </div>
            <p class="mt-8 text-gray-400 font-bold uppercase tracking-widest text-xs">Tanpa biaya pendaftaran awal •
                Batalkan kapan saja</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-white border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="HadirYuk" class="h-8 w-auto">
                    <span class="font-black text-xl tracking-tighter">Hadir<span
                            class="text-brand-primary">Yuk</span></span>
                </div>
                <div class="flex gap-8 text-sm font-bold text-brand-muted">
                    <a href="#" class="hover:text-brand-primary">Tentang Kami</a>
                    <a href="#" class="hover:text-brand-primary">Syarat & Ketentuan</a>
                    <a href="#" class="hover:text-brand-primary">Kebijakan Privasi</a>
                </div>
                <p class="text-xs font-bold text-brand-muted">© 2026 Almas Alfatih, CTO PT Thortech. Hak Cipta
                    Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WA -->
    <a href="https://wa.me/6281234567890" target="_blank"
        class="fixed bottom-6 right-6 z-[60] bg-brand-secondary text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 active:scale-95 transition-all group">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
        <span
            class="absolute right-20 bg-brand-secondary px-4 py-2 rounded-xl text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl">
            Tanya Admin via WhatsApp
        </span>
    </a>

</body>

</html>