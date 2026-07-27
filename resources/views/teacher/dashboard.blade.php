<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-chalkboard-user text-red-600"></i>
            <span>{{ __('Dasbor Guru & Wali Kelas') }}</span>
        </h2>
    </x-slot>

    <div class="py-8" x-data="{
        openBantuAbsen: false,
        openPhotoModal: false,
        previewPhotoUrl: '',
        previewPhotoName: '',
        selectedStudentId: '',
        students: {{ json_encode(($studentsForBantuAbsen ?? collect())->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nisn' => $s->nisn ?: $s->nis ?: '-',
                'class_name' => optional($s->schoolClass)->full_name ?: 'Kelas Binaan',
                'avatar_url' => $s->avatar ? asset('storage/' . $s->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=f87171&color=fff',
            ];
        })->values()) }},
        getSelectedStudent() {
            return this.students.find(s => s.id == this.selectedStudentId) || null;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- ===== BANNER SAMBUTAN WALI KELAS & GURU ===== -->
            @if(auth()->user()->tenant)
            @php
                $tenant = $tenant ?? auth()->user()->tenant;
                $bannerColor = $tenant->banner_color ?? 'red';
                $gradientClass = match($bannerColor) {
                    'blue' => 'from-blue-700 to-blue-900',
                    'green' => 'from-emerald-600 to-emerald-800',
                    'slate' => 'from-slate-700 to-slate-900',
                    default => 'from-red-600 to-red-700'
                };
                $homerooms = auth()->user()->homeroomClasses;
            @endphp
            <div class="rounded-2xl border border-white/10 shadow-lg bg-gradient-to-br {{ $gradientClass }} p-6 text-white relative overflow-hidden">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-white/15 pb-5 mb-4">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-white flex flex-wrap items-center gap-2 mb-1.5">
                            <span>Selamat datang, {{ auth()->user()->name }}!</span>
                            @if($homerooms && $homerooms->count() > 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-300 text-xs font-semibold backdrop-blur-sm shadow-sm">
                                    <i class="fa-solid fa-star"></i> Wali Kelas: {{ $homerooms->map(fn($c) => $c->full_name)->implode(', ') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-white/90 text-xs font-medium backdrop-blur-sm">
                                    <i class="fa-solid fa-graduation-cap"></i> Guru Pengajar
                                </span>
                            @endif
                        </h2>
                        <p class="text-white/80 text-sm font-medium">
                            {{ $tenant->name ?? 'Sekolah' }} — Pantau kedisiplinan siswa, proses pengajuan izin, dan kelola aktivitas pembelajaran kelas.
                        </p>
                    </div>

                    <!-- Kode Sekolah Badge -->
                    <div class="bg-black/30 border border-white/20 px-3.5 py-2 rounded-xl text-xs font-mono flex items-center gap-2 backdrop-blur-sm">
                        <span class="text-white/70">Kode Sekolah:</span>
                        <strong class="text-amber-300 tracking-widest text-sm">{{ $tenant->code ?? 'SCH-001' }}</strong>
                    </div>
                </div>

                <!-- Tombol Pintas Akses Cepat Guru -->
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.leaves.index') }}" class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-envelope-open-text text-slate-900"></i> Persetujuan Izin Siswa
                        @if(($pendingLeavesCount ?? 0) > 0)
                            <span class="ml-1 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full font-extrabold animate-pulse">
                                {{ $pendingLeavesCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('students.index') }}" class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-users"></i> Siswa Binaan
                    </a>

                    <a href="{{ route('class-schedules.index') }}" class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-calendar-days"></i> Jadwal Pelajaran (KBM)
                    </a>

                    <button type="button" @click="openBantuAbsen = true" class="bg-white hover:bg-slate-100 text-red-700 font-bold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2 text-sm cursor-pointer">
                        <i class="fa-solid fa-user-check text-red-600"></i> + Bantu Absen
                    </button>
                </div>
            </div>
            @endif

            <!-- ===== KARTU RINGKASAN STATISTIK KELAS BINAAN ===== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Siswa Binaan -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Siswa Binaan</p>
                        <h3 class="text-2xl font-extrabold text-slate-800">{{ $waliTotalSiswa ?? $totalSiswa ?? 0 }}</h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">{{ $waliClassName ?? 'Kelas Binaan' }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>

                <!-- Card 2: Siswa Hadir Hari Ini -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Siswa Hadir Hari Ini</p>
                        <h3 class="text-2xl font-extrabold text-emerald-600">{{ $waliHadirHariIni ?? 0 }}</h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Telah presensi hari ini</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <!-- Card 3: Siswa Izin / Sakit -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Siswa Izin / Sakit</p>
                        <h3 class="text-2xl font-extrabold text-amber-600">{{ $waliIzinSakit ?? 0 }}</h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">Memiliki surat / dispensasi</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                </div>

                <!-- Card 4: Permohonan Izin Menunggu -->
                <a href="{{ route('admin.leaves.index') }}" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between hover:border-amber-400 transition-all group">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Persetujuan Izin</p>
                        <div class="flex items-center gap-2">
                            <h3 class="text-2xl font-extrabold text-red-600">{{ $pendingLeavesCount ?? 0 }}</h3>
                            <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-md">Pending</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1 font-medium group-hover:text-red-600 transition-colors">Klik untuk memproses &rarr;</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold group-hover:bg-red-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                </a>

            </div>

            <!-- ===== TABEL FEED LOG PRESENSI HARI INI ===== -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-red-600"></i>
                            <span>Catatan Kehadiran Siswa Hari Ini</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh rekaman presensi masuk/keluar siswa pada hari ini.</p>
                    </div>
                    <a href="{{ route('attendances.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700 flex items-center gap-1">
                        <span>Lihat Semua Log</span>
                        <i class="fa-solid fa-chevron-right text-2xs"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="px-6 py-3.5 font-bold">Waktu</th>
                                <th class="px-6 py-3.5 font-bold">Nama Siswa</th>
                                <th class="px-6 py-3.5 font-bold text-center">Foto Wajah</th>
                                <th class="px-6 py-3.5 font-bold">Kelas</th>
                                <th class="px-6 py-3.5 font-bold">Status Presensi</th>
                                <th class="px-6 py-3.5 font-bold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($attendances as $attendance)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-3.5 text-xs font-mono font-semibold text-slate-600">
                                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '--:--' }}
                                    </td>
                                    <td class="px-6 py-3.5 font-medium text-slate-900">
                                        {{ optional($attendance->user)->name ?? 'Unknown' }}
                                    </td>
                                    <!-- FOTO BUKTI / WAJAH -->
                                    <td class="px-6 py-3.5 text-center">
                                        @php
                                            $photoUrl = null;
                                            if (!empty($attendance->photo_path)) {
                                                $photoUrl = Storage::url($attendance->photo_path);
                                            } elseif (optional($attendance->user)->avatar || optional($attendance->user)->master_photo) {
                                                $photoUrl = Storage::url(optional($attendance->user)->avatar ?: optional($attendance->user)->master_photo);
                                            }
                                        @endphp

                                        @if($photoUrl)
                                            <button type="button" 
                                                    @click="previewPhotoUrl = '{{ $photoUrl }}'; previewPhotoName = '{{ addslashes(optional($attendance->user)->name ?? '') }}'; openPhotoModal = true" 
                                                    class="group relative inline-block focus:outline-none cursor-pointer"
                                                    title="Klik untuk lihat foto bukti berukuran penuh">
                                                <img src="{{ $photoUrl }}" 
                                                     alt="{{ optional($attendance->user)->name }}" 
                                                     class="w-10 h-10 rounded-xl object-cover border-2 border-slate-200 shadow-sm group-hover:border-red-500 group-hover:scale-105 transition-all mx-auto">
                                                <span class="absolute inset-0 rounded-xl bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-2xs transition-opacity">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </span>
                                            </button>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-bold mx-auto" title="Belum ada foto">
                                                <i class="fa-solid fa-user text-slate-300"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-slate-500">
                                        {{ optional(optional($attendance->user)->schoolClass)->nama_kelas ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex flex-col items-start gap-1">
                                            <div>
                                                @if($attendance->status === 'present')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i class="fa-solid fa-circle-check text-2xs"></i> Hadir Tepat
                                                    </span>
                                                @elseif($attendance->status === 'late')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        <i class="fa-solid fa-clock text-2xs"></i> Terlambat
                                                    </span>
                                                @elseif(in_array($attendance->status, ['sick', 'permission', 'leave']))
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                        <i class="fa-solid fa-hospital-user text-2xs"></i> Izin / Sakit
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                        {{ ucfirst($attendance->status) }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if(!in_array($attendance->status, ['sick', 'permission', 'leave']))
                                                @if($attendance->is_wifi_verified)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200" title="Terhubung langsung via Wi-Fi Sekolah (IP: {{ $attendance->ip_address ?: 'Terverifikasi' }})">
                                                        <i class="fa-solid fa-wifi text-3xs"></i> Wi-Fi Sekolah 📶
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-amber-100/90 text-amber-900 border border-amber-300" title="Menggunakan paket data seluler / IP Luar (IP: {{ $attendance->ip_address ?: 'Data Seluler' }})">
                                                        <i class="fa-solid fa-signal text-3xs"></i> Data Seluler 📱
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-slate-500 max-w-xs truncate">
                                        {{ $attendance->notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-clipboard-check text-3xl mb-2 text-slate-300 block"></i>
                                        Belum ada catatan presensi siswa untuk hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($attendances, 'links') && $attendances->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $attendances->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- ===== MODAL BANTU ABSEN (PRESENSI MANUAL GURU) ===== -->
        <div x-show="openBantuAbsen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.outside="openBantuAbsen = false"
                 class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 p-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <i class="fa-solid fa-user-check text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">Bantu Absen Siswa</h3>
                            <p class="text-white/80 text-xs">Pencatatan Presensi Manual oleh Guru / Wali Kelas</p>
                        </div>
                    </div>
                    <button type="button" @click="openBantuAbsen = false" class="text-white/70 hover:text-white transition-colors text-xl font-bold p-1 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Modal Form -->
                <form method="POST" action="{{ route('teacher.manual-attendance') }}" class="p-6 space-y-5">
                    @csrf

                    <!-- Dropdown Pilih Siswa -->
                    <div>
                        <label for="student_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Siswa <span class="text-red-500">*</span>
                        </label>
                        <select id="student_id" name="student_id" x-model="selectedStudentId" class="w-full border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm font-medium" required>
                            <option value="" disabled selected>-- Pilih Siswa Binaan --</option>
                            <template x-for="student in students" :key="student.id">
                                <option :value="student.id" x-text="student.name + ' (' + student.class_name + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- BOX VERIFIKASI FOTO SISWA (Anti-Kecurangan) -->
                    <div x-show="getSelectedStudent()" class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <img :src="getSelectedStudent()?.avatar_url" alt="Foto Siswa" class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-red-100 text-red-700 uppercase tracking-wider mb-1">
                                    <i class="fa-solid fa-id-badge text-2xs"></i> Verifikasi Wajah Siswa
                                </span>
                                <h4 class="font-bold text-slate-800 text-sm truncate" x-text="getSelectedStudent()?.name"></h4>
                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5">
                                    <span>NISN/NIS: <strong class="text-slate-700" x-text="getSelectedStudent()?.nisn"></strong></span>
                                    <span>&bull;</span>
                                    <span>Kelas: <strong class="text-slate-700" x-text="getSelectedStudent()?.class_name"></strong></span>
                                </div>
                            </div>
                        </div>
                        <template x-if="getSelectedStudent()?.id">
                            <form :action="'/teacher/students/' + getSelectedStudent()?.id + '/reset-photo'" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset foto profil siswa ini agar siswa dapat mengunggah foto baru?');">
                                @csrf
                                <button type="submit" class="bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 font-bold px-3 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1 flex-shrink-0 cursor-pointer" title="Reset Foto Profil Siswa">
                                    <i class="fa-solid fa-rotate-left"></i> Reset Foto
                                </button>
                            </form>
                        </template>
                    </div>

                    <!-- Pilihan Status Presensi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Status Presensi <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/50 cursor-pointer font-semibold text-xs text-slate-700">
                                <input type="radio" name="status" value="present" checked class="text-red-600 focus:ring-red-500">
                                <span class="flex items-center gap-1.5 text-emerald-700">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Hadir Tepat
                                </span>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-amber-50/50 cursor-pointer font-semibold text-xs text-slate-700">
                                <input type="radio" name="status" value="late" class="text-red-600 focus:ring-red-500">
                                <span class="flex items-center gap-1.5 text-amber-700">
                                    <i class="fa-solid fa-clock text-amber-600"></i> Terlambat
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Catatan Guru -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alasan / Catatan Guru
                        </label>
                        <input type="text" id="notes" name="notes" placeholder="Contoh: Lupa kartu presensi / HP mati" class="w-full border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button" @click="openBantuAbsen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Simpan Presensi</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ===== MODAL LIGHTBOX PRATINJAU FOTO BUKTI / WAJAH ===== -->
        <div x-show="openPhotoModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            
            <div @click.outside="openPhotoModal = false"
                 class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                
                <!-- Header -->
                <div class="bg-slate-900 p-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-camera text-red-500 text-lg"></i>
                        <div>
                            <h3 class="font-bold text-sm" x-text="previewPhotoName || 'Foto Bukti Presensi'"></h3>
                            <p class="text-2xs text-slate-400">Verifikasi Wajah / Snapshot Clock-In</p>
                        </div>
                    </div>
                    <button type="button" @click="openPhotoModal = false" class="text-slate-400 hover:text-white transition-colors text-xl font-bold p-1 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Body / Full Image -->
                <div class="p-4 bg-slate-900 flex justify-center items-center">
                    <img :src="previewPhotoUrl" alt="Foto Wajah" class="max-h-96 w-full object-contain rounded-2xl border border-slate-800 shadow-lg">
                </div>

                <!-- Footer -->
                <div class="p-3.5 bg-slate-50 border-t border-slate-100 text-center">
                    <button type="button" @click="openPhotoModal = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
