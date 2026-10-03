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

        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-brand-bg text-brand-dark antialiased font-sans selection:bg-brand-primary selection:text-white pb-12">

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
                    <a href="{{ route('panduan.rapor') }}"
                        class="text-sm font-bold text-brand-primary hover:text-brand-primary-hover flex items-center gap-1.5 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">menu_book</span>
                        <span>Panduan Rapor</span>
                    </a>

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
            <a href="{{ route('panduan.rapor') }}" @click="mobileMenuOpen = false" class="block font-bold text-brand-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">menu_book</span>
                <span>Panduan Rapor</span>
            </a>
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
                    <span class="text-[#B81D24]">Harga Termurah</span> <span class="whitespace-nowrap">se-Indonesia.</span>
                </h1>

                <p class="text-lg sm:text-xl text-brand-muted font-medium mb-8 leading-relaxed max-w-3xl mx-auto">
                    Satu sistem untuk seluruh operasional sekolah: Presensi 5 metode, Modul Rapor Resmi (Kurikulum Merdeka, SMK &amp; Madrasah), integrasi ekspor Dapodik/e-Rapor, hingga notifikasi WhatsApp otomatis ke orang tua tanpa biaya server mahal.
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
                        <p class="text-xs text-[#6B5E60]">Investasi sistem sekolah paling efisien se-Indonesia.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-brand-bg border border-[#EAE2E3]">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">fingerprint</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#1A1516]">Presensi 5 Metode</h4>
                        <p class="text-xs text-[#6B5E60]">RFID, QR, Geofencing GPS, Face AI, &amp; Manual.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-brand-bg border border-[#EAE2E3]">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">menu_book</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#1A1516]">Rapor Resmi Terintegrasi</h4>
                        <p class="text-xs text-[#6B5E60]">Auto-pull presensi, Smart Narasi TP, &amp; Cetak A4 Zero Server Load.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php($demoAccounts = config('demo.accounts'))

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
                    Jelajahi pengalaman 6 peran berbeda. Masuk sebagai Guru untuk mencoba Smart Narasi Rapor, atau sebagai Wali Kelas untuk melihat Auto-Pull Presensi &amp; Cetak Rapor Resmi A4. Klik salin untuk menyalin kredensial lengkap, atau masuk untuk mengisi kode sekolah dan email secara otomatis.
                </p>
                <p class="mt-4 text-xs font-semibold text-slate-500 md:hidden">← Geser untuk peran lain →</p>
            </div>

            <div class="flex md:grid md:grid-cols-2 lg:grid-cols-3 overflow-x-auto md:overflow-visible snap-x snap-mandatory gap-4 pb-4 -mx-4 px-4 md:mx-0 md:px-0 scrollbar-none">
                @foreach ($demoAccounts as $email => $account)
                    <article
                        x-data="{
                            copied: false,
                            copyError: false,
                            copyTimeout: null,
                            async copyCredentials(text) {
                                try {
                                    if (navigator.clipboard?.writeText) {
                                        await navigator.clipboard.writeText(text);
                                    } else {
                                        const field = document.createElement('textarea');
                                        field.value = text;
                                        field.setAttribute('readonly', '');
                                        field.style.position = 'fixed';
                                        field.style.opacity = '0';
                                        document.body.appendChild(field);
                                        let copied;
                                        try {
                                            field.select();
                                            copied = document.execCommand('copy');
                                        } finally {
                                            field.remove();
                                        }
                                        if (!copied) throw new Error('Clipboard copy failed');
                                    }
                                    this.copied = true;
                                    this.copyError = false;
                                } catch (error) {
                                    this.copyError = true;
                                }
                                clearTimeout(this.copyTimeout);
                                this.copyTimeout = setTimeout(() => {
                                    this.copied = false;
                                    this.copyError = false;
                                }, 2000);
                            }
                        }"
                        class="min-w-[85vw] sm:min-w-[340px] snap-center md:min-w-0 rounded-2xl border border-slate-200 bg-white p-4 md:p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-xl
                            @if($account['accent'] === 'slate') hover:border-slate-300
                            @elseif($account['accent'] === 'indigo') hover:border-indigo-300
                            @elseif($account['accent'] === 'emerald') hover:border-emerald-300
                            @elseif($account['accent'] === 'amber') hover:border-amber-300
                            @else hover:border-rose-300 @endif">
                        <div class="flex items-center gap-3 mb-3 md:mb-5">
                            <div @class([
                                'flex h-11 w-11 items-center justify-center rounded-xl',
                                'bg-slate-100 text-slate-800' => $account['accent'] === 'slate',
                                'bg-indigo-100 text-indigo-700' => $account['accent'] === 'indigo',
                                'bg-emerald-100 text-emerald-700' => $account['accent'] === 'emerald',
                                'bg-amber-100 text-amber-700' => $account['accent'] === 'amber',
                                'bg-rose-100 text-rose-700' => $account['accent'] === 'rose',
                            ])>
                                <span class="material-symbols-outlined">{{ $account['icon'] }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-brand-muted">Akun demo</p>
                                <h3 class="font-bold text-[#1A1516]">{{ $account['role'] }}</h3>
                            </div>
                        </div>

                        <div class="mb-3 md:mb-5 flex flex-wrap gap-1.5">
                            @foreach ($account['features'] as $feature)
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-[11px] font-semibold',
                                    'bg-slate-100 text-slate-700' => $account['accent'] === 'slate',
                                    'bg-indigo-50 text-indigo-700' => $account['accent'] === 'indigo',
                                    'bg-emerald-50 text-emerald-700' => $account['accent'] === 'emerald',
                                    'bg-amber-50 text-amber-700' => $account['accent'] === 'amber',
                                    'bg-rose-50 text-rose-700' => $account['accent'] === 'rose',
                                ])>{{ $feature }}</span>
                            @endforeach
                        </div>

                        <dl class="rounded-lg bg-gray-50/80 p-2.5 text-xs">
                            <div class="mb-2">
                                <dt class="font-semibold text-brand-muted">Email</dt>
                                <dd class="break-all font-semibold text-[#1A1516]">{{ $email }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="min-w-0">
                                    <dt class="font-semibold text-brand-muted">Kode sekolah</dt>
                                    <dd class="mt-0.5"><code class="break-all rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ config('demo.school_code') }}</code></dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="font-semibold text-brand-muted">Password</dt>
                                    <dd class="mt-0.5"><code class="break-all rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ config('demo.password') }}</code></dd>
                                </div>
                            </div>
                        </dl>

                        <div class="grid grid-cols-2 gap-2 md:gap-3 mt-4 md:mt-6">
                            <button type="button"
                                data-credentials="{{ "Kode sekolah: ".config('demo.school_code')."\nEmail: {$email}\nPassword: ".config('demo.password') }}"
                                @click="copyCredentials($el.dataset.credentials)"
                                class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl border border-slate-200 px-2 md:px-3 py-2.5 text-xs md:text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50">
                                <span class="material-symbols-outlined text-lg" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
                                <span aria-live="polite" x-text="copied ? '✓ Tersalin!' : (copyError ? 'Gagal menyalin' : 'Salin Kredensial')">Salin Kredensial</span>
                            </button>
                            <form method="POST" action="{{ route('demo.login') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ $email }}">
                                <button type="submit" @class([
                                    'inline-flex min-h-[40px] w-full items-center justify-center gap-2 rounded-xl px-2 md:px-3 py-2.5 text-xs md:text-sm font-bold text-white transition-colors',
                                    'bg-slate-800 hover:bg-slate-900' => $account['accent'] === 'slate',
                                    'bg-indigo-600 hover:bg-indigo-700' => $account['accent'] === 'indigo',
                                    'bg-emerald-600 hover:bg-emerald-700' => $account['accent'] === 'emerald',
                                    'bg-amber-500 hover:bg-amber-600' => $account['accent'] === 'amber',
                                    'bg-rose-600 hover:bg-rose-700' => $account['accent'] === 'rose',
                                ])>
                                    Masuk →
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <p class="mt-6 text-center text-xs text-brand-muted">
                Data demo ditujukan untuk eksplorasi fitur; perubahan pada akun dapat terlihat oleh pengguna demo lainnya.
            </p>
        </div>
    </section>

    <section id="apk-download" class="py-20 bg-white border-b border-[#EAE2E3]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center mb-10">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    <span class="material-symbols-outlined text-base">android</span>
                    Android Native / PWA Client (~4.3 MB)
                </span>
                <h2 class="mt-5 text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1A1516]">
                    Uji Aplikasi Mobile (Android APK)
                </h2>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <span class="material-symbols-outlined text-3xl">smartphone</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Siswa</p>
                            <h3 class="text-xl font-bold text-[#1A1516]">Download APK Siswa</h3>
                        </div>
                    </div>
                    <p class="mb-5 text-sm text-slate-600">Versi APK siswa untuk presensi dan aktivitas harian dengan ukuran sekitar 4.3 MB.</p>
                    <a href="{{ route('apk.download', ['role' => 'siswa']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700">
                        <span class="material-symbols-outlined">download</span>
                        Download APK Siswa
                    </a>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <span class="material-symbols-outlined text-3xl">family_restroom</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Orang Tua</p>
                            <h3 class="text-xl font-bold text-[#1A1516]">Download APK Orang Tua</h3>
                        </div>
                    </div>
                    <p class="mb-5 text-sm text-slate-600">Versi APK Orang Tua untuk memantau aktivitas dan status kehadiran anak dengan ukuran sekitar 4.3 MB.</p>
                    <a href="{{ route('apk.download', ['role' => 'orangtua']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-800 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900">
                        <span class="material-symbols-outlined">download</span>
                        Download APK Orang Tua
                    </a>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Jika muncul konfirmasi <strong>File might be harmful</strong>, pilih <strong>Tetap Download</strong>, lalu aktifkan izin <strong>Install unknown apps / Sumber tidak dikenal</strong> saat membuka berkas.
            </div>
        </div>
    </section>

    <!-- Fitur Utama -->
    <section id="fitur" class="py-24 bg-brand-bg relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#EAE2E3] text-xs font-bold uppercase tracking-wider text-[#B81D24] mb-4">
                    <span class="material-symbols-outlined text-base">auto_awesome</span>
                    Ekosistem Lengkap Sekolah
                </span>
                <h2 class="text-2xl sm:text-4xl font-bold text-[#1A1516] tracking-tight mb-3">Fitur Dahsyat Untuk <span class="text-[#B81D24]">Sekolah Modern</span>
                </h2>
                <p class="text-brand-muted font-medium max-w-2xl mx-auto">Satu platform untuk menjawab semua kebutuhan operasional sekolah Anda — dari presensi harian hingga cetak rapor resmi nasional.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Kartu 1: Presensi Fleksibel Multi-Mode -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-6 transition-transform group-hover:scale-105">
                            <span class="material-symbols-outlined text-3xl font-bold">sensors</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1516] mb-4">Presensi Fleksibel Multi-Mode</h3>
                        <p class="text-xs text-[#6B5E60] font-medium mb-4">RFID, QR, Geofencing, Face AI, Presensi Guru &amp; Staf.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span>IoT RFID Scanner: Tap kartu tanpa butuh PC maupun operator</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span>QR Code Dynamic: Anti-fraud dan anti-titip absen cepat</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span>Geofencing GPS: Presensi via HP akurat dalam radius sekolah</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span>Face Recognition AI dengan verifikasi liveness</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span>Presensi Guru &amp; Staf, KBM per-mapel, &amp; ekstrakurikuler</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#F4EFEB] text-xs font-semibold text-[#B81D24] flex items-center gap-1">
                        <span>5 Mode Presensi Terintegrasi</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </div>
                </div>

                <!-- Kartu 2: Dasbor Terpadu 6 Peran -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-6 transition-transform group-hover:scale-105">
                            <span class="material-symbols-outlined text-3xl font-bold">dashboard</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1516] mb-4">Dasbor Terpadu 6 Peran</h3>
                        <p class="text-xs text-[#6B5E60] font-medium mb-4">Operator, Kepala Sekolah, Guru, Wali Kelas, Siswa, Orang Tua.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Operator:</strong> Master data guru, siswa, jam &amp; jadwal sekolah</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Kepala Sekolah:</strong> Pantau kehadiran, analitik &amp; laporan real-time</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Guru:</strong> Input presensi KBM, jurnal mengajar, &amp; nilai mapel</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Wali Kelas:</strong> Rekap absensi kelas, catatan sikap, &amp; cetak rapor</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Siswa:</strong> Akses jadwal, riwayat presensi, tugas &amp; kartu digital</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Orang Tua:</strong> Pantau kehadiran &amp; rapor anak via HP</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#F4EFEB] text-xs font-semibold text-[#B81D24] flex items-center gap-1">
                        <span>Akses Multi-Role Lengkap</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </div>
                </div>

                <!-- Kartu 3 (Baru): Modul Rapor Kurikulum Merdeka & K13 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-6 transition-transform group-hover:scale-105">
                            <span class="material-symbols-outlined text-3xl font-bold">assignment_turned_in</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1516] mb-4">Modul Rapor Kurikulum Merdeka &amp; K13</h3>
                        <p class="text-xs text-[#6B5E60] font-medium mb-4">Smart Auto-Narasi TP, Leger Nilai, Cetak A4 Zero Server Load, QR Code Verifikasi SHA-256.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Smart Auto-Narasi TP:</strong> Generator otomatis narasi capaian kompetensi tertinggi &amp; terendah</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Leger Nilai Lengkap:</strong> Pengolahan nilai formatif, sumatif materi, &amp; sumatif akhir</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Cetak A4 Zero Server Load:</strong> Render cetak rapor resmi A4 langsung di peramban</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>QR Code Verifikasi SHA-256:</strong> Validasi keaslian dokumen tanda tangan digital resmi</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#F4EFEB] text-xs font-semibold text-[#B81D24] flex items-center gap-1">
                        <a href="{{ route('panduan.rapor') }}" class="hover:underline flex items-center gap-1">
                            <span>Panduan Lengkap Rapor</span>
                            <span class="material-symbols-outlined text-sm">menu_book</span>
                        </a>
                    </div>
                </div>

                <!-- Kartu 4 (Baru): Kokurikuler P5/P5RA & Vokasi SMK -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-6 transition-transform group-hover:scale-105">
                            <span class="material-symbols-outlined text-3xl font-bold">engineering</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1516] mb-4">Kokurikuler P5/P5RA &amp; Vokasi SMK</h3>
                        <p class="text-xs text-[#6B5E60] font-medium mb-4">Rubrik Projek P5 &amp; P5RA Kemenag, Penilaian PKL terintegrasi Geofence industri, &amp; Transkrip UKK.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Rubrik Projek P5 &amp; P5RA Kemenag:</strong> Penilaian dimensi Profil Pelajar Pancasila &amp; Rahmatan Lil Alamin</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Penilaian Deskriptif Projek:</strong> Rekap capaian MB, SB, BSH, &amp; SAB otomatis</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Penilaian PKL Terintegrasi:</strong> Absensi geofence di lokasi mitra industri &amp; lembar magang</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Transkrip UKK:</strong> Transkrip Uji Kompetensi Keahlian SMK standar Ditjen Vokasi</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#F4EFEB] text-xs font-semibold text-[#B81D24] flex items-center gap-1">
                        <a href="{{ route('panduan.rapor') }}" class="hover:underline flex items-center gap-1">
                            <span>Panduan Projek P5 &amp; Kurikulum</span>
                            <span class="material-symbols-outlined text-sm">menu_book</span>
                        </a>
                    </div>
                </div>

                <!-- Kartu 5 (Baru): Auto-Pull Presensi & Ekspor Siap Setor -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-6 transition-transform group-hover:scale-105">
                            <span class="material-symbols-outlined text-3xl font-bold">cloud_sync</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1516] mb-4">Auto-Pull Presensi &amp; Ekspor Siap Setor</h3>
                        <p class="text-xs text-[#6B5E60] font-medium mb-4">Rekap Sakit/Izin/Alpa otomatis tanpa hitung manual, Ekspor 1-Klik e-Rapor SP, RDM Kemenag, &amp; Leger Dapodik.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Auto-Pull Presensi:</strong> Rekap Sakit/Izin/Alpa otomatis tanpa hitung manual ke rapor</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Ekspor 1-Klik e-Rapor SP:</strong> File tervalidasi siap impor ke aplikasi e-Rapor resmi dinas</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>RDM Kemenag:</strong> Format kompatibel Rapor Digital Madrasah Kementerian Agama</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Leger Dapodik:</strong> Ekspor leger nilai dan presensi sinkron tanpa dobel input</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#F4EFEB] text-xs font-semibold text-[#B81D24] flex items-center gap-1">
                        <span>Efisiensi Operator Hingga 90%</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </div>
                </div>

                <!-- Kartu 6: Notifikasi WhatsApp & Hemat Sumber Daya -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#EAE2E3] group hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-[#F4EFEB] text-[#B81D24] flex items-center justify-center mb-6 transition-transform group-hover:scale-105">
                            <span class="material-symbols-outlined text-3xl font-bold">send_and_archive</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1516] mb-4">Notifikasi WhatsApp &amp; Hemat Sumber Daya</h3>
                        <p class="text-xs text-[#6B5E60] font-medium mb-4">Notifikasi penerbitan rapor otomatis ke orang tua &amp; arsitektur lean tanpa server mahal.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Notifikasi Penerbitan Rapor:</strong> Kirim link rapor digital otomatis ke orang tua via WhatsApp</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Broadcast Presensi Real-Time:</strong> Laporan absensi harian langsung ke wali murid</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Pengumuman &amp; Izin Sakit:</strong> Broadcast pesan sekolah &amp; approval digital tanpa kertas</span>
                            </li>
                            <li class="flex items-start gap-2.5 font-medium text-sm text-brand-dark">
                                <span class="material-symbols-outlined text-[#B81D24] text-lg shrink-0 mt-0.5">check_circle</span>
                                <span><strong>Arsitektur Lean Tanpa Server Mahal:</strong> Efisien, cepat, dan ramah anggaran operasional</span>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#F4EFEB] text-xs font-semibold text-[#B81D24] flex items-center gap-1">
                        <span>Notifikasi Otomatis Terpercaya</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </div>
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
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Terbatas (Hanya 1-2)</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">5 Metode (Lengkap!)</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Modul Rapor</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Harus sewa aplikasi e-rapor terpisah &amp; rawan server down</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Terintegrasi penuh dengan absensi &amp; Zero Server Load</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Administrasi Nilai</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Ketik narasi manual &amp; hitung absensi manual</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Smart Narasi otomatis &amp; Auto-Pull presensi HadirYuk</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Integrasi Data</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Ketik ulang ke aplikasi dinas</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Ekspor 1-klik kompatibel e-Rapor SP, RDM, &amp; Dapodik</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Biaya Server</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Mahal (Jutaan/Bulan)</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Sangat Murah &amp; Efisien</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Akses Orang Tua</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Seringkali Tidak Ada</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Dasbor Khusus Ortu</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Notifikasi WA</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Bayar Per Pesan / Mahal</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Tersedia &amp; Terjangkau</td>
                        </tr>
                        <tr>
                            <td class="p-6 sm:p-8 bg-gray-50">Kecepatan Tap RFID</td>
                            <td class="p-6 sm:p-8 font-medium text-slate-600">Butuh PC &amp; Operator</td>
                            <td class="p-6 sm:p-8 text-[#B81D24] italic">Stand-alone IoT (Tap &amp; Go!)</td>
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
                        <span class="font-semibold text-[#1A1516] text-base">Apakah HadirYuk mendukung Rapor Kurikulum Merdeka, SMK, dan Madrasah?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 0 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 0" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        Sangat mendukung! Modul Rapor HadirYuk dirancang fleksibel untuk seluruh jenjang: Kurikulum Merdeka Fase A sampai F (SD/MI, SMP/MTs, SMA/MA), rubrik Kokurikuler Projek P5 &amp; P5RA Kemenag lengkap dengan skala capaian deskriptif, hingga Rapor Kejuruan SMK lengkap dengan penilaian Praktik Kerja Lapangan (PKL) terintegrasi Geofence industri serta Transkrip Uji Kompetensi Keahlian (UKK).
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 1 ? null : 1"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Apakah operator perlu menginput ulang nilai ke e-Rapor atau Dapodik?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 1 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 1" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        Tidak perlu repot! HadirYuk menyediakan fitur Ekspor 1-Klik yang kompatibel dengan format resmi e-Rapor SP Kemendikbudristek, RDM (Rapor Digital Madrasah) Kemenag, dan Leger Dapodik. Rekap presensi masuk otomatis (Auto-Pull) dan data nilai tersusun rapi sehingga dapat langsung diimpor ke sistem dinas tanpa konversi kolom berbelit-belit.
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 2 ? null : 2"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Apakah server akan lambat saat seluruh guru mencetak rapor bersamaan?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 2 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 2" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        Sama sekali tidak! HadirYuk mengusung arsitektur Zero Server Load Print Engine. Seluruh proses perataan tata letak (layout) dan render lembar cetak rapor resmi A4 dieksekusi secara instan di peramban pengguna (client-side), bukan di server pusat. Ratusan guru dan wali kelas dapat mencetak buku rapor secara serentak di akhir semester tanpa khawatir server down atau lambat.
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 3 ? null : 3"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Apakah benar harga HadirYuk termurah di Indonesia?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 3 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 3" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        Ya, benar. Kami merancang HadirYuk dengan arsitektur cloud yang sangat efisien sehingga bisa
                        menekan biaya server seminimal mungkin. Fokus kami adalah membantu sekolah mendigitalisasi
                        operasionalnya tanpa beban biaya bulanan yang mencekik.
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 4 ? null : 4"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Apakah kami harus membeli alat RFID khusus?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 4 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 4" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        HadirYuk mendukung berbagai metode. Jika sekolah ingin menggunakan RFID, kami menyediakan skema
                        IoT yang sangat murah. Namun jika sekolah ingin GRATIS tanpa alat, Anda bisa menggunakan metode
                        QR Code atau Geofencing GPS yang hanya membutuhkan smartphone.
                    </div>
                </div>

                <div class="border border-[#EAE2E3] rounded-2xl bg-white shadow-sm overflow-hidden">
                    <button @click="active = active === 5 ? null : 5"
                        class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-[#1A1516] text-base">Bagaimana jika guru atau admin kami gaptek?</span>
                        <span class="material-symbols-outlined transform transition-transform"
                            :class="active === 5 ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="active === 5" x-cloak class="p-6 pt-0 text-[#6B5E60] text-sm font-medium border-t border-[#EAE2E3]">
                        HadirYuk didesain dengan antarmuka yang sangat modern dan user-friendly. Kami mengadopsi prinsip
                        "Sekali Lihat Langsung Paham". Selain itu, tim kami menyediakan dokumentasi lengkap, panduan rapor resmi, dan
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
                <div class="flex flex-wrap items-center gap-6 md:gap-8">
                    <a href="{{ route('panduan.rapor') }}" class="text-[#6B5E60] hover:text-[#B81D24] text-sm font-bold transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">menu_book</span>
                        <span>Panduan Rapor</span>
                    </a>
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
        class="fixed bottom-6 right-6 z-50 bg-emerald-600 text-white w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center shadow-2xl hover:bg-emerald-700 hover:scale-110 active:scale-95 transition-all group"
        aria-label="Konsultasi via WhatsApp">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
        <span
            class="absolute right-20 bg-emerald-600 px-4 py-2 rounded-xl text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl">
            Tanya Admin via WhatsApp
        </span>
    </a>

</body>

</html>