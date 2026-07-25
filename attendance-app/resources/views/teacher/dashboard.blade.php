<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-chalkboard-user text-red-600"></i>
            <span>{{ __('Dasbor Guru & Wali Kelas') }}</span>
        </h2>
    </x-slot>

    <div class="py-8">
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
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-white flex flex-wrap items-center gap-2 mb-1.5">
                            <span>Selamat datang, {{ auth()->user()->name }}!</span>
                            @if($homerooms && $homerooms->count() > 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-300 text-xs font-semibold backdrop-blur-sm shadow-sm">
                                    <i class="fa-solid fa-star"></i> Wali Kelas: {{ $homerooms->pluck('nama_kelas')->implode(', ') }}
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

            <!-- ===== CARD UTAMA AKSI WALI KELAS & GURU ===== -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-red-600"></i>
                        <span>Menu & Fitur Pengelolaan Guru / Wali Kelas</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- MENU 1: PERSETUJUAN IZIN SISWA -->
                    <a href="{{ route('admin.leaves.index') }}" class="p-4 rounded-xl border border-slate-200 hover:border-red-500 hover:shadow-md transition-all flex flex-col justify-between bg-white group">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold group-hover:bg-red-600 group-hover:text-white transition-all">
                                <i class="fa-solid fa-envelope-open-text"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-red-600 transition-colors">Persetujuan Izin Siswa</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Tinjau, setujui, atau tolak surat pengajuan izin dan sakit dari siswa.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-red-600">
                            <span>Buka Pengajuan</span>
                            @if(($pendingLeavesCount ?? 0) > 0)
                                <span class="bg-red-600 text-white px-2 py-0.5 rounded-full text-xs font-bold">{{ $pendingLeavesCount }} pending</span>
                            @else
                                <i class="fa-solid fa-arrow-right"></i>
                            @endif
                        </div>
                    </a>

                    <!-- MENU 2: KELOLA SISWA BINAAN -->
                    <a href="{{ route('students.index') }}" class="p-4 rounded-xl border border-slate-200 hover:border-red-500 hover:shadow-md transition-all flex flex-col justify-between bg-white group">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <i class="fa-solid fa-users text-lg"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-blue-600 transition-colors">Kelola Siswa Binaan</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Lihat daftar siswa, profil data, serta rekaman kehadiran siswa di kelas Anda.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-600">
                            <span>Lihat Siswa</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- MENU 3: KELOLA KELAS & ROMBEL -->
                    <a href="{{ route('operator.classes.index') }}" class="p-4 rounded-xl border border-slate-200 hover:border-red-500 hover:shadow-md transition-all flex flex-col justify-between bg-white group">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <i class="fa-solid fa-school-flag text-lg"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition-colors">Kelola Rombel & Kelas</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Pantau pembagian kelas, anggota rombel, dan struktur kelas binaan.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
                            <span>Kelola Kelas</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- MENU 4: JADWAL KBM & KEGIATAN -->
                    <a href="{{ route('class-schedules.index') }}" class="p-4 rounded-xl border border-slate-200 hover:border-red-500 hover:shadow-md transition-all flex flex-col justify-between bg-white group">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold group-hover:bg-purple-600 group-hover:text-white transition-all">
                                <i class="fa-solid fa-calendar-days text-lg"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-purple-600 transition-colors">Jadwal KBM & Kegiatan</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Atur dan lihat jadwal mata pelajaran, jadwal piket, dan kegiatan sekolah.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-purple-600">
                            <span>Lihat Jadwal</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>

                </div>
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
                                    <td class="px-6 py-3.5 text-xs text-slate-500">
                                        {{ optional(optional($attendance->user)->schoolClass)->nama_kelas ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3.5">
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
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-slate-500 max-w-xs truncate">
                                        {{ $attendance->notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs">
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
    </div>
</x-app-layout>
