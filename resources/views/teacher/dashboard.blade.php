<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-chalkboard-user text-red-600"></i>
            <span>{{ __('Dasbor Guru') }}</span>
        </h2>
    </x-slot>

    <div class="py-8" x-data="{
        codeCopied: false,
        openBantuAbsen: false,
        openKbmModal: false,
        activeKbmSchedule: null,
        openKbmAbsen(scheduleData) {
            this.activeKbmSchedule = scheduleData;
            this.openKbmModal = true;
        },
        openPhotoModal: false,
        previewPhotoUrl: '',
        previewPhotoName: '',
        selectedClassId: '',
        selectedStudentId: '',
        searchStudentQuery: '',
        isStudentDropdownOpen: false,
        allClasses: {{ json_encode((\App\Models\SchoolClass::where('tenant_id', auth()->user()->tenant_id ?? 0)->orderBy('nama_kelas')->get())->map(function ($c) {
    return [
        'id' => $c->id,
        'name' => $c->full_name ?: ('Kelas ' . $c->nama_kelas),
    ];
})->values()) }},
        students: {{ json_encode(($studentsForBantuAbsen ?? collect())->map(function ($s) {
    return [
        'id' => $s->id,
        'name' => $s->name,
        'nisn' => $s->nisn ?: $s->nis ?: '-',
        'class_id' => $s->class_id,
        'class_name' => optional($s->schoolClass)->full_name ?: (optional($s->schoolClass)->nama_kelas ?: 'Tanpa Kelas'),
        'avatar_url' => ($s->avatar || $s->master_photo) ? Storage::url($s->avatar ?: $s->master_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($s->name) . '&background=f87171&color=fff',
    ];
})->values()) }},
        getFilteredStudents() {
            return this.students.filter(s => {
                const matchClass = !this.selectedClassId || s.class_id == this.selectedClassId;
                const q = this.searchStudentQuery.toLowerCase().trim();
                const matchSearch = !q || s.name.toLowerCase().includes(q) || (s.nisn && s.nisn.toLowerCase().includes(q));
                return matchClass && matchSearch;
            });
        },
        getSelectedStudent() {
            return this.students.find(s => s.id == this.selectedStudentId) || null;
        },
        selectStudent(st) {
            this.selectedStudentId = st.id;
            this.searchStudentQuery = st.name + ' (' + st.class_name + ')';
            this.isStudentDropdownOpen = false;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ===== BANNER SAMBUTAN WALI KELAS & GURU ===== -->
            @if(auth()->user()->tenant)
                @php
                    $tenant = $tenant ?? auth()->user()->tenant;
                    $bannerColor = $tenant->banner_color ?? 'red';
                    $gradientClass = match ($bannerColor) {
                        'blue' => 'from-blue-700 to-blue-900',
                        'green' => 'from-emerald-600 to-emerald-800',
                        'slate' => 'from-slate-700 to-slate-900',
                        default => 'from-red-600 to-red-700'
                    };
                    $homerooms = auth()->user()->homeroomClasses;
                @endphp
                <div
                    class="rounded-2xl border border-white/10 shadow-lg bg-gradient-to-br {{ $gradientClass }} p-6 text-white relative overflow-hidden">

                    <!-- BARIS 1: INFORMASI & KODE SEKOLAH -->
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">

                        <!-- SISI KIRI: TEKS & BADGE -->
                        <div class="flex-1 space-y-2.5">
                            <h2
                                class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex flex-wrap items-center gap-2">
                                <span>Selamat datang, {{ auth()->user()->name }}! 👋</span>
                                @php
                                    $homerooms = auth()->user()->homeroomClasses;
                                    if ((!$homerooms || $homerooms->isEmpty()) && auth()->user()->homeroomClass) {
                                        $homerooms = collect([auth()->user()->homeroomClass]);
                                    }
                                    $hasHomeroom = ($homerooms && $homerooms->isNotEmpty()) || (isset($isHomeroom) && $isHomeroom && isset($homeroomClass) && $homeroomClass);
                                    $activeHomeroomClass = ($homerooms && $homerooms->isNotEmpty()) ? $homerooms->first() : ($homeroomClass ?? null);
                                @endphp
                                @if($hasHomeroom && $activeHomeroomClass)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-300 text-xs font-semibold backdrop-blur-sm shadow-sm">
                                        <i class="fa-solid fa-star"></i> Wali Kelas
                                        {{ $activeHomeroomClass->nama_kelas ?: $activeHomeroomClass->full_name }}
                                    </span>
                                @elseif($hasHomeroom)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-300 text-xs font-semibold backdrop-blur-sm shadow-sm">
                                        <i class="fa-solid fa-star"></i> Wali Kelas
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-white/90 text-xs font-medium backdrop-blur-sm">
                                        <i class="fa-solid fa-graduation-cap"></i> Guru Pengajar
                                    </span>
                                @endif
                            </h2>

                            <p class="text-white/80 text-sm font-medium">
                                @if($isHomeroom)
                                    {{ $tenant->name ?? 'Sekolah' }} — Pantau kedisiplinan siswa, proses pengajuan izin, dan
                                    kelola aktivitas pembelajaran kelas binaan Anda.
                                @else
                                    {{ $tenant->name ?? 'Sekolah' }} — Pantau jadwal pelajaran KBM dan bantuan pencatatan
                                    presensi siswa.
                                @endif
                            </p>
                        </div>

                        <!-- SISI KANAN: KODE SEKOLAH SAJA -->
                        <div class="shrink-0">
                            <div @click="navigator.clipboard.writeText('{{ $tenant->code ?? 'SCH-001' }}'); codeCopied = true; setTimeout(() => codeCopied = false, 2000)"
                                class="bg-black/30 hover:bg-black/40 border border-white/20 px-3.5 py-2 rounded-xl text-xs font-mono flex items-center gap-2 backdrop-blur-sm cursor-pointer transition-all group relative"
                                title="Klik untuk menyalin Kode Sekolah">
                                <span class="text-white/70">Kode Sekolah:</span>
                                <strong
                                    class="text-amber-300 tracking-widest text-sm font-bold">{{ $tenant->code ?? 'SCH-001' }}</strong>
                                <span x-show="!codeCopied"
                                    class="text-white/60 group-hover:text-amber-300 transition-colors ml-0.5">
                                    <i class="fa-regular fa-copy text-xs"></i>
                                </span>
                                <span x-show="codeCopied" x-cloak
                                    class="text-emerald-400 font-extrabold flex items-center gap-1 text-2xs animate-pulse ml-0.5">
                                    <i class="fa-solid fa-check"></i> Disalin!
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- BARIS 2: KELOMPOK TOMBOL AKSI -->
                    <div class="mt-6 pt-4 border-t border-white/15 flex flex-wrap items-center gap-3">
                        @if($isHomeroom)
                            <a href="{{ route('admin.leaves.index') }}"
                                class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm shrink-0">
                                <i class="fa-solid fa-envelope-open-text text-amber-300"></i> Persetujuan Izin Siswa
                                @if(($pendingLeavesCount ?? 0) > 0)
                                    <span
                                        class="ml-1 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full font-extrabold animate-pulse">
                                        {{ $pendingLeavesCount }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('students.index', isset($homeroomClass) && $homeroomClass ? ['class_id' => $homeroomClass->id] : []) }}"
                                class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm shrink-0">
                                <i class="fa-solid fa-users text-amber-300"></i> Siswa Binaan
                            </a>
                            <a href="{{ route('teacher.announcements.index') }}"
                                class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm shrink-0">
                                <i class="fa-solid fa-bullhorn text-amber-300"></i> Pengumuman Kelas
                            </a>
                        @endif

                        <a href="{{ route('support-tickets.index') }}"
                            class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm shrink-0">
                            <i class="fa-solid fa-headset text-amber-300"></i> Bantuan Operator
                        </a>

                        <button type="button" @click="openBantuAbsen = true"
                            class="bg-white text-[#B81D24] font-bold px-4 py-2.5 rounded-xl hover:bg-slate-100 shadow-sm transition-all flex items-center gap-2 text-sm cursor-pointer shrink-0">
                            <i class="fa-solid fa-user-check text-[#B81D24]"></i> Bantu Absen
                        </button>
                    </div>

                </div>
            @endif

            <!-- ===== PRIORITAS UTAMA: JADWAL MENGAJAR HARI INI ===== -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm p-6 space-y-5">
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-red-100 text-red-700 border border-red-200">
                                <i class="fa-solid fa-calendar-day me-1"></i> Hari {{ $todayDayName ?? 'Senin' }}
                            </span>
                            <span class="text-xs text-gray-400 font-medium">{{ date('d F Y') }}</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-gray-900 mt-1 flex items-center gap-2">
                            <i class="fa-solid fa-chalkboard-user text-brand-primary"></i> Jadwal Mengajar Hari Ini
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Daftar kelas & mata pelajaran yang dijadwalkan untuk Anda ampu hari ini.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if(in_array(auth()->user()->role, ['operator', 'admin', 'admin_dapodik']))
                            <a href="{{ route('class-schedules.index') }}"
                                class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar-days"></i> Kelola Semua Jadwal
                            </a>
                        @endif
                        <button type="button" @click="openBantuAbsen = true"
                            class="px-4 py-2 bg-brand-primary hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm transition inline-flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-user-check"></i> Bantu Absen Kelas
                        </button>
                    </div>
                </div>

                <!-- Table Schedule -->
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3.5 w-16 text-center">Slot</th>
                                <th class="p-3.5">Waktu / Jam KBM</th>
                                <th class="p-3.5">Mata Pelajaran</th>
                                <th class="p-3.5">Kelas & Rombel</th>
                                <th class="p-3.5">Materi / Topik Pembahasan</th>
                                <th class="p-3.5 text-right">Aksi KBM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            @forelse($todayTeacherSchedules ?? [] as $sch)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="p-3.5 text-center font-extrabold text-gray-900">
                                        <span
                                            class="w-7 h-7 rounded-lg bg-gray-100 border border-gray-200 inline-flex items-center justify-center text-xs">
                                            {{ $sch->period_number }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 whitespace-nowrap">
                                        <span class="font-bold text-gray-900 block">
                                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} WIB
                                        </span>
                                        <span class="text-[10px] text-gray-400">Jam Ke-{{ $sch->period_number }}</span>
                                    </td>
                                    <td class="p-3.5">
                                        <span
                                            class="font-bold text-gray-900 text-sm block">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</span>
                                        <span class="text-[10px] text-gray-400">Kode:
                                            {{ $sch->subject->code ?? '-' }}</span>
                                    </td>
                                    <td class="p-3.5 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-bold text-xs">
                                            {{ $sch->schoolClass->full_name ?? 'Kelas' }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-gray-500 italic">
                                        {{ $sch->topic ?? 'Pengajaran reguler KBM di kelas.' }}
                                    </td>
                                    <td class="p-3.5 text-right whitespace-nowrap">
                                        @php
                                            $existingMap = ($sch->attendances ?? collect())->keyBy('user_id');
                                            $scheduleJson = json_encode([
                                                'id' => $sch->id,
                                                'subject_name' => $sch->subject->name ?? 'Mata Pelajaran',
                                                'subject_code' => $sch->subject->code ?? '-',
                                                'class_name' => $sch->schoolClass->full_name ?? 'Kelas',
                                                'time_range' => substr($sch->start_time, 0, 5) . ' - ' . substr($sch->end_time, 0, 5) . ' WIB',
                                                'period_number' => $sch->period_number,
                                                'date_formatted' => \Carbon\Carbon::now()->isoFormat('D MMMM YYYY'),
                                                'students' => ($sch->schoolClass?->students ?? collect())->map(function ($st) use ($existingMap) {
                                                    $existing = $existingMap->get($st->id);
                                                    $userNote = '';
                                                    if ($existing && $existing->notes) {
                                                        $parts = explode(' | Presensi KBM', $existing->notes);
                                                        $userNote = trim($parts[0]);
                                                        if (str_contains($userNote, 'Presensi KBM')) {
                                                            $userNote = '';
                                                        }
                                                    }
                                                    return [
                                                        'id' => $st->id,
                                                        'name' => $st->name,
                                                        'nisn' => $st->nisn ?: ($st->nis ?: '-'),
                                                        'avatar_url' => ($st->avatar || $st->master_photo)
                                                            ? Storage::url($st->avatar ?: $st->master_photo)
                                                            : 'https://ui-avatars.com/api/?name=' . urlencode($st->name) . '&background=f87171&color=fff',
                                                        'status' => $existing ? $existing->status : 'present',
                                                        'notes' => $userNote,
                                                    ];
                                                })->values(),
                                            ]);
                                        @endphp
                                        <button type="button" @click="openKbmAbsen({{ $scheduleJson }})"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition inline-flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-clipboard-user"></i> Absen Kelas
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400 font-medium">
                                        <i class="fa-solid fa-calendar-xmark text-4xl text-gray-300 mb-2 block"></i>
                                        <span class="font-bold text-gray-600 block text-sm">Tidak Ada Jadwal Mengajar Hari
                                            Ini ({{ $todayDayName ?? 'Senin' }})</span>
                                        <span class="text-xs text-gray-400">Anda tidak memiliki jam KBM terdaftar untuk hari
                                            ini. Silakan periksa jadwal mingguan Anda.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== KOMPONEN SEKUNDER: RINGKASAN REKAP ABSENSI SISWA ===== -->
            @if($isHomeroom)
                <!-- STATISTIK UNTUK WALI KELAS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Card 1: Total Siswa Binaan -->
                    <div
                        class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Siswa Binaan
                            </p>
                            <h3 class="text-2xl font-extrabold text-slate-800">{{ $waliTotalSiswa ?? 0 }}</h3>
                            <p class="text-xs text-slate-400 mt-1 font-medium">{{ $waliClassName ?? 'Kelas Binaan' }}</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                    </div>

                    <!-- Card 2: Siswa Hadir Hari Ini -->
                    <div
                        class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Siswa Hadir Hari
                                Ini</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600">{{ $waliHadirHariIni ?? 0 }}</h3>
                            <p class="text-xs text-slate-400 mt-1 font-medium">Telah presensi hari ini</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>

                    <!-- Card 3: Siswa Izin / Sakit -->
                    <div
                        class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Siswa Izin / Sakit
                            </p>
                            <h3 class="text-2xl font-extrabold text-amber-600">{{ $waliIzinSakit ?? 0 }}</h3>
                            <p class="text-xs text-slate-400 mt-1 font-medium">Memiliki surat / dispensasi</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-hospital-user"></i>
                        </div>
                    </div>

                    <!-- Card 4: Permohonan Izin Menunggu -->
                    <a href="{{ route('admin.leaves.index') }}"
                        class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between hover:border-amber-400 transition-all group">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Persetujuan Izin
                            </p>
                            <div class="flex items-center gap-2">
                                <h3 class="text-2xl font-extrabold text-red-600">{{ $pendingLeavesCount ?? 0 }}</h3>
                                <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-md">Pending</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 font-medium group-hover:text-red-600 transition-colors">
                                Klik untuk memproses &rarr;</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold group-hover:bg-red-600 group-hover:text-white transition-all">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                    </a>
                </div>
            @endif

            <!-- ===== TABEL FEED LOG PRESENSI HARI INI ===== -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div
                    class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-red-600"></i>
                            <span>Catatan Kehadiran Siswa Hari Ini</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Menampilkan rekaman presensi siswa dari kelas yang Anda
                            ampu hari ini.</p>
                    </div>
                    <a href="{{ route('attendances.index') }}"
                        class="text-xs font-semibold text-red-600 hover:text-red-700 flex items-center gap-1">
                        <span>Lihat Semua Log</span>
                        <i class="fa-solid fa-chevron-right text-2xs"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
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
                                                <img src="{{ $photoUrl }}" alt="{{ optional($attendance->user)->name }}"
                                                    class="w-10 h-10 rounded-xl object-cover border-2 border-slate-200 shadow-sm group-hover:border-red-500 group-hover:scale-105 transition-all mx-auto">
                                                <span
                                                    class="absolute inset-0 rounded-xl bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-2xs transition-opacity">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </span>
                                            </button>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-bold mx-auto"
                                                title="Belum ada foto">
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
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i class="fa-solid fa-circle-check text-2xs"></i> Hadir Tepat
                                                    </span>
                                                @elseif($attendance->status === 'late')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        <i class="fa-solid fa-clock text-2xs"></i> Terlambat
                                                    </span>
                                                @elseif(in_array($attendance->status, ['sick', 'permission', 'leave']))
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                        <i class="fa-solid fa-hospital-user text-2xs"></i> Izin / Sakit
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                        {{ ucfirst($attendance->status) }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if(!in_array($attendance->status, ['sick', 'permission', 'leave']))
                                                @if($attendance->is_wifi_verified)
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200"
                                                        title="Terhubung langsung via Perangkat Alat Presensi (IP: {{ $attendance->ip_address ?: 'Terverifikasi' }})">
                                                        <i class="fa-solid fa-tower-broadcast text-3xs"></i> Alat Presensi 📡
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-amber-100/90 text-amber-900 border border-amber-300"
                                                        title="Menggunakan paket data seluler / IP Luar (IP: {{ $attendance->ip_address ?: 'Data Seluler' }})">
                                                        <i class="fa-solid fa-mobile-screen-button text-3xs"></i> Aplikasi PWA 📱
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
        <div x-show="openBantuAbsen" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" style="display: none;"
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
                    <button type="button" @click="openBantuAbsen = false"
                        class="text-white/70 hover:text-white transition-colors text-xl font-bold p-1 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Modal Form -->
                <form method="POST" action="{{ route('teacher.manual-attendance') }}" class="p-6 space-y-5">
                    @csrf

                    <!-- 1. Filter Berdasarkan Kelas / Rombel -->
                    <div>
                        <label for="filter_class_id"
                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>1. Filter Kelas / Rombel</span>
                            <span class="text-[10px] text-slate-400 font-normal">Opsional</span>
                        </label>
                        <select id="filter_class_id" x-model="selectedClassId"
                            @change="selectedStudentId = ''; searchStudentQuery = '';"
                            class="w-full border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm font-medium">
                            <option value="">-- Semua Kelas / Rombel --</option>
                            <template x-for="cls in allClasses" :key="cls.id">
                                <option :value="cls.id" x-text="cls.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- 2. Fitur Pencarian & Searchable Dropdown Siswa -->
                    <div class="relative font-sans" @click.outside="isStudentDropdownOpen = false">
                        <label for="student_search_input"
                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            2. Cari & Pilih Nama Siswa <span class="text-red-500">*</span>
                        </label>

                        <!-- Native Select Synced for Form POST & Automated Test Compatibility -->
                        <select id="student_id" name="student_id" x-model="selectedStudentId" class="sr-only" required>
                            <option value="" disabled>-- Pilih Siswa --</option>
                            <template x-for="student in students" :key="student.id">
                                <option :value="student.id" x-text="student.name + ' (' + student.class_name + ')'">
                                </option>
                            </template>
                        </select>

                        <!-- Search Input Field -->
                        <div class="relative">
                            <input type="text" id="student_search_input" x-model="searchStudentQuery"
                                @focus="isStudentDropdownOpen = true" @click="isStudentDropdownOpen = true"
                                @input="isStudentDropdownOpen = true; selectedStudentId = ''"
                                placeholder="Ketik nama atau NISN siswa..." autocomplete="off"
                                class="w-full pl-10 pr-10 border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm font-medium">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </div>
                            <button type="button" x-show="searchStudentQuery || selectedStudentId"
                                @click="searchStudentQuery = ''; selectedStudentId = ''; isStudentDropdownOpen = true"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                                title="Bersihkan Pencarian">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <!-- Dropdown Results List Container -->
                        <div x-show="isStudentDropdownOpen" x-cloak
                            class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="st in getFilteredStudents()" :key="st.id">
                                <div @click="selectStudent(st)"
                                    class="p-3 hover:bg-red-50/80 cursor-pointer flex items-center justify-between transition-colors select-none">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img :src="st.avatar_url" alt=""
                                            class="w-8 h-8 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 text-sm truncate" x-text="st.name">
                                            </div>
                                            <div class="text-xs text-slate-500" x-text="'NISN/NIS: ' + st.nisn"></div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold whitespace-nowrap flex-shrink-0"
                                        x-text="st.class_name"></span>
                                </div>
                            </template>
                            <div x-show="getFilteredStudents().length === 0"
                                class="p-4 text-center text-slate-400 text-xs font-medium">
                                <i class="fa-solid fa-user-slash text-slate-300 text-xl block mb-1"></i>
                                Siswa tidak ditemukan untuk kriteria ini.
                            </div>
                        </div>
                    </div>

                    <!-- BOX VERIFIKASI FOTO SISWA (Anti-Kecurangan & Pratinjau Identitas) -->
                    <div x-show="getSelectedStudent()" x-cloak
                        class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center gap-4">
                        <img :src="getSelectedStudent()?.avatar_url" alt="Foto Siswa"
                            class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-red-100 text-red-700 uppercase tracking-wider mb-1">
                                <i class="fa-solid fa-id-badge text-2xs"></i> Identitas & Foto Siswa
                            </span>
                            <h4 class="font-bold text-slate-800 text-sm truncate" x-text="getSelectedStudent()?.name">
                            </h4>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 mt-0.5">
                                <span>NISN/NIS: <strong class="text-slate-700"
                                        x-text="getSelectedStudent()?.nisn"></strong></span>
                                <span>&bull;</span>
                                <span>Kelas: <strong class="text-slate-700"
                                        x-text="getSelectedStudent()?.class_name"></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Timestamp Otomatis -->
                    <div
                        class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 flex items-start gap-3 text-xs text-emerald-900">
                        <i class="fa-solid fa-circle-info text-emerald-600 text-sm mt-0.5"></i>
                        <div>
                            <strong class="font-bold block">Pencatatan Jam Otomatis (Audit Trail)</strong>
                            <span>Waktu kehadiran siswa akan dicatat secara otomatis sesuai detik saat Anda mengeklik
                                tombol <strong>Clock In</strong>. ID Guru pengabsen akan tercatat di sistem audit
                                log.</span>
                        </div>
                    </div>

                    <!-- Catatan Guru -->
                    <div>
                        <label for="notes"
                            class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alasan / Catatan Kendala (Opsional)
                        </label>
                        <input type="text" id="notes" name="notes"
                            placeholder="Contoh: Kendala perangkat / HP mati saat presensi"
                            class="w-full border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button" @click="openBantuAbsen = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                            <span>Clock In Presensi Sekarang</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ===== MODAL LIGHTBOX PRATINJAU FOTO BUKTI / WAJAH ===== -->
        <div x-show="openPhotoModal" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" style="display: none;"
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
                    <button type="button" @click="openPhotoModal = false"
                        class="text-slate-400 hover:text-white transition-colors text-xl font-bold p-1 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Body / Full Image -->
                <div class="p-4 bg-slate-900 flex justify-center items-center">
                    <img :src="previewPhotoUrl" alt="Foto Wajah"
                        class="max-h-96 w-full object-contain rounded-2xl border border-slate-800 shadow-lg">
                </div>

                <!-- Footer -->
                <div class="p-3.5 bg-slate-50 border-t border-slate-100 text-center">
                    <button type="button" @click="openPhotoModal = false"
                        class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== MODAL PRESENSI KBM KELAS ==================== -->
        <div x-show="openKbmModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-kbm-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop Overlay -->
                <div x-show="openKbmModal" x-cloak x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                    @click="openKbmModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Container Panel -->
                <div x-show="openKbmModal" x-cloak x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block w-full max-w-4xl px-4 pt-5 pb-6 text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:p-6 border border-slate-100">

                    <form action="{{ route('teacher.kbm-attendance') }}" method="POST">
                        @csrf
                        <input type="hidden" name="schedule_id" :value="activeKbmSchedule ? activeKbmSchedule.id : ''">

                        <!-- Header Modal Presensi KBM -->
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 mb-4 border-b border-slate-100 gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Presensi KBM Kelas
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold"
                                        x-text="activeKbmSchedule ? activeKbmSchedule.date_formatted : ''"></span>
                                </div>
                                <h3 class="text-xl font-extrabold text-slate-900 flex items-center gap-2"
                                    id="modal-kbm-title">
                                    <i class="fa-solid fa-book-open text-emerald-600"></i>
                                    <span x-text="activeKbmSchedule ? activeKbmSchedule.subject_name : ''"></span>
                                </h3>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    <span class="font-bold text-slate-700"
                                        x-text="activeKbmSchedule ? activeKbmSchedule.class_name : ''"></span> &bull;
                                    <span
                                        x-text="activeKbmSchedule ? ('Jam Ke-' + activeKbmSchedule.period_number + ' (' + activeKbmSchedule.time_range + ')') : ''"></span>
                                </p>
                            </div>
                            <button type="button" @click="openKbmModal = false"
                                class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-xl hover:bg-slate-100">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <!-- Body: Tabel Presensi Siswa -->
                        <div class="max-h-[60vh] overflow-y-auto pr-1">
                            <template
                                x-if="activeKbmSchedule && activeKbmSchedule.students && activeKbmSchedule.students.length > 0">
                                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr
                                                class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                                                <th class="px-4 py-3 w-12 text-center">No</th>
                                                <th class="px-4 py-3">Siswa</th>
                                                <th class="px-4 py-3 text-center">Status Kehadiran KBM</th>
                                                <th class="px-4 py-3">Catatan / Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-sm">
                                            <template x-for="(st, index) in activeKbmSchedule.students" :key="st.id">
                                                <tr class="hover:bg-slate-50/70 transition">
                                                    <!-- No -->
                                                    <td class="px-4 py-3 text-center font-bold text-slate-400 text-xs"
                                                        x-text="index + 1"></td>

                                                    <!-- Info Siswa -->
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <input type="hidden"
                                                                :name="'attendances[' + index + '][student_id]'"
                                                                :value="st.id">
                                                            <img :src="st.avatar_url" :alt="st.name"
                                                                class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-sm flex-shrink-0">
                                                            <div>
                                                                <div class="font-bold text-slate-900 text-sm"
                                                                    x-text="st.name"></div>
                                                                <div class="text-[11px] text-slate-500 font-mono">NISN:
                                                                    <span x-text="st.nisn"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Status Radios -->
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                                            <!-- Hadir -->
                                                            <label class="cursor-pointer">
                                                                <input type="radio"
                                                                    :name="'attendances[' + index + '][status]'"
                                                                    value="present" x-model="st.status"
                                                                    autocomplete="off" class="peer sr-only">
                                                                <span
                                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 text-slate-600 bg-white peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition-colors shadow-sm inline-block">
                                                                    Hadir
                                                                </span>
                                                            </label>

                                                            <!-- Sakit -->
                                                            <label class="cursor-pointer">
                                                                <input type="radio"
                                                                    :name="'attendances[' + index + '][status]'"
                                                                    value="sick" x-model="st.status" autocomplete="off"
                                                                    class="peer sr-only">
                                                                <span
                                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 text-slate-600 bg-white peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 transition-colors shadow-sm inline-block">
                                                                    Sakit
                                                                </span>
                                                            </label>

                                                            <!-- Izin -->
                                                            <label class="cursor-pointer">
                                                                <input type="radio"
                                                                    :name="'attendances[' + index + '][status]'"
                                                                    value="permission" x-model="st.status"
                                                                    autocomplete="off" class="peer sr-only">
                                                                <span
                                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 text-slate-600 bg-white peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-colors shadow-sm inline-block">
                                                                    Izin
                                                                </span>
                                                            </label>

                                                            <!-- Alpa -->
                                                            <label class="cursor-pointer">
                                                                <input type="radio"
                                                                    :name="'attendances[' + index + '][status]'"
                                                                    value="alpha" x-model="st.status" autocomplete="off"
                                                                    class="peer sr-only">
                                                                <span
                                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 text-slate-600 bg-white peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-colors shadow-sm inline-block">
                                                                    Alpa
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </td>

                                                    <!-- Catatan Opsional -->
                                                    <td class="px-4 py-3">
                                                        <input type="text" :name="'attendances[' + index + '][notes]'"
                                                            x-model="st.notes" placeholder="Catatan (opsional)..."
                                                            class="w-full text-xs border-slate-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 py-1.5 px-2.5">
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template
                                x-if="!activeKbmSchedule || !activeKbmSchedule.students || activeKbmSchedule.students.length === 0">
                                <div class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-users-slash text-4xl mb-2 text-slate-300"></i>
                                    <p class="font-bold text-slate-600 text-sm">Belum ada siswa terdaftar di kelas ini.
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1">Pastikan data siswa telah ditambahkan ke
                                        kelas terkait oleh Operator.</p>
                                </div>
                            </template>
                        </div>

                        <!-- Footer Action Buttons -->
                        <div
                            class="mt-6 pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row justify-end gap-2.5">
                            <button type="button" @click="openKbmModal = false"
                                class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl text-xs hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md transition inline-flex items-center justify-center gap-2 cursor-pointer"
                                :disabled="!activeKbmSchedule || !activeKbmSchedule.students || activeKbmSchedule.students.length === 0">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Presensi KBM
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>