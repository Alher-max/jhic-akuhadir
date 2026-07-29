<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight flex items-center gap-2">
            <i class="fa-solid fa-user-tie text-red-600"></i>
            <span>{{ __('Dasbor Wali Kelas') }}</span>
        </h2>
    </x-slot>

    <div class="py-8" x-data="{
        openBantuAbsen: false,
        openPhotoModal: false,
        previewPhotoUrl: '',
        previewPhotoName: '',
        selectedClassId: '',
        selectedStudentId: '',
        searchStudentQuery: '',
        isStudentDropdownOpen: false,
        allClasses: {{ json_encode((\App\Models\SchoolClass::where('tenant_id', auth()->user()->tenant_id ?? 0)->orderBy('nama_kelas')->get())->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->full_name ?: ('Kelas ' . $c->nama_kelas),
            ];
        })->values()) }},
        students: {{ json_encode(($studentsForBantuAbsen ?? collect())->map(function($s) {
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
            
            <!-- ===== BANNER SAMBUTAN WALI KELAS ===== -->
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
            @endphp
            <div class="rounded-2xl border border-white/10 shadow-lg bg-gradient-to-br {{ $gradientClass }} p-6 text-white relative overflow-hidden">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-white/15 pb-5 mb-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-md mb-2">
                            <i class="fa-solid fa-user-tie"></i> Wali Kelas
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                            Selamat Datang, {{ auth()->user()->name }} 👋
                        </h1>
                        <p class="text-white/80 text-sm mt-1">
                            Anda mengelola <strong class="text-white underline underline-offset-4 decoration-white/40">{{ $waliClassName ?? 'Kelas Binaan' }}</strong> di {{ $tenant->name ?? 'Sekolah' }}.
                        </p>
                    </div>

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

                        <a href="{{ route('support-tickets.index') }}" class="bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-headset text-amber-300"></i> Bantuan Operator
                        </a>

                        <button type="button" @click="openBantuAbsen = true" class="bg-white hover:bg-slate-100 text-red-700 font-bold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2 text-sm cursor-pointer">
                            <i class="fa-solid fa-user-check text-red-600"></i> Bantu Absen
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- ===== KARTU RINGKASAN STATISTIK WALI KELAS ===== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Siswa Binaan -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Siswa Binaan</p>
                        <h3 class="text-2xl font-extrabold text-slate-800">{{ $waliTotalSiswa ?? 0 }}</h3>
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

            <!-- ===== TABEL FEED LOG PRESENSI KELAS BINAAN HARI INI ===== -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-red-600"></i>
                            <span>Catatan Kehadiran Siswa Kelas Binaan Hari Ini</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh rekaman presensi masuk/keluar siswa kelas binaan Anda hari ini.</p>
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
                                                <img src="{{ $photoUrl }}" alt="Wajah" class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-2xs group-hover:scale-110 group-hover:border-red-500 transition-all">
                                                <div class="absolute -bottom-1 -right-1 bg-slate-900/70 text-white rounded-full w-4 h-4 flex items-center justify-center text-3xs shadow-xs">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </div>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-2xs text-slate-400 italic">
                                                <i class="fa-solid fa-user-slash text-slate-300"></i> No Photo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-slate-600 font-medium">
                                        {{ optional(optional($attendance->user)->schoolClass)->full_name ?? (optional(optional($attendance->user)->schoolClass)->nama_kelas ?? '-') }}
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-2">
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
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200" title="Terhubung langsung via Perangkat Alat Presensi (IP: {{ $attendance->ip_address ?: 'Terverifikasi' }})">
                                                        <i class="fa-solid fa-tower-broadcast text-3xs"></i> Alat Presensi 📡
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-amber-100/90 text-amber-900 border border-amber-300" title="Menggunakan paket data seluler / IP Luar (IP: {{ $attendance->ip_address ?: 'Data Seluler' }})">
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
                                        Belum ada catatan presensi siswa kelas binaan untuk hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ===== MODAL BANTU ABSEN (PRESENSI MANUAL WALI KELAS) ===== -->
        <div x-show="openBantuAbsen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-100"
                 @click.away="openBantuAbsen = false">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 p-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <i class="fa-solid fa-user-check text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">Bantu Absen Siswa</h3>
                            <p class="text-white/80 text-xs">Pencatatan Presensi Manual oleh Wali Kelas</p>
                        </div>
                    </div>
                    <button type="button" @click="openBantuAbsen = false" class="text-white/70 hover:text-white transition-colors text-xl font-bold p-1 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Modal Form -->
                <form method="POST" action="{{ route('teacher.manual-attendance') }}" class="p-6 space-y-5">
                    @csrf

                    <!-- 1. Filter Berdasarkan Kelas / Rombel -->
                    <div>
                        <label for="filter_class_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>1. Filter Kelas / Rombel</span>
                            <span class="text-[10px] text-slate-400 font-normal">Opsional</span>
                        </label>
                        <select id="filter_class_id" x-model="selectedClassId" @change="selectedStudentId = ''; searchStudentQuery = '';" class="w-full border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm font-medium">
                            <option value="">-- Semua Kelas / Rombel --</option>
                            <template x-for="cls in allClasses" :key="cls.id">
                                <option :value="cls.id" x-text="cls.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- 2. Fitur Pencarian & Searchable Dropdown Siswa -->
                    <div class="relative">
                        <label for="student_search_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            2. Cari & Pilih Nama Siswa <span class="text-red-500">*</span>
                        </label>

                        <!-- Native Select Synced for Form POST & Automated Test Compatibility -->
                        <select id="student_id" name="student_id" x-model="selectedStudentId" class="sr-only" required>
                            <option value="" disabled>-- Pilih Siswa --</option>
                            <template x-for="student in students" :key="student.id">
                                <option :value="student.id" x-text="student.name + ' (' + student.class_name + ')'"></option>
                            </template>
                        </select>

                        <!-- Search Input Field -->
                        <div class="relative">
                            <input type="text"
                                   id="student_search_input"
                                   x-model="searchStudentQuery"
                                   @focus="isStudentDropdownOpen = true"
                                   @input="isStudentDropdownOpen = true; selectedStudentId = ''"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   autocomplete="off"
                                   class="w-full pl-10 pr-10 border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm font-medium">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </div>
                            <button type="button" x-show="searchStudentQuery" @click="searchStudentQuery = ''; selectedStudentId = ''; isStudentDropdownOpen = true" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <!-- Dropdown Results List -->
                        <div x-show="isStudentDropdownOpen" 
                             @click.outside="isStudentDropdownOpen = false" 
                             class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="st in getFilteredStudents()" :key="st.id">
                                <div @click="selectStudent(st)" 
                                     class="p-3 hover:bg-red-50/70 cursor-pointer flex items-center justify-between transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img :src="st.avatar_url" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 text-sm truncate" x-text="st.name"></div>
                                            <div class="text-xs text-slate-500" x-text="'NISN/NIS: ' + st.nisn"></div>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold whitespace-nowrap flex-shrink-0" x-text="st.class_name"></span>
                                </div>
                            </template>
                            <div x-show="getFilteredStudents().length === 0" class="p-4 text-center text-slate-400 text-xs font-medium">
                                <i class="fa-solid fa-user-slash text-slate-300 text-xl block mb-1"></i>
                                Siswa tidak ditemukan untuk kriteria ini.
                            </div>
                        </div>
                    </div>

                    <!-- BOX VERIFIKASI FOTO SISWA (Anti-Kecurangan & Pratinjau Identitas) -->
                    <div x-show="getSelectedStudent()" class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <img :src="getSelectedStudent()?.avatar_url" alt="Foto Siswa" class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-3xs font-bold bg-red-100 text-red-700 uppercase tracking-wider mb-1">
                                    <i class="fa-solid fa-id-badge text-2xs"></i> Identitas & Foto Siswa
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

                    <!-- Informasi Timestamp Otomatis -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 flex items-start gap-3 text-xs text-emerald-900">
                        <i class="fa-solid fa-circle-info text-emerald-600 text-sm mt-0.5"></i>
                        <div>
                            <strong class="font-bold block">Pencatatan Jam Otomatis (Audit Trail)</strong>
                            <span>Waktu kehadiran siswa akan dicatat secara otomatis sesuai detik saat Anda mengeklik tombol <strong>Clock In</strong>. ID Wali Kelas pengabsen akan tercatat di sistem audit log.</span>
                        </div>
                    </div>

                    <!-- Catatan Wali Kelas -->
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alasan / Catatan Kendala (Opsional)
                        </label>
                        <input type="text" id="notes" name="notes" placeholder="Contoh: Kendala perangkat / HP mati saat presensi" class="w-full border-slate-300 rounded-xl shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button" @click="openBantuAbsen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                            <span>Clock In Presensi Sekarang</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Modal Preview Foto Bukti Presensi -->
        <div x-show="openPhotoModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-100 relative"
                 @click.away="openPhotoModal = false">
                <div class="bg-slate-900 text-white p-4 flex items-center justify-between">
                    <h3 class="font-bold text-sm truncate flex items-center gap-2">
                        <i class="fa-solid fa-image text-red-500"></i>
                        <span x-text="'Foto Bukti: ' + previewPhotoName"></span>
                    </h3>
                    <button type="button" @click="openPhotoModal = false" class="text-slate-400 hover:text-white transition-colors text-xl font-bold p-1">
                        &times;
                    </button>
                </div>
                <div class="p-4 bg-slate-950 flex items-center justify-center min-h-[300px]">
                    <img :src="previewPhotoUrl" alt="Foto Wajah" class="max-h-[70vh] w-auto rounded-lg object-contain shadow-lg">
                </div>
                <div class="p-3 bg-slate-50 border-t border-slate-100 text-right">
                    <button type="button" @click="openPhotoModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
