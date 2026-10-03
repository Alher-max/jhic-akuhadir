<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Panduan & Tutorial Modul Rapor Sekolah HadirYuk</title>
    <meta name="description"
        content="Panduan langkah demi langkah penggunaan modul rapor Kurikulum Merdeka, Madrasah Kemenag, dan SMK Vokasi untuk seluruh peran di sekolah: Operator, Guru Mapel, Wali Kelas, Fasilitator P5, Pembimbing PKL/UKK, Siswa, dan Orang Tua.">

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
                        'brand-primary-hover': '#9B181E',
                        'brand-dark': '#1A1516',
                        'brand-bg': '#FDFBF9',
                        'brand-surface': '#FFFFFF',
                        'brand-border': '#EAE2E3',
                        'brand-muted': '#6B5E60',
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
            background-size: 28px 28px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .print-break {
                page-break-before: always;
                break-before: page;
            }
            .role-guide-panel {
                display: block !important;
                margin-bottom: 2rem !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</head>

<body class="bg-brand-bg text-brand-dark antialiased font-sans selection:bg-brand-primary selection:text-white"
      x-data="{
          activeRole: 'operator',
          searchQuery: '',
          matches(text) {
              if (!this.searchQuery.trim()) return true;
              return text.toLowerCase().includes(this.searchQuery.toLowerCase());
          }
      }">

    <!-- Top Navigation Bar (Screen Only) -->
    <header class="no-print sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-brand-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="/" class="flex items-center gap-2 group">
                        <img src="{{ asset('images/logo.png') }}" alt="HadirYuk" class="h-9 w-auto">
                        <span class="font-black text-xl tracking-tighter text-brand-dark">
                            Hadir<span class="text-brand-primary">Yuk</span>
                        </span>
                    </a>
                    <span class="hidden sm:inline-block text-gray-300">|</span>
                    <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-brand-primary border border-red-100">
                        <span class="material-symbols-outlined text-[15px]">menu_book</span>
                        Pusat Panduan Rapor
                    </span>
                </div>

                <!-- Navigation Action Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="/"
                        class="px-3.5 py-2 text-xs font-bold text-gray-600 hover:text-brand-primary rounded-xl hover:bg-gray-100 transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        <span class="hidden sm:inline">Kembali ke Beranda</span>
                        <span class="sm:hidden">Beranda</span>
                    </a>

                    <button onclick="window.print()"
                        class="px-3.5 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition flex items-center gap-1.5 shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">print</span>
                        <span class="hidden md:inline">Cetak Panduan</span>
                    </button>

                    <a href="{{ route('login') }}"
                        class="px-4 py-2 text-xs font-extrabold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        <span>Coba Akun Demo</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-12 pb-10 overflow-hidden border-b border-brand-border bg-white">
        <div class="absolute inset-0 bg-grid-pattern opacity-40"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <!-- Badge Resmi -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-extrabold bg-red-50 text-brand-primary border border-red-200 shadow-2xs mb-4">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    DOKUMENTASI SISTEM & BUKU PANDUAN PENGGUNA RESMI
                </div>

                <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-brand-dark tracking-tight leading-tight">
                    Pusat Panduan & Tutorial <br class="hidden sm:inline">
                    <span class="text-brand-primary">Modul Rapor Sekolah HadirYuk</span>
                </h1>

                <p class="mt-3 text-sm sm:text-base text-brand-muted max-w-2xl mx-auto leading-relaxed">
                    Panduan langkah demi langkah penggunaan modul rapor Kurikulum Merdeka, Madrasah Kemenag, dan SMK Vokasi untuk seluruh peran di sekolah.
                </p>

                <!-- Search / Instant Filter Bar -->
                <div class="mt-8 max-w-xl mx-auto relative no-print">
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-gray-400 text-xl pointer-events-none">search</span>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Cari kata kunci: 'presensi', 'P5', 'cetak', 'whatsapp', 'UKK', 'leger'..."
                               class="w-full pl-11 pr-10 py-3.5 bg-gray-50 border border-brand-border rounded-2xl text-sm font-medium text-brand-dark placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:bg-white shadow-sm transition">
                        <button x-show="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute right-3.5 text-gray-400 hover:text-gray-600 transition"
                                title="Reset Pencarian">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                    <div x-show="searchQuery" class="text-left mt-2 px-2 text-xs text-brand-primary font-bold">
                        Menampilkan hasil pencarian untuk: "<span x-text="searchQuery"></span>"
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content: Interactive Role Guide -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Role Selector Tab Pills (Screen Only) -->
        <div class="no-print mb-8">
            <div class="text-xs font-extrabold uppercase tracking-wider text-gray-400 mb-3 px-1">
                Pilih Peran Pengguna di Sekolah:
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-3 scrollbar-none">
                <!-- 1. Operator & Kepala Sekolah -->
                <button @click="activeRole = 'operator'"
                    :class="activeRole === 'operator' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">domain</span>
                    <span>Operator & Kepala Sekolah</span>
                </button>

                <!-- 2. Guru Mata Pelajaran -->
                <button @click="activeRole = 'teacher'"
                    :class="activeRole === 'teacher' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">school</span>
                    <span>Guru Mata Pelajaran</span>
                </button>

                <!-- 3. Wali Kelas -->
                <button @click="activeRole = 'homeroom'"
                    :class="activeRole === 'homeroom' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">assignment_ind</span>
                    <span>Wali Kelas</span>
                </button>

                <!-- 4. Fasilitator P5 & P5RA -->
                <button @click="activeRole = 'p5'"
                    :class="activeRole === 'p5' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">psychology</span>
                    <span>Fasilitator P5 & P5RA</span>
                </button>

                <!-- 5. Pembimbing PKL & UKK (SMK) -->
                <button @click="activeRole = 'vocational'"
                    :class="activeRole === 'vocational' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">precision_manufacturing</span>
                    <span>Vokasi SMK (PKL & UKK)</span>
                </button>

                <!-- 6. Siswa -->
                <button @click="activeRole = 'student'"
                    :class="activeRole === 'student' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">person</span>
                    <span>Siswa</span>
                </button>

                <!-- 7. Orang Tua -->
                <button @click="activeRole = 'parent'"
                    :class="activeRole === 'parent' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">family_restroom</span>
                    <span>Orang Tua</span>
                </button>

                <!-- 8. Verifikasi Dokumen Publik -->
                <button @click="activeRole = 'verification'"
                    :class="activeRole === 'verification' ? 'bg-brand-primary text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-brand-border'"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                    <span>Verifikasi QR Code</span>
                </button>
            </div>
        </div>

        <!-- ==================== 1. OPERATOR & KEPALA SEKOLAH ==================== -->
        <section x-show="activeRole === 'operator' || (searchQuery !== '' && matches('Operator Kepala Sekolah Tahun Ajaran Rombel Fase Dapodik e-Rapor RDM Supervisi'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 text-brand-primary flex items-center justify-center font-black border border-red-100">
                        <span class="material-symbols-outlined text-2xl">domain</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Operator & Kepala Sekolah</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Pengaturan master data tahun ajaran, fase kurikulum, supervisi nilai, dan integrasi ekspor Dapodik.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Peran: Manajerial & Supervisi</span>
            </div>

            <!-- Ringkasan Peran -->
            <div class="mt-6 bg-red-50/50 rounded-2xl p-4 border border-red-100 text-xs sm:text-sm text-brand-dark leading-relaxed">
                <strong>Ringkasan Tanggung Jawab:</strong> Bertanggung jawab mengatur tahun ajaran aktif, menetapkan fase Kurikulum Merdeka atau Madrasah Kemenag pada rombel, memantau keterisian buku nilai guru mapel, mengunci rapor, dan mengalirkan data ke Dapodik/RDM.
            </div>

            <!-- Langkah-Langkah -->
            <div class="mt-8 space-y-6">
                <h3 class="text-base font-extrabold text-brand-dark uppercase tracking-wide flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary text-xl">format_list_numbered</span>
                    Langkah-Langkah Praktis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5 hover:border-brand-primary/50 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-brand-primary text-white font-extrabold text-xs flex items-center justify-center">1</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Aktivasi Tahun Ajaran & Semester</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka menu <strong>Tahun Ajaran</strong>. Buat atau aktifkan tahun ajaran (contoh: 2026/2027 Ganjil). Sistem HadirYuk menjaga invariansi hanya ada 1 tahun ajaran aktif per sekolah.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-brand-primary/50 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-brand-primary text-white font-extrabold text-xs flex items-center justify-center">2</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Konfigurasi Rombel & Fase</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Tentukan <strong>Fase Kurikulum</strong> (Fase A s.d. F) dan <strong>Tipe Kurikulum</strong> (Merdeka, Madrasah Kemenag, atau K13) pada masing-masing rombongan belajar.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-brand-primary/50 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-brand-primary text-white font-extrabold text-xs flex items-center justify-center">3</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Supervisi Progres Nilai & Kunci Rapor</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Kepala Sekolah dan Operator memegang hak akses supervisi ke seluruh rombel kelas. Anda dapat memantau keterisian buku nilai dan mengunci rapor kelas dari intervensi tak sah.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-brand-primary/50 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-brand-primary text-white font-extrabold text-xs flex items-center justify-center">4</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Ekspor Kompatibilitas Dapodik & RDM</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka menu <strong>Ekspor Nilai</strong>. Unduh format e-Rapor SP (Kemendikbud), Rapor Digital Madrasah (RDM Kemenag), atau Leger Rekapitulasi Dapodik dalam format CSV Excel-Ready.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Callout Keunggulan -->
            <div class="mt-6 bg-gradient-to-r from-red-50 to-amber-50 rounded-2xl p-5 border border-red-200 text-xs sm:text-sm">
                <div class="flex items-center gap-2 font-black text-brand-primary mb-1">
                    <span class="material-symbols-outlined text-lg">bolt</span>
                    Keunggulan HadirYuk: Streaming Ekspor O(1) Memory
                </div>
                <p class="text-brand-dark leading-relaxed">
                    Pengaliran data ekspor menggunakan <em>Native PHP Output Stream</em> dengan injeksi UTF-8 BOM dan delimiter titik koma (<code>;</code>) standar Excel Indonesia. Beban RAM server konstan 0 MB sekalipun mengekspor data puluhan ribu siswa.
                </p>
            </div>
        </section>

        <!-- ==================== 2. GURU MATA PELAJARAN ==================== -->
        <section x-show="activeRole === 'teacher' || (searchQuery !== '' && matches('Guru Mata Pelajaran TP Tujuan Pembelajaran Nilai Sumatif Smart Narasi Buku Nilai'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-black border border-indigo-100">
                        <span class="material-symbols-outlined text-2xl">school</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Guru Mata Pelajaran</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Penyusunan Tujuan Pembelajaran (TP), entri nilai sumatif siswa, dan Smart Narasi otomatis.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Peran: Pendidik Pengampu</span>
            </div>

            <div class="mt-6 bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100 text-xs sm:text-sm text-brand-dark leading-relaxed">
                <strong>Ringkasan Tanggung Jawab:</strong> Merumuskan Tujuan Pembelajaran (TP) per semester, menginput nilai hasil asesmen formatif/sumatif, serta meninjau deskripsi narasi capaian belajar siswa sebelum diserahkan ke wali kelas.
            </div>

            <div class="mt-8 space-y-6">
                <h3 class="text-base font-extrabold text-brand-dark uppercase tracking-wide flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary text-xl">format_list_numbered</span>
                    Langkah-Langkah Praktis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5 hover:border-indigo-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center">1</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Buka Menu Buku Nilai</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Pilih menu <strong>Buku Nilai Guru</strong>. Sistem otomatis menyaring jadwal dan rombel kelas yang ditugaskan kepada Anda sesuai tahun ajaran aktif.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-indigo-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center">2</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Input Tujuan Pembelajaran (TP)</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Definisikan Tujuan Pembelajaran (TP) yang diajarkan pada semester berjalan (contoh: <em>TP 1: Memahami struktur algoritma percabangan</em>).
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-indigo-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center">3</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Entri Nilai Angka Siswa (0 - 100)</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Masukkan nilai akhir siswa pada kolom yang disediakan. Sistem menerapkan validasi numerik ketat dan mencegah duplikasi data melalui operasi atomik.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-indigo-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center">4</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Smart Narasi Capaian Otomatis</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Sistem otomatis menghasilkan narasi capaian kompetensi tertinggi dan hal yang perlu ditingkatkan. Guru tetap memiliki fleksibilitas untuk menyunting deskripsi secara manual.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-2xl p-5 border border-indigo-200 text-xs sm:text-sm">
                <div class="flex items-center gap-2 font-black text-indigo-700 mb-1">
                    <span class="material-symbols-outlined text-lg">auto_awesome</span>
                    Keunggulan HadirYuk: Smart Narasi Berbasis Tujuan Pembelajaran
                </div>
                <p class="text-brand-dark leading-relaxed">
                    Guru tidak perlu lagi menyalin dan mengetik deskripsi raport ratusan siswa secara manual. Algoritma HadirYuk memetakan capaian optimal dan area peningkatan secara instan sesuai standar Kurikulum Merdeka.
                </p>
            </div>
        </section>

        <!-- ==================== 3. WALI KELAS ==================== -->
        <section x-show="activeRole === 'homeroom' || (searchQuery !== '' && matches('Wali Kelas Leger Presensi Auto-Pull Sakit Izin Alpa Catatan Karakter Ekskul Publikasi WhatsApp Cetak A4'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black border border-emerald-100">
                        <span class="material-symbols-outlined text-2xl">assignment_ind</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Wali Kelas</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Kompilasi leger nilai, auto-pull presensi HadirYuk, catatan karakter, publikasi, dan cetak massal A4.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Peran: Pengelola Rombel</span>
            </div>

            <div class="mt-6 bg-emerald-50/50 rounded-2xl p-4 border border-emerald-100 text-xs sm:text-sm text-brand-dark leading-relaxed">
                <strong>Ringkasan Tanggung Jawab:</strong> Mengawasi kompilasi nilai seluruh mapel kelas binaan, menarik data rekap presensi secara otomatis, mengisi catatan motivasi siswa, mencatat nilai ekskul, menerbitkan rapor digital, dan mencetak lembar rapor fisik.
            </div>

            <div class="mt-8 space-y-6">
                <h3 class="text-base font-extrabold text-brand-dark uppercase tracking-wide flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary text-xl">format_list_numbered</span>
                    Langkah-Langkah Praktis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5 hover:border-emerald-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">1</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Tinjau Matriks Leger Kelas</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka menu <strong>Dasbor Wali Kelas &rarr; Leger Nilai</strong>. Pantau matriks nilai seluruh siswa, nilai rerata per mapel, dan status kelengkapan nilai kelas.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-emerald-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">2</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">1-Klik Auto-Pull Presensi HadirYuk</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka tab <strong>Rekap Presensi</strong> lalu klik tombol <em>"Tarik Data Presensi HadirYuk"</em>. Sistem otomatis menghitung jumlah Sakit, Izin, dan Alpa secara akurat dari rentang tanggal semester aktif.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-emerald-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">3</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Catatan Karakter & Ekstrakurikuler</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Isi catatan perkembangan karakter, motivasi belajar, dan status kenaikan kelas pada tab <strong>Catatan</strong>, serta predikat kegiatan ekstrakurikuler pada tab <strong>Ekstrakurikuler</strong>.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-emerald-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">4</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Publikasikan Rapor & Notifikasi WhatsApp</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Klik tombol <strong>Publikasikan Rapor</strong>. Sistem secara atomik mengubah status menjadi <em>Published</em>, men-generate QR Code verifikasi unik, dan otomatis mengirimkan pesan WhatsApp ke orang tua.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl p-5 border border-emerald-200 text-xs sm:text-sm">
                <div class="flex items-center gap-2 font-black text-emerald-800 mb-1">
                    <span class="material-symbols-outlined text-lg">print</span>
                    Keunggulan HadirYuk: Zero Server Load Print Engine (A4 Portrait)
                </div>
                <p class="text-brand-dark leading-relaxed">
                    Wali kelas dapat mencetak seluruh rapor kelas sekaligus (*Batch Print*) dengan satu klik tanpa antrean server. CSS <code>@page { size: A4 portrait; }</code> dan pemisah halaman <code>.page-break</code> memastikan pencetakan rapi dan hemat kertas.
                </p>
            </div>
        </section>

        <!-- ==================== 4. FASILITATOR P5 & P5RA ==================== -->
        <section x-show="activeRole === 'p5' || (searchQuery !== '' && matches('P5 P5RA Profil Pelajar Pancasila Rahmatan Lil Alamin Rubrik MB SB BSH SAB Projek'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center font-black border border-amber-100">
                        <span class="material-symbols-outlined text-2xl">psychology</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Fasilitator P5 & P5RA</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Pengelolaan projek profil pelajar Pancasila & Rahmatan Lil 'Alamin Madrasah Kemenag.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">Peran: Koordinator Projek</span>
            </div>

            <div class="mt-6 bg-amber-50/50 rounded-2xl p-4 border border-amber-100 text-xs sm:text-sm text-brand-dark leading-relaxed">
                <strong>Standar Predikat Capaian Kualitatif:</strong> Menggunakan 4 skala perkembangan resmi: <strong>MB</strong> (Mulai Berkembang), <strong>SB</strong> (Sedang Berkembang), <strong>BSH</strong> (Berkembang Sesuai Harapan), dan <strong>SAB</strong> (Sangat Berkembang).
            </div>

            <div class="mt-8 space-y-6">
                <h3 class="text-base font-extrabold text-brand-dark uppercase tracking-wide flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary text-xl">format_list_numbered</span>
                    Langkah-Langkah Praktis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5 hover:border-amber-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-amber-600 text-white font-extrabold text-xs flex items-center justify-center">1</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Buat Projek Profil Rombel</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka menu <strong>Projek P5 & P5RA</strong> lalu buat projek baru. Masukkan tema projek (contoh: <em>Gaya Hidup Berkelanjutan</em>), judul, dan deskripsi singkat kegiatan.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-amber-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-amber-600 text-white font-extrabold text-xs flex items-center justify-center">2</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Pilih Dimensi via Quick Picker</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Gunakan modal <em>Quick Picker</em> untuk memilih 6 Dimensi Pancasila Kemendikbudristek atau 10 Nilai Rahmatan Lil 'Alamin Kemenag beserta elemen dan sub-elemen target projek.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-amber-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-amber-600 text-white font-extrabold text-xs flex items-center justify-center">3</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Matriks Penilaian Interaktif</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka lembar penilaian projek. Klik radio button predikat kualitatif (MB, SB, BSH, SAB) untuk tiap siswa terhadap sub-elemen yang dinilai.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-amber-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-amber-600 text-white font-extrabold text-xs flex items-center justify-center">4</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Cetak Lembar Rapor Projek A4</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Cetak lembar Rapor Projek Profil resmi A4 lengkap dengan centang (✓) rubrik otomatis, catatan proses fasilitator, dan tanda tangan resmi 3 pihak.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 5. PEMBIMBING PKL & UKK (SMK) ==================== -->
        <section x-show="activeRole === 'vocational' || (searchQuery !== '' && matches('Vokasi SMK PKL UKK Magang DUDI Industri Geofence Sertifikat Transkrip LSP'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-800 flex items-center justify-center font-black border border-cyan-100">
                        <span class="material-symbols-outlined text-2xl">precision_manufacturing</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Modul Vokasi SMK (PKL & UKK)</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Pengelolaan penempatan magang DUDI, presensi lokasi geofence, dan Uji Kompetensi Keahlian.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">Peran: Pembimbing Vokasi</span>
            </div>

            <div class="mt-6 bg-cyan-50/50 rounded-2xl p-4 border border-cyan-100 text-xs sm:text-sm text-brand-dark leading-relaxed">
                <strong>Formula Pembobotan Resmi:</strong><br>
                &bull; <strong>PKL (Praktik Kerja Lapangan):</strong> 50% Kompetensi Teknis + 30% Budaya Kerja (Softskill) + 20% Kehadiran Industri (Auto-Pull).<br>
                &bull; <strong>UKK (Uji Kompetensi Keahlian):</strong> 30% Teori Kejuruan + 70% Ujian Praktik LSP / DUDI (atau 100% Praktik jika tanpa teori).
            </div>

            <div class="mt-8 space-y-6">
                <h3 class="text-base font-extrabold text-brand-dark uppercase tracking-wide flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary text-xl">format_list_numbered</span>
                    Langkah-Langkah Praktis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5 hover:border-cyan-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-cyan-700 text-white font-extrabold text-xs flex items-center justify-center">1</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Daftarkan Penempatan Magang DUDI</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka <strong>Modul Vokasi &rarr; Penempatan PKL</strong>. Masukkan nama industri mitra, mentor lapangan, guru pembimbing sekolah, dan rentang tanggal magang.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-cyan-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-cyan-700 text-white font-extrabold text-xs flex items-center justify-center">2</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Tautkan Geofence Presensi Industri</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Pilih titik lokasi GPS kantor industri mitra yang telah didaftarkan di HadirYuk. Log presensi siswa selama PKL otomatis difilter khusus pada koordinat industri tersebut.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-cyan-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-cyan-700 text-white font-extrabold text-xs flex items-center justify-center">3</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Input Penilaian PKL Terbobot</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Buka lembar penilaian PKL. Nilai kehadiran di-auto-pull langsung dari log absensi HadirYuk. Masukkan nilai teknis dan budaya kerja untuk menghasilkan predikat kelulusan otomatis.
                        </p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5 hover:border-cyan-400 transition">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-xl bg-cyan-700 text-white font-extrabold text-xs flex items-center justify-center">4</span>
                            <h4 class="font-extrabold text-sm text-brand-dark">Entri Nilai UKK & Cetak Transkrip</h4>
                        </div>
                        <p class="text-xs text-brand-muted leading-relaxed">
                            Masukkan skema sertifikasi, nama asesor LSP, nomor sertifikat BNSP, dan nilai ujian teori/praktik. Cetak Transkrip UKK resmi A4 bertanda tangan 3 pihak.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 6. SISWA ==================== -->
        <section x-show="activeRole === 'student' || (searchQuery !== '' && matches('Siswa Portal Mobile Rapor Siswa Mandiri Cetak Nilai Digital'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-black border border-blue-100">
                        <span class="material-symbols-outlined text-2xl">person</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Siswa</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Akses rapor digital, pantau grafik nilai belajar, dan cetak lembar rapor mandiri.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Peran: Peserta Didik</span>
            </div>

            <div class="mt-8 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center mb-3">1</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Masuk ke Aplikasi HadirYuk</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Gunakan akun siswa pada aplikasi mobile HadirYuk Siswa (Android) atau melalui browser web sekolah.</p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center mb-3">2</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Buka Menu Rapor Siswa</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Pilih menu <strong>Rapor Saya</strong>. Jika wali kelas telah menerbitkan rapor, rincian nilai dan kehadiran akan langsung tampil.</p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center mb-3">3</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Cetak & Unduh Mandiri</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Klik tombol <em>"Cetak Lembar Rapor Resmi A4"</em> untuk menyimpan file PDF atau mencetak langsung dengan QR Code validasi keaslian.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 7. ORANG TUA / WALI MURID ==================== -->
        <section x-show="activeRole === 'parent' || (searchQuery !== '' && matches('Orang Tua Wali Murid Notifikasi WhatsApp WhatsApp Resmi Pantau Nilai'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-700 flex items-center justify-center font-black border border-rose-100">
                        <span class="material-symbols-outlined text-2xl">family_restroom</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Orang Tua / Wali Murid</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Penerimaan notifikasi WhatsApp otomatis, akses portal rapor anak, dan verifikasi.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Peran: Orang Tua Siswa</span>
            </div>

            <div class="mt-8 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white font-black text-xs flex items-center justify-center mb-3">1</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Terima Pesan WhatsApp Resmi</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Saat rapor dipublikasikan oleh sekolah, Anda otomatis menerima pesan WhatsApp berisi ringkasan nilai, presensi, dan tautan rapor digital.</p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white font-black text-xs flex items-center justify-center mb-3">2</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Buka Tautan Rapor Digital</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Klik tautan resmi yang tertera pada pesan WA atau login ke aplikasi HadirYuk Orang Tua untuk meninjau rincian nilai tiap mata pelajaran.</p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white font-black text-xs flex items-center justify-center mb-3">3</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Simpan Rapor Resmi A4</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Unduh salinan resmi rapor digital berformat A4 lengkap dengan rekapitulasi kehadiran dan tanda tangan digital terverifikasi.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 8. VERIFIKASI DOKUMEN PUBLIK ==================== -->
        <section x-show="activeRole === 'verification' || (searchQuery !== '' && matches('Verifikasi QR Code Validasi Dokumen Publik Bebas Login Hash SHA-256'))"
                 class="role-guide-panel bg-white rounded-3xl border border-brand-border p-6 sm:p-8 shadow-sm mb-10 transition">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-brand-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center font-black border border-purple-100">
                        <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-brand-dark">Panduan Verifikasi Keaslian Dokumen Publik</h2>
                        <p class="text-xs sm:text-sm text-brand-muted mt-0.5">Validasi keaslian rapor secara instan via pemindaian QR Code tanpa perlu akun.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">Akses: Terbuka untuk Umum</span>
            </div>

            <div class="mt-6 bg-purple-50/50 rounded-2xl p-4 border border-purple-100 text-xs sm:text-sm text-brand-dark leading-relaxed">
                <strong>Prinsip Keamanan Kriptografi:</strong> Setiap lembar rapor resmi yang diterbitkan memiliki <code>verification_hash</code> 64-karakter SHA-256 yang unik. Siapa pun dapat memvalidasi keaslian dokumen tanpa meminta legalisir cap basah.
            </div>

            <div class="mt-8 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center mb-3">1</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Pindai QR Code di Rapor</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Gunakan kamera smartphone atau aplikasi pemindai QR untuk memindai kode verifikasi pada pojok lembar cetak rapor fisik.</p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center mb-3">2</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Akses Laman Verifikasi Resmi</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Browser akan langsung membuka endpoint <code>/verify-report/{hash}</code> tanpa meminta login maupun instalasi aplikasi apa pun.</p>
                    </div>

                    <div class="border border-brand-border rounded-2xl p-5">
                        <div class="w-8 h-8 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center mb-3">3</div>
                        <h4 class="font-extrabold text-sm text-brand-dark mb-1">Periksa Keaslian Dokumen</h4>
                        <p class="text-xs text-brand-muted leading-relaxed">Halaman menampilkan badge <em>"DOKUMEN RESMI TERVERIFIKASI"</em>, identitas sekolah, nama siswa, tanggal rilis, dan ringkasan nilai yang sah.</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Bottom CTA Bar (Screen Only) -->
    <section class="no-print bg-white border-t border-brand-border py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-xl sm:text-2xl font-black text-brand-dark">
                Siap Menggunakan Modul Rapor HadirYuk?
            </h3>
            <p class="text-xs sm:text-sm text-brand-muted mt-1 max-w-md mx-auto">
                Coba pengalaman langsung pengelolaan nilai sekolah secara modern, otomatis, dan bebas hambatan.
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('login') }}"
                    class="px-6 py-3 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
                    <span>Coba Dasbor Akun Demo</span>
                </a>
                <a href="/"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold rounded-xl transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    <span>Kembali ke Halaman Depan</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 bg-brand-bg border-t border-brand-border text-center text-xs text-brand-muted">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="HadirYuk" class="h-6 w-auto">
                <span class="font-extrabold text-sm text-brand-dark">Hadir<span class="text-brand-primary">Yuk</span></span>
                <span class="text-gray-400">&bull;</span>
                <span>Pusat Panduan & Dokumentasi Rapor</span>
            </div>
            <p>&copy; 2026 PT Thortech Asia. Seluruh hak cipta dilindungi undang-undang.</p>
        </div>
    </footer>

</body>
</html>
