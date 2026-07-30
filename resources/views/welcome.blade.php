<!DOCTYPE html>
<html lang="id" class="scroll-smooth w-full max-w-full overflow-x-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HadirSekolah - Ekosistem Presensi Edukasi Pintar</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='85' font-style='italic' font-weight='900' fill='%23b91c1c' font-family='sans-serif'>H</text></svg>">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

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
                        'brand-bg': '#FBF9F9',
                        'brand-surface': '#FFFFFF',
                        'brand-border': '#EAE2E3',
                        'brand-primary': '#B81D24',
                        'brand-text-main': '#1A1516',
                        'brand-text-muted': '#6B5E60'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .bg-grid-pattern {
            background-image: radial-gradient(#EAE2E3 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(234, 226, 227, 0.5);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-brand-bg text-brand-text-main antialiased selection:bg-brand-primary selection:text-white w-full max-w-full overflow-x-hidden">

    <!-- Navbar -->
    <nav x-data="{ mobileMenuOpen: false }"
        class="fixed w-full z-50 transition-all duration-300 bg-brand-bg/90 backdrop-blur-md shadow-sm border-b border-brand-border/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3 sm:py-4">
                <div class="flex items-center gap-2 min-w-0">
                    <img src="/hadiryuklogo1-4.png" alt="Logo HadirYuk"
                        class="h-7 sm:h-9 w-auto object-contain shrink-0">
                    <span class="font-bold text-xl sm:text-2xl tracking-tight text-brand-text-main truncate">Hadir<span
                            class="text-brand-primary">Sekolah</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#fitur"
                        class="text-brand-text-muted hover:text-brand-primary font-medium transition-colors">Fitur</a>
                    <a href="#arsitektur"
                        class="text-brand-text-muted hover:text-brand-primary font-medium transition-colors">Arsitektur</a>
                    <a href="#rbac"
                        class="text-brand-text-muted hover:text-brand-primary font-medium transition-colors">Akses</a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="font-medium text-brand-text-main hover:text-brand-primary transition-colors">Dasbor</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="border border-red-700 text-red-700 hover:bg-red-700 hover:text-white px-4 py-2 rounded-xl font-medium transition duration-200">Log
                                in</a>
                        @endauth
                    @endif
                </div>

                <!-- Mobile Menu Button & Quick Login -->
                <div class="flex md:hidden items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="text-xs font-bold bg-brand-primary text-white px-3 py-1.5 rounded-lg shadow-sm">Dasbor</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-xs font-bold border border-brand-primary text-brand-primary px-3 py-1.5 rounded-lg">Log
                                in</a>
                        @endauth
                    @endif

                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-brand-text-main hover:text-brand-primary focus:outline-none p-1"
                        aria-label="Toggle menu">
                        <i class="fa-solid fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                        <i class="fa-solid fa-xmark text-xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="md:hidden bg-white border-b border-brand-border shadow-lg overflow-hidden" x-cloak>
            <div class="px-4 pt-2 pb-6 space-y-1">
                <a href="#fitur" @click="mobileMenuOpen = false"
                    class="block px-3 py-3 rounded-lg text-base font-semibold text-brand-text-muted hover:text-brand-primary hover:bg-brand-primary/5 transition-colors">
                    <i class="fa-solid fa-star w-6 text-brand-primary/50"></i> Fitur
                </a>
                <a href="#arsitektur" @click="mobileMenuOpen = false"
                    class="block px-3 py-3 rounded-lg text-base font-semibold text-brand-text-muted hover:text-brand-primary hover:bg-brand-primary/5 transition-colors">
                    <i class="fa-solid fa-layer-group w-6 text-brand-primary/50"></i> Arsitektur
                </a>
                <a href="#rbac" @click="mobileMenuOpen = false"
                    class="block px-3 py-3 rounded-lg text-base font-semibold text-brand-text-muted hover:text-brand-primary hover:bg-brand-primary/5 transition-colors">
                    <i class="fa-solid fa-shield-halved w-6 text-brand-primary/50"></i> Akses
                </a>

                <div class="pt-4 mt-4 border-t border-brand-border">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl font-bold bg-brand-primary text-white shadow-md">
                                <i class="fa-solid fa-gauge-high"></i> Ke Dasbor
                            </a>
                        @else
                            <div class="grid grid-cols-1 gap-3">
                                <a href="{{ route('login') }}"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl font-bold border-2 border-brand-primary text-brand-primary">
                                    <i class="fa-solid fa-right-to-bracket"></i> Log in
                                </a>
                                <a href="/register?role=owner"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl font-bold bg-brand-primary text-white shadow-md">
                                    <i class="fa-solid fa-rocket"></i> Daftar Sekarang
                                </a>
                            </div>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden w-full">
        <div class="absolute inset-0 bg-grid-pattern opacity-50"></div>
        <div
            class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-brand-primary/5 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-brand-primary/10 blur-3xl pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center flex flex-col items-center">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-brand-border text-xs sm:text-sm font-semibold text-brand-primary mb-6 sm:mb-8 shadow-sm flex-wrap justify-center max-w-full break-words">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-primary"></span>
                </span>
                <span class="whitespace-normal">Versi 2.0 Kini Tersedia</span>
            </div>

            <h1
                class="text-xl sm:text-3xl md:text-5xl lg:text-6xl font-black text-brand-text-main leading-snug mb-6 w-full break-words">
                Ekosistem Absensi Pintar,<br class="hidden md:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-primary to-[#8A151A]">Anti-Titip
                    Absen & Real-Time</span>
            </h1>

            <p
                class="mt-4 max-w-2xl text-sm sm:text-base md:text-lg text-brand-text-muted mx-auto mb-8 sm:mb-10 font-medium leading-relaxed w-full">
                Platform presensi digital modern untuk siswa, guru, dan staf sekolah. Dirancang khusus untuk
                meningkatkan kedisiplinan belajar mengajar tanpa beban infrastruktur rumit.
                <br class="hidden md:block" />
                <span
                    class="inline-block mt-3 font-semibold bg-brand-primary/10 text-brand-primary px-3 py-1.5 rounded-lg text-xs sm:text-sm w-auto max-w-full break-words whitespace-normal">⚡
                    Tanpa Perlu Server Sendiri</span>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center w-full">
                <a href="/register?role=owner"
                    class="w-full sm:w-auto px-6 sm:px-8 py-4 rounded-xl font-bold text-sm sm:text-lg bg-brand-primary text-white hover:bg-brand-primary/90 active:scale-95 transition-all shadow-md flex items-center justify-center gap-2 text-center whitespace-normal break-words">
                    <i class="fa-solid fa-rocket shrink-0"></i> Daftarkan Sekolah Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- 4 Pilar Anti-Kecurangan -->
    <section id="fitur"
        class="py-16 sm:py-20 bg-brand-surface relative border-y border-brand-border/50 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 break-words">4 Pilar Utama Pencegah
                    Kecurangan</h2>
                <p class="text-brand-text-muted text-sm sm:text-base md:text-lg max-w-2xl mx-auto break-words">
                    Memastikan data kehadiran siswa dan guru 100% valid, akurat, serta transparan untuk pihak sekolah
                    dan wali murid.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 w-full">
                <!-- Pilar 1 -->
                <div
                    class="group bg-brand-bg p-6 sm:p-8 rounded-2xl border border-brand-border hover:border-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 w-full max-w-full">
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 bg-brand-surface rounded-xl flex items-center justify-center border border-brand-border mb-5 sm:mb-6 group-hover:scale-110 group-hover:bg-brand-primary transition-all duration-300 shadow-sm shrink-0">
                        <i
                            class="fa-solid fa-map-location-dot text-xl sm:text-2xl text-brand-primary group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3 text-brand-text-main break-words">Validasi
                        Geofencing Presisi</h3>
                    <p class="text-brand-text-muted leading-relaxed text-xs sm:text-sm break-words">Membatasi radius
                        lokasi presensi secara ketat di area gerbang atau lingkungan fisik sekolah dengan proteksi
                        Anti-Fake GPS.</p>
                </div>

                <!-- Pilar 2 -->
                <div
                    class="group bg-brand-bg p-6 sm:p-8 rounded-2xl border border-brand-border hover:border-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 w-full max-w-full">
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 bg-brand-surface rounded-xl flex items-center justify-center border border-brand-border mb-5 sm:mb-6 group-hover:scale-110 group-hover:bg-brand-primary transition-all duration-300 shadow-sm shrink-0">
                        <i
                            class="fa-solid fa-user-check text-brand-primary text-xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3 text-brand-text-main break-words">AI Liveness
                        Detection</h3>
                    <p class="text-brand-text-muted leading-relaxed text-xs sm:text-sm break-words">Pemindaian wajah
                        interaktif untuk memastikan presensi dilakukan oleh siswa/guru bersangkutan (bukan foto,
                        cetakan, atau rekaman video).</p>
                </div>

                <!-- Pilar 3 -->
                <div
                    class="group bg-brand-bg p-6 sm:p-8 rounded-2xl border border-brand-border hover:border-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 w-full max-w-full">
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 bg-brand-surface rounded-xl flex items-center justify-center border border-brand-border mb-5 sm:mb-6 group-hover:scale-110 group-hover:bg-brand-primary transition-all duration-300 shadow-sm shrink-0">
                        <i
                            class="fa-solid fa-wifi text-xl sm:text-2xl text-brand-primary group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3 text-brand-text-main break-words">Wi-Fi Network
                        Locking</h3>
                    <p class="text-brand-text-muted leading-relaxed text-xs sm:text-sm break-words">Sistem mengunci
                        jalur presensi agar hanya bisa diakses ketika perangkat terhubung ke Wi-Fi resmi lingkungan
                        sekolah.</p>
                </div>

                <!-- Pilar 4 -->
                <div
                    class="group bg-brand-bg p-6 sm:p-8 rounded-2xl border border-brand-border hover:border-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 w-full max-w-full">
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 bg-brand-surface rounded-xl flex items-center justify-center border border-brand-border mb-5 sm:mb-6 group-hover:scale-110 group-hover:bg-brand-primary transition-all duration-300 shadow-sm shrink-0">
                        <i
                            class="fa-brands fa-nfc-symbol text-xl sm:text-2xl text-brand-primary group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3 text-brand-text-main break-words">Integrasi IoT
                        & Mesin Fisik</h3>
                    <p class="text-brand-text-muted leading-relaxed text-xs sm:text-sm break-words">Dukungan langsung
                        untuk mesin absensi fisik (RFID/ESP32) yang terhubung otomatis ke dalam satu dashboard pusat.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Arsitektur Modular -->
    <section id="arsitektur" class="py-16 sm:py-24 bg-brand-bg relative overflow-hidden w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="flex flex-col lg:flex-row items-center gap-12 sm:gap-16 w-full">
                <div class="w-full lg:w-1/2 text-center lg:text-left min-w-0">
                    <div
                        class="inline-block px-4 py-1.5 rounded-full bg-brand-primary/10 text-brand-primary text-xs sm:text-sm font-bold mb-4 sm:mb-6 tracking-wide break-words whitespace-normal">
                        ARSITEKTUR MODULAR</div>
                    <h2 class="text-xl sm:text-3xl md:text-4xl font-bold mb-4 sm:mb-6 break-words">Solusi Fleksibel
                        untuk Semua Jenjang Sekolah & Yayasan</h2>
                    <p class="text-brand-text-muted text-sm sm:text-base md:text-lg mb-8 leading-relaxed break-words">
                        Dirancang kokoh untuk melayani kebutuhan presensi harian dari jenjang SD, SMP, SMA/SMK, hingga
                        Kompleks Yayasan Pendidikan.
                    </p>

                    <div class="space-y-4 sm:space-y-6 text-left w-full min-w-0">
                        <!-- ZERO INFRASTRUKTUR HIGHLIGHT CARD -->
                        <div
                            class="relative bg-white p-5 sm:p-6 rounded-2xl border-2 border-brand-primary/30 shadow-lg group hover:border-brand-primary/60 transition-colors mt-8 sm:mt-0 w-full min-w-0 break-words">
                            <div
                                class="absolute -top-4 sm:-top-4 left-4 sm:left-auto sm:-right-3 bg-brand-primary text-white text-[10px] sm:text-xs font-bold px-3 py-1 rounded-full shadow-md animate-pulse whitespace-normal break-words text-center max-w-[80%]">
                                ★ ZERO INFRASTRUKTUR
                            </div>
                            <div class="flex flex-col sm:flex-row gap-4 mt-4 sm:mt-0 w-full min-w-0">
                                <div
                                    class="w-12 h-12 shrink-0 rounded-full bg-brand-surface border border-brand-border flex items-center justify-center shadow-sm">
                                    <i class="fa-solid fa-building text-brand-primary text-lg sm:text-xl"></i>
                                </div>
                                <div class="w-full min-w-0">
                                    <h4 class="text-lg sm:text-xl font-bold mb-2 break-words">Standalone SaaS Sekolah
                                    </h4>
                                    <p class="text-brand-text-muted text-xs sm:text-sm leading-relaxed break-words">
                                        Setiap sekolah mendapat Kode Instansi unik, dashboard terisolasi, dan
                                        penyimpanan data aman. Langsung siap pakai tanpa perlu membeli server fisik
                                        lokal yang mahal.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 p-5 sm:p-6 w-full min-w-0">
                            <div
                                class="w-12 h-12 shrink-0 rounded-full bg-white border border-brand-border flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-puzzle-piece text-brand-primary text-lg sm:text-xl"></i>
                            </div>
                            <div class="w-full min-w-0">
                                <h4 class="text-lg sm:text-xl font-bold mb-2 break-words">Modul Integrasi LMS & Sistem
                                    Sekolah</h4>
                                <p class="text-brand-text-muted text-xs sm:text-sm leading-relaxed break-words">Desain
                                    Plug-and-Play yang mudah dihubungkan ke sistem LMS, e-Rapor, maupun database
                                    internal sekolah yang sudah ada.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/2 hidden sm:block">
                    <div class="glass-card rounded-2xl p-2 shadow-2xl relative max-w-md mx-auto w-full">
                        <div
                            class="absolute inset-0 bg-gradient-to-tr from-brand-primary/10 to-transparent rounded-2xl pointer-events-none">
                        </div>
                        <div
                            class="bg-white rounded-xl border border-brand-border p-6 sm:p-8 relative overflow-hidden w-full">
                            <!-- Visualisasi Arsitektur -->
                            <div class="flex justify-center mb-6 sm:mb-8 relative z-10 w-full">
                                <div class="w-full max-w-sm">
                                    <div
                                        class="bg-brand-bg rounded-lg p-3 sm:p-4 border border-brand-border text-center mb-4 relative z-10 font-bold text-xs sm:text-sm break-words whitespace-normal">
                                        LMS / HRIS Anda</div>

                                    <div class="flex justify-center -my-2 relative z-0">
                                        <div
                                            class="w-1 h-10 sm:h-12 bg-gradient-to-b from-brand-border to-brand-primary border-l border-dashed border-brand-primary">
                                        </div>
                                        <div
                                            class="absolute top-1/2 -translate-y-1/2 bg-brand-surface border border-brand-border rounded-full p-1 shadow-sm">
                                            <i
                                                class="fa-solid fa-arrows-up-down text-brand-primary text-[10px] sm:text-xs"></i>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-brand-primary text-white rounded-lg p-4 sm:p-5 shadow-lg text-center relative z-10 transform hover:scale-105 transition-transform w-full break-words">
                                        <div class="flex flex-wrap items-center justify-center gap-2 mb-1 sm:mb-2">
                                            <i class="fa-solid fa-fingerprint text-lg sm:text-xl shrink-0"></i>
                                            <span
                                                class="font-bold text-base sm:text-lg break-words whitespace-normal">HadirSekolah
                                                Core</span>
                                        </div>
                                        <div class="text-[10px] sm:text-xs text-white/80 break-words whitespace-normal">
                                            API Gateway & Webhook</div>
                                    </div>

                                    <div class="flex justify-between -my-2 relative z-0 px-6 sm:px-8">
                                        <div
                                            class="w-1 h-10 sm:h-12 bg-gradient-to-t from-brand-border to-brand-primary">
                                        </div>
                                        <div
                                            class="w-1 h-10 sm:h-12 bg-gradient-to-t from-brand-border to-brand-primary">
                                        </div>
                                        <div
                                            class="w-1 h-10 sm:h-12 bg-gradient-to-t from-brand-border to-brand-primary">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 sm:gap-3 relative z-10 w-full">
                                        <div
                                            class="bg-brand-surface border border-brand-border rounded-lg p-2 sm:p-3 text-center shadow-sm w-full min-w-0">
                                            <i class="fa-solid fa-mobile-screen text-brand-text-muted mb-1"></i>
                                            <div class="text-[9px] sm:text-[10px] font-bold break-words">Mobile</div>
                                        </div>
                                        <div
                                            class="bg-brand-surface border border-brand-border rounded-lg p-2 sm:p-3 text-center shadow-sm w-full min-w-0">
                                            <i class="fa-brands fa-nfc-symbol text-brand-text-muted mb-1"></i>
                                            <div class="text-[9px] sm:text-[10px] font-bold break-words">RFID/IoT</div>
                                        </div>
                                        <div
                                            class="bg-brand-surface border border-brand-border rounded-lg p-2 sm:p-3 text-center shadow-sm w-full min-w-0">
                                            <i class="fa-solid fa-desktop text-brand-text-muted mb-1"></i>
                                            <div class="text-[9px] sm:text-[10px] font-bold break-words">Web</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic RBAC -->
    <section id="rbac" class="py-16 sm:py-24 bg-brand-surface border-y border-brand-border/50 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 break-words">Hak Akses 4 Tingkat
                    Terintegrasi</h2>
                <p class="text-brand-text-muted text-sm sm:text-base md:text-lg max-w-2xl mx-auto break-words">Pembagian
                    peran yang jelas untuk mendukung transparansi dan efisiensi manajemen operasional sekolah.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 w-full">
                <!-- Role 1 -->
                <div
                    class="bg-brand-bg rounded-2xl p-5 sm:p-6 border border-brand-border relative overflow-hidden group w-full max-w-full">
                    <div
                        class="absolute top-0 right-0 w-20 h-20 sm:w-24 sm:h-24 bg-brand-primary/5 rounded-bl-[100px] -z-10 group-hover:bg-brand-primary/10 transition-colors pointer-events-none">
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-full flex items-center justify-center border border-brand-border shadow-sm mb-3 sm:mb-4 shrink-0">
                        <i class="fa-solid fa-crown text-brand-primary text-base sm:text-lg"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-brand-text-main mb-2 break-words whitespace-normal">
                        Kepala Sekolah & Yayasan</h3>
                    <p class="text-xs sm:text-sm text-brand-text-muted leading-relaxed break-words">Akses Dasbor
                        Analitik Eksekutif, laporan tingkat kedisiplinan sekolah, dan kontrol kebijakan utama.</p>
                </div>

                <!-- Role 2 -->
                <div
                    class="bg-brand-bg rounded-2xl p-5 sm:p-6 border border-brand-border relative overflow-hidden group w-full max-w-full">
                    <div
                        class="absolute top-0 right-0 w-20 h-20 sm:w-24 sm:h-24 bg-brand-primary/5 rounded-bl-[100px] -z-10 group-hover:bg-brand-primary/10 transition-colors pointer-events-none">
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-full flex items-center justify-center border border-brand-border shadow-sm mb-3 sm:mb-4 shrink-0">
                        <i class="fa-solid fa-shield-halved text-brand-primary text-base sm:text-lg"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-brand-text-main mb-2 break-words whitespace-normal">
                        Operator Dapodik & Tata Usaha</h3>
                    <p class="text-xs sm:text-sm text-brand-text-muted leading-relaxed break-words">Manajemen data
                        siswa, guru, rombel/kelas, pengaturan jadwal KBM, dan verifikasi permohonan izin/sakit.</p>
                </div>

                <!-- Role 3 -->
                <div
                    class="bg-brand-bg rounded-2xl p-5 sm:p-6 border border-brand-border relative overflow-hidden group w-full max-w-full">
                    <div
                        class="absolute top-0 right-0 w-20 h-20 sm:w-24 sm:h-24 bg-brand-primary/5 rounded-bl-[100px] -z-10 group-hover:bg-brand-primary/10 transition-colors pointer-events-none">
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-full flex items-center justify-center border border-brand-border shadow-sm mb-3 sm:mb-4 shrink-0">
                        <i class="fa-solid fa-user-tie text-brand-primary text-base sm:text-lg"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-brand-text-main mb-2 break-words whitespace-normal">
                        Wali Kelas & Guru Pengajar</h3>
                    <p class="text-xs sm:text-sm text-brand-text-muted leading-relaxed break-words">Monitoring kehadiran
                        siswa binaan secara langsung, validasi presensi jam pelajaran (KBM), dan rekapitulasinya.</p>
                </div>

                <!-- Role 4 -->
                <div
                    class="bg-brand-bg rounded-2xl p-5 sm:p-6 border border-brand-border relative overflow-hidden group w-full max-w-full">
                    <div
                        class="absolute top-0 right-0 w-20 h-20 sm:w-24 sm:h-24 bg-brand-primary/5 rounded-bl-[100px] -z-10 group-hover:bg-brand-primary/10 transition-colors pointer-events-none">
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-full flex items-center justify-center border border-brand-border shadow-sm mb-3 sm:mb-4 shrink-0">
                        <i class="fa-solid fa-users text-brand-primary text-base sm:text-lg"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-brand-text-main mb-2 break-words whitespace-normal">
                        Siswa & Peserta Didik</h3>
                    <p class="text-xs sm:text-sm text-brand-text-muted leading-relaxed break-words">Melakukan presensi
                        harian, melihat jadwal pelajaran, riwayat kehadiran, dan mengajukan izin/sakit secara mandiri.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section class="py-16 sm:py-20 relative overflow-hidden w-full">
        <div class="absolute inset-0 bg-brand-surface border-y border-brand-border"></div>
        <div class="absolute inset-0 opacity-20"
            style="background-image: radial-gradient(#B81D24 2px, transparent 2px); background-size: 40px 40px;"></div>
        <div
            class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 sm:w-96 sm:h-96 rounded-full bg-brand-primary/5 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 -ml-20 -mb-20 w-48 h-48 sm:w-80 sm:h-80 rounded-full bg-brand-primary/5 blur-3xl pointer-events-none">
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center w-full">
            <h2
                class="text-xl sm:text-3xl md:text-5xl font-bold text-brand-text-main mb-4 sm:mb-6 break-words whitespace-normal">
                Siap Mewujudkan Kedisiplinan Digital di Sekolah Anda?</h2>
            <p
                class="text-brand-text-muted text-sm sm:text-base md:text-lg mb-8 sm:mb-10 max-w-2xl mx-auto w-full break-words whitespace-normal">
                Tingkatkan efisiensi, akurasi, dan ketertiban sekolah dalam hitungan menit bersama HadirSekolah.</p>
            <div class="flex justify-center w-full">
                <a href="/register?role=owner"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 sm:px-8 py-4 rounded-xl font-bold text-sm sm:text-lg bg-brand-primary text-white hover:bg-brand-primary/90 active:scale-95 transition-all shadow-md shrink-0 text-center whitespace-normal break-words">
                    <i class="fa-solid fa-paper-plane shrink-0"></i> Daftarkan Sekolah Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-brand-bg pt-12 sm:pt-16 pb-8 border-t border-brand-border w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 w-full text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-2 w-full min-w-0">
                    <img src="/hadiryuklogo1-4.png" alt="Logo HadirYuk" class="h-7 w-auto object-contain shrink-0">
                    <span class="font-bold text-xl text-brand-text-main truncate break-words">Hadir<span
                            class="text-brand-primary">Sekolah</span></span>
                </div>
            </div>

            <div
                class="mt-8 pt-8 border-t border-brand-border/50 flex flex-col md:flex-row justify-between items-center gap-4 w-full">
                <p
                    class="text-brand-text-muted text-xs sm:text-sm font-medium w-full text-center md:text-left break-words whitespace-normal">
                    © 2026 Almas Alfatih, CTO PT Thortech. Hak Cipta Dilindungi.
                </p>
                <div class="w-full text-center md:text-right">
                    <div
                        class="inline-block text-xs sm:text-sm font-medium text-brand-text-muted bg-brand-surface border border-brand-border px-3 sm:px-4 py-2 rounded-lg shadow-sm break-words whitespace-normal max-w-full">
                        Dibuat oleh <span class="text-brand-primary font-bold break-words whitespace-normal">Almas
                            Alfatih</span> (CTO of PT Thortech Asiasoftware Enjiniring)
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>