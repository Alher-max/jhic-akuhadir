<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if(in_array(auth()->user()->role, ['operator', 'admin_dapodik', 'admin']))
                {{ __('Dasbor Operator Sekolah') }}
            @elseif(in_array(auth()->user()->role, ['teacher', 'wali_kelas', 'guru', 'guru_mapel']))
                {{ __('Dasbor Guru / Wali Kelas') }}
            @elseif(in_array(auth()->user()->role, ['headmaster', 'kepala_sekolah']))
                {{ __('Dasbor Kepala Sekolah') }}
            @else
                {{ __('Dasbor Utama') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showBannerModal: false, codeCopied: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(auth()->user()->tenant)
            @php
                $bannerColor = $tenant->banner_color ?? 'red';
                $gradientClass = match($bannerColor) {
                    'blue' => 'from-blue-700 to-blue-900',
                    'green' => 'from-emerald-600 to-emerald-800',
                    'slate' => 'from-slate-700 to-slate-900',
                    default => 'from-red-600 to-red-700'
                };
                $gradientStyle = match($bannerColor) {
                    'blue' => 'rgba(29, 78, 216, 0.93) 0%, rgba(30, 58, 138, 0.82) 100%',
                    'green' => 'rgba(5, 150, 105, 0.93) 0%, rgba(6, 78, 59, 0.82) 100%',
                    'slate' => 'rgba(51, 65, 85, 0.93) 0%, rgba(15, 23, 42, 0.82) 100%',
                    default => 'rgba(184, 29, 36, 0.93) 0%, rgba(150, 15, 20, 0.82) 100%'
                };
            @endphp
            <div class="mb-8 rounded-2xl border border-white/10 shadow-lg overflow-hidden {{ $tenant->banner_path ? '' : 'bg-gradient-to-br ' . $gradientClass }} p-5 lg:p-6 text-white"
                 @if($tenant->banner_path)
                    style="background: linear-gradient(135deg, {{ $gradientStyle }}), url('{{ asset('storage/' . $tenant->banner_path) }}') center/cover no-repeat;"
                 @endif
            >
                <!-- ===== BARIS ATAS: Header + Kontrol ===== -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 pb-4 border-b border-white/15">

                    <!-- KIRI: Judul & Deskripsi -->
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl lg:text-2xl font-extrabold tracking-tight text-white mb-1.5 flex flex-col sm:flex-row sm:items-center gap-2">
                            <span>{{ $tenant->banner_title ?: 'Selamat datang, ' . auth()->user()->name . '!' }}</span>
                            @if(in_array(auth()->user()->role, ['operator', 'admin_dapodik', 'admin']))
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white/20 border border-white/30 text-xs font-semibold backdrop-blur-sm shadow-sm">
                                    <i class="fa-solid fa-user-gear text-amber-300"></i> Operator Sekolah
                                </span>
                            @elseif(in_array(auth()->user()->role, ['guru', 'wali_kelas', 'teacher', 'guru_mapel']))
                                @php $homerooms = auth()->user()->homeroomClasses; @endphp
                                @if($homerooms && $homerooms->count() > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white/20 border border-white/30 text-xs font-semibold backdrop-blur-sm shadow-sm">
                                        <i class="fa-solid fa-star text-amber-300"></i> Wali Kelas: {{ $homerooms->pluck('nama_kelas')->implode(', ') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white/10 border border-white/20 text-xs font-semibold backdrop-blur-sm text-white/90 shadow-sm">
                                        <i class="fa-solid fa-chalkboard-user text-white/80"></i> Guru Pengajar
                                    </span>
                                @endif
                            @endif
                        </h2>
                        @if(in_array(auth()->user()->role, ['operator', 'admin_dapodik', 'admin']))
                            <p class="text-white/90 text-sm font-semibold mt-1">Dasbor Operasional & Admin Dapodik — {{ auth()->user()->tenant->name ?? 'Sekolah Anda' }}</p>
                            <p class="text-white/70 text-xs leading-relaxed mt-0.5 max-w-xl">
                                {{ $tenant->banner_description ?: 'Kelola data siswa, pendidik, perangkat presensi, dan konfigurasi operasional sekolah secara terpusat.' }}
                            </p>
                        @elseif(in_array(auth()->user()->role, ['guru', 'wali_kelas', 'teacher', 'guru_mapel']))
                            <p class="text-white/80 text-sm">Dasbor Guru & Tenaga Pendidik</p>
                        @else
                            <p class="text-white/80 text-sm font-medium">{{ auth()->user()->tenant->name ?? 'Sekolah Anda' }}</p>
                            <p class="text-white/65 text-xs leading-relaxed mt-0.5 max-w-xl">
                                {{ $tenant->banner_description ?: 'Kelola pengguna, pantau statistik absensi, dan atur konfigurasi operasional secara terpusat.' }}
                            </p>
                        @endif
                    </div>

                    <!-- KANAN: Badge Kode Sekolah + Ubah Banner (selalu tampil, tanpa x-data nested) -->
                    <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
                        <!-- Badge Kode Sekolah dengan Click-to-Copy -->
                        <div @click="navigator.clipboard.writeText('{{ $tenant->code ?? auth()->user()->tenant->code ?? 'SCH-001' }}'); codeCopied = true; setTimeout(() => codeCopied = false, 2000)"
                             class="bg-black/30 hover:bg-black/40 border border-white/20 px-3.5 py-2 rounded-xl text-xs font-mono flex items-center gap-2 backdrop-blur-sm cursor-pointer transition-all group relative"
                             title="Klik untuk menyalin Kode Sekolah">
                            <span class="text-white/70">Kode Sekolah:</span>
                            <strong class="text-amber-300 tracking-widest text-sm font-bold">{{ $tenant->code ?? auth()->user()->tenant->code ?? 'SCH-001' }}</strong>
                            <span x-show="!codeCopied" class="text-white/60 group-hover:text-amber-300 transition-colors ml-0.5">
                                <i class="fa-regular fa-copy text-xs"></i>
                            </span>
                            <span x-show="codeCopied" x-cloak class="text-emerald-400 font-extrabold flex items-center gap-1 text-2xs animate-pulse ml-0.5">
                                <i class="fa-solid fa-check"></i> Disalin!
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ===== BARIS BAWAH: Tombol Pintas Glassmorphism Berdasarkan Role ===== -->
                @php
                    $roleLower = strtolower(auth()->user()->role ?? '');
                    $isTeacherRole = in_array($roleLower, ['wali_kelas', 'guru', 'guru_mapel']);
                @endphp

                <div class="mt-3 flex flex-wrap items-center gap-2.5">
                    @if($isTeacherRole)
                        <!-- Tombol Pintas Khusus Wali Kelas / Guru -->
                        <a href="{{ route('attendances.index') }}" class="h-9 bg-white/90 hover:bg-white text-red-700 backdrop-blur-md font-semibold px-4 rounded-xl shadow-sm transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-clipboard-user"></i> <span class="leading-none">Input Presensi Kelas</span>
                        </a>
                        <a href="{{ route('students.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-user-graduate"></i> <span class="leading-none">Siswa Binaan</span>
                        </a>
                        <a href="{{ route('class-schedules.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-calendar-days"></i> <span class="leading-none">Jadwal</span>
                        </a>
                    @else
                        <!-- Tombol Pintas Khusus Operator / Admin Sekolah (Urutan Alfabetis: Guru, Jadwal, Kelas, Orang Tua, Pengaturan, Siswa) -->
                        <a href="{{ route('operator.teachers.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-chalkboard-user"></i> <span class="leading-none">Guru</span>
                        </a>
                        <a href="{{ route('class-schedules.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-calendar-days"></i> <span class="leading-none">Jadwal</span>
                        </a>
                        <a href="{{ route('operator.classes.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-chalkboard"></i> <span class="leading-none">Kelas</span>
                        </a>
                        <a href="{{ route('operator.parents.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-users-line"></i> <span class="leading-none">Orang Tua</span>
                        </a>
                        <a href="{{ route('attendance-settings.index') }}" class="h-9 bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-medium px-4 rounded-xl transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-sliders"></i> <span class="leading-none">Pengaturan</span>
                        </a>
                        <a href="{{ route('students.index') }}" class="h-9 bg-white/90 hover:bg-white text-red-700 backdrop-blur-md font-semibold px-4 rounded-xl shadow-sm transition-all inline-flex items-center justify-center gap-2 text-sm leading-none">
                            <i class="fa-solid fa-user-graduate"></i> <span class="leading-none">Siswa</span>
                        </a>
                    @endif
                </div>

            </div>

            <!-- Modal Banner -->
            <div x-show="showBannerModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="showBannerModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">📸 Ubah Foto Banner Sekolah</h3>
                        <button @click="showBannerModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>
                    <form action="{{ route('operator.dashboard.banner') }}" method="POST" enctype="multipart/form-data" class="p-6">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Banner</label>
                            <input type="text" name="banner_title" value="{{ $tenant->banner_title }}" placeholder="Contoh: Selamat datang, Budi!" class="w-full rounded-lg border-gray-300 text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Sub-judul / Deskripsi</label>
                            <textarea name="banner_description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:ring-brand-primary focus:border-brand-primary" placeholder="Tuliskan deskripsi ringkas...">{{ $tenant->banner_description }}</textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Skema Warna</label>
                            <select name="banner_color" class="w-full rounded-lg border-gray-300 text-sm focus:ring-brand-primary focus:border-brand-primary">
                                <option value="red" {{ ($tenant->banner_color ?? 'red') === 'red' ? 'selected' : '' }}>Merah Maroon (Default)</option>
                                <option value="blue" {{ $tenant->banner_color === 'blue' ? 'selected' : '' }}>Biru Navy</option>
                                <option value="green" {{ $tenant->banner_color === 'green' ? 'selected' : '' }}>Hijau Emerald</option>
                                <option value="slate" {{ $tenant->banner_color === 'slate' ? 'selected' : '' }}>Dark Slate</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Foto Banner (Max 3MB, Opsional)</label>
                            <input type="file" name="banner_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl p-3 mt-3">
                                <ul class="space-y-1">
                                    <li>📐 Rekomendasi Ukuran: 1200 x 400 px (Rasio 3:1)</li>
                                    <li>📁 Format: JPG, PNG, atau WEBP (Maks. 3 MB)</li>
                                    <li>💡 Tips: Gunakan foto lanskap horizontal dengan fokus objek di area tengah.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" @click="showBannerModal = false" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-brand-primary rounded-xl hover:bg-red-700 transition-colors">Simpan Banner</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                @if(auth()->user()->role === 'wali_kelas')
                    <!-- KOTAK 1: SISWA KELAS SAYA -->
                    <div class="bg-brand-surface rounded-xl shadow-sm border border-brand-border p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Siswa Kelas Saya</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $waliTotalSiswa ?? 0 }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">{{ $waliClassName ?? 'Belum Ada Kelas Binaan' }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                            <i class="fa-solid fa-users text-xl"></i>
                        </div>
                    </div>

                    <!-- KOTAK 2: HADIR HARI INI -->
                    <div class="bg-brand-surface rounded-xl shadow-sm border border-brand-border p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hadir Hari Ini</p>
                            <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $waliHadirHariIni ?? 0 }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">Sudah Presensi Masuk</p>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                            <i class="fa-solid fa-check-circle text-xl"></i>
                        </div>
                    </div>

                    <!-- KOTAK 3: IZIN & SAKIT -->
                    <div class="bg-brand-surface rounded-xl shadow-sm border border-brand-border p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Izin & Sakit</p>
                            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $waliIzinSakit ?? 0 }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">Menunggu / Disetujui</p>
                        </div>
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                            <i class="fa-solid fa-file-medical text-xl"></i>
                        </div>
                    </div>

                    <!-- KOTAK 4: BELUM ABSEN / ALPHA -->
                    <div class="bg-brand-surface rounded-xl shadow-sm border border-brand-border p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Belum Absen / Alpha</p>
                            <p class="text-3xl font-bold text-rose-600 mt-1">{{ $waliBelumAbsen ?? 0 }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">Butuh Tindak Lanjut</p>
                        </div>
                        <div class="p-3 bg-rose-50 text-rose-600 rounded-lg">
                            <i class="fa-solid fa-user-xmark text-xl"></i>
                        </div>
                    </div>
                @else
                    <!-- NEW OPERATOR DASHBOARD LAYOUT -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 col-span-full">
                        
                        <!-- KOLOM KIRI: METRIK OPERASIONAL -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- METRIK SISWA -->
                            <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-5">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-users text-brand-primary"></i> Live Snapshot Siswa</h3>
                                    <span class="text-xs text-gray-500">Total: {{ $totalSiswa }} Terdaftar</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="bg-emerald-50 rounded-xl p-3 text-center border border-emerald-100">
                                        <p class="text-[10px] sm:text-xs text-emerald-700 font-semibold mb-1">Tepat Waktu</p>
                                        <p class="text-2xl font-bold text-emerald-600">{{ $opSiswaHadirTepat }}</p>
                                    </div>
                                    <div class="bg-amber-50 rounded-xl p-3 text-center border border-amber-100">
                                        <p class="text-[10px] sm:text-xs text-amber-700 font-semibold mb-1">Terlambat</p>
                                        <p class="text-2xl font-bold text-amber-600">{{ $opSiswaTerlambat }}</p>
                                    </div>
                                    <div class="bg-blue-50 rounded-xl p-3 text-center border border-blue-100">
                                        <p class="text-[10px] sm:text-xs text-blue-700 font-semibold mb-1">Izin / Sakit</p>
                                        <p class="text-2xl font-bold text-blue-600">{{ $opSiswaIzinSakit }}</p>
                                    </div>
                                    <div class="bg-rose-50 rounded-xl p-3 text-center border border-rose-100">
                                        <p class="text-[10px] sm:text-xs text-rose-700 font-semibold mb-1">Alpa / Belum</p>
                                        <p class="text-2xl font-bold text-rose-600">{{ $opSiswaAlpa }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- METRIK GURU -->
                            <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-5">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-chalkboard-user text-brand-primary"></i> Kehadiran Pendidik & Tenaga Kependidikan</h3>
                                    <span class="text-xs text-gray-500">Total: {{ $totalGuruStaff }} Guru/Staf</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner"><i class="fa-solid fa-check"></i></div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-700">Hadir / KBM</p>
                                                <p class="text-[10px] text-gray-500">Telah Presensi Masuk</p>
                                            </div>
                                        </div>
                                        <span class="text-2xl font-bold text-emerald-600">{{ $opGuruHadir }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shadow-inner"><i class="fa-solid fa-user-xmark"></i></div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-700">Absen / Izin</p>
                                                <p class="text-[10px] text-gray-500">Belum Presensi / Izin</p>
                                            </div>
                                        </div>
                                        <span class="text-2xl font-bold text-rose-600">{{ $totalGuruStaff - $opGuruHadir }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: ACTION ITEMS & SYSTEM HEALTH -->
                        <div class="space-y-6">
                            
                            <!-- TUGAS PENDING -->
                            <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-5">
                                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-clipboard-list text-brand-primary"></i> Antrean Tugas Operator</h3>
                                
                                <div class="space-y-3">
                                    @if($opPendingInvitations > 0)
                                    <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                                        <i class="fa-solid fa-user-clock text-blue-500 mt-0.5"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-800">Verifikasi Akun</p>
                                            <p class="text-xs text-blue-600">{{ $opPendingInvitations }} undangan/akun tertunda.</p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="flex items-center gap-2 text-sm text-gray-500 py-2">
                                        <i class="fa-solid fa-check-circle text-emerald-500"></i> Tidak ada antrean pendaftaran.
                                    </div>
                                    @endif

                                    @if($opRombelKosong > 0)
                                    <div class="flex items-start gap-3 p-3 bg-rose-50 border border-rose-100 rounded-xl">
                                        <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-rose-800">Rombel Kosong</p>
                                            <p class="text-xs text-rose-600">{{ $opRombelKosong }} rombel perlu ditugaskan wali kelas.</p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="flex items-center gap-2 text-sm text-gray-500 py-2">
                                        <i class="fa-solid fa-check-circle text-emerald-500"></i> Semua rombel memiliki wali kelas.
                                    </div>
                                    @endif

                                    <!-- Tiket Bantuan Kendala Operator -->
                                    @if(($opPendingTickets ?? 0) > 0)
                                    <a href="{{ route('operator.support-tickets.index') }}" class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl hover:bg-amber-100/70 transition mt-2">
                                        <i class="fa-solid fa-headset text-amber-500 mt-0.5"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-amber-800">Tiket Bantuan Kendala</p>
                                            <p class="text-xs text-amber-600 font-medium">{{ $opPendingTickets }} permintaan bantuan mengantre.</p>
                                        </div>
                                    </a>
                                    @else
                                    <a href="{{ route('operator.support-tickets.index') }}" class="flex items-center gap-2 text-sm text-gray-500 py-2 border-t border-gray-100 mt-2 hover:text-brand-primary transition">
                                        <i class="fa-solid fa-check-circle text-emerald-500"></i> 0 Permintaan Bantuan Kendala.
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <!-- SYSTEM HEALTH / STATUS SISTEM & ALAT PRESENSI -->
                            <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                        <i class="fa-solid fa-heart-pulse text-brand-primary"></i> Status Sistem & Alat
                                    </h3>
                                    <a href="{{ route('attendance-settings.index') }}" class="text-[11px] font-semibold text-brand-primary hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-sliders text-xs"></i> Kelola
                                    </a>
                                </div>

                                <div class="space-y-2.5">
                                    <!-- 1. Aplikasi (Mobile PWA) -->
                                    <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ ($sysPwaActive ?? false) ? 'bg-emerald-500 shadow-xs' : 'bg-gray-300' }}"></div>
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <i class="fa-solid fa-mobile-screen-button text-xs {{ ($sysPwaActive ?? false) ? 'text-sky-600' : 'text-gray-400' }}"></i>
                                                Aplikasi (PWA & GPS)
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border {{ ($sysPwaActive ?? false) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-gray-500 bg-gray-100 border-gray-200' }}">
                                            {{ ($sysPwaActive ?? false) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <!-- 2. RFID / Tap Card -->
                                    <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ ($sysRfidActive ?? false) ? 'bg-emerald-500 shadow-xs' : 'bg-gray-300' }}"></div>
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <i class="fa-regular fa-id-card text-xs {{ ($sysRfidActive ?? false) ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                                                RFID / Tap Card
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border {{ ($sysRfidActive ?? false) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-gray-500 bg-gray-100 border-gray-200' }}">
                                            {{ ($sysRfidActive ?? false) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <!-- 3. QR Code Scanner -->
                                    <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ ($sysQrcodeActive ?? false) ? 'bg-emerald-500 shadow-xs' : 'bg-gray-300' }}"></div>
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <i class="fa-solid fa-qrcode text-xs {{ ($sysQrcodeActive ?? false) ? 'text-emerald-600' : 'text-gray-400' }}"></i>
                                                QR Code Scanner
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border {{ ($sysQrcodeActive ?? false) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-gray-500 bg-gray-100 border-gray-200' }}">
                                            {{ ($sysQrcodeActive ?? false) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <!-- 4. Mesin Biometrik -->
                                    <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ ($sysBiometricActive ?? false) ? 'bg-emerald-500 shadow-xs' : 'bg-gray-300' }}"></div>
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <i class="fa-solid fa-fingerprint text-xs {{ ($sysBiometricActive ?? false) ? 'text-amber-600' : 'text-gray-400' }}"></i>
                                                Mesin Biometrik
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border {{ ($sysBiometricActive ?? false) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-gray-500 bg-gray-100 border-gray-200' }}">
                                            {{ ($sysBiometricActive ?? false) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <!-- 6. WA Notif API -->
                                    <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 transition border-t border-gray-100 pt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full {{ ($sysWaReady ?? false) ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300' }}"></div>
                                            <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                                <i class="fa-brands fa-whatsapp text-xs {{ ($sysWaReady ?? false) ? 'text-emerald-600' : 'text-gray-400' }}"></i>
                                                WA Notif API
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border {{ ($sysWaReady ?? false) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-gray-500 bg-gray-100 border-gray-200' }}">
                                            {{ ($sysWaReady ?? false) ? 'Terhubung' : 'Offline' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Attendance Table -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900 whitespace-nowrap">Log Kehadiran Hari Ini</h3>
                    <form method="GET" action="" class="flex flex-col sm:flex-row w-full sm:w-auto items-center gap-2">
                        <select name="class_id" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg focus:ring-brand-primary focus:border-brand-primary w-full sm:w-48">
                            <option value="">Semua Kelas</option>
                            @if(isset($availableClasses))
                                @foreach($availableClasses as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->nama_kelas }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="relative w-full sm:w-64">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa..." class="w-full pl-9 pr-3 text-sm py-2 border border-gray-300 rounded-lg focus:ring-brand-primary focus:border-brand-primary">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                        <noscript><button type="submit" class="px-3 py-2 bg-gray-200 rounded-lg text-sm">Cari</button></noscript>
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-medium">Nama Anggota</th>
                                <th class="px-6 py-4 font-medium">Kelas / Rombel</th>
                                <th class="px-6 py-4 font-medium">Email</th>
                                <th class="px-6 py-4 font-medium">Jam Masuk</th>
                                <th class="px-6 py-4 font-medium">Jam Pulang</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($attendances as $attendance)
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $attendance->user->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $attendance->user->schoolClass->nama_kelas ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $attendance->user->email ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if($attendance->clock_in)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($attendance->clock_out)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <p class="text-base font-medium text-gray-900">Belum ada riwayat absensi</p>
                                        <p class="text-sm mt-1">Belum ada log presensi untuk kelas ini hari ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>


    </div>
</x-app-layout>
