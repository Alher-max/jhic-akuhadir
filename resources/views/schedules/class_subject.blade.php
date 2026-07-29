<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-brand-primary"></i>
            {{ __('KBM & Kegiatan Hub') }}
        </h2>
    </x-slot>

    <!-- Tom Select CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <!-- Flatpickr CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .ts-dropdown { z-index: 9999 !important; }
        .ts-control { border-radius: 0.75rem !important; padding: 0.625rem 0.875rem !important; font-size: 0.875rem !important; border-color: #d1d5db !important; }
        .ts-control.focus { border-color: #4f46e5 !important; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important; }
        .flatpickr-calendar { z-index: 10000 !important; }
    </style>

    <div class="py-10 bg-brand-bg min-h-screen" x-data="classSubjectHub">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- TAB NAVIGATION BAR (3 TABS) -->
            <div class="bg-brand-surface p-1.5 rounded-2xl border border-brand-border shadow-xs flex items-center gap-2 overflow-x-auto">
                <button type="button" @click="activeTab = 'subjects'; const url = new URL(window.location); url.searchParams.set('tab', 'subjects'); window.history.replaceState({}, '', url);"
                    :class="activeTab === 'subjects' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-book-bookmark"></i> Master Mapel
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold"
                        :class="activeTab === 'subjects' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">
                        {{ $subjects->count() }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'schedules'; const url = new URL(window.location); url.searchParams.set('tab', 'schedules'); window.history.replaceState({}, '', url);"
                    :class="activeTab === 'schedules' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-calendar-days"></i> Jadwal KBM Rutin
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold"
                        :class="activeTab === 'schedules' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">
                        {{ $selectedClass ? $selectedClass->full_name : 'Pilih Kelas' }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'activities'; const url = new URL(window.location); url.searchParams.set('tab', 'activities'); window.history.replaceState({}, '', url);"
                    :class="activeTab === 'activities' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-people-group"></i> Ekskul & Kegiatan Sekolah
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold"
                        :class="activeTab === 'activities' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">
                        {{ $activities->count() }}
                    </span>
                </button>
            </div>

            <!-- TAB 1: MASTER MAPEL -->
            <div x-show="activeTab === 'subjects'" x-transition class="space-y-4">
                <div class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl shrink-0">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-brand-text-main">
                                Daftar Master Mata Pelajaran
                            </h3>
                            <p class="text-xs text-brand-text-muted">
                                Kelola direktori mata pelajaran terdaftar untuk plotting jadwal KBM sekolah.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Toggle Switch: Sembunyikan Preset Standar -->
                        <label class="inline-flex items-center cursor-pointer bg-white px-3 py-2 rounded-xl border border-gray-200 shadow-2xs">
                            <input type="checkbox" x-model="hideSubjectPresets" class="sr-only peer">
                            <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-amber-500 relative"></div>
                            <span class="ms-2 text-xs font-semibold text-gray-700">Sembunyikan Preset Standar</span>
                        </label>

                        @if(in_array(auth()->user()->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin']))
                            <!-- Dropdown / Action Menu Kelola Preset -->
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button type="button" @click="open = !open" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs sm:text-sm font-semibold rounded-xl transition border border-indigo-200 inline-flex items-center gap-1.5 shadow-2xs">
                                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-600"></i> Kelola Preset <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-64 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
                                    <div class="py-1">
                                        <form id="form-load-subject-presets" action="{{ route('subjects.presets') }}" method="POST">
                                            @csrf
                                            <button type="button" @click="confirmAction('form-load-subject-presets', 'Muat Preset Mapel Standar?', 'Muat preset 10 mata pelajaran umum Kurikulum Standar Kemendikbud?', 'info', 'Ya, Muat Preset')" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 font-semibold flex items-center gap-2">
                                                <i class="fa-solid fa-cloud-arrow-down text-indigo-600"></i> Muat Preset Standar Kemendikbud
                                            </button>
                                        </form>
                                    </div>
                                    <div class="py-1">
                                        <form id="form-clear-subject-presets" action="{{ route('subjects.presets.clear') }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" @click="confirmAction('form-clear-subject-presets', 'Hapus Semua Preset Mapel?', 'Hapus seluruh data preset mata pelajaran standar untuk sekolah ini?', 'warning', 'Ya, Hapus Semua')" class="w-full text-left px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 font-semibold flex items-center gap-2">
                                                <i class="fa-solid fa-trash-can text-rose-600"></i> Hapus Semua Preset Standar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol + Mapel Baru -->
                            <button type="button" @click="showSubjectModal = true" class="px-4 py-2 bg-brand-primary hover:bg-red-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-plus"></i> + Mapel Baru
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-100 font-bold">
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Kode Mapel</th>
                                    <th class="px-6 py-4">Nama Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($subjects as $index => $sub)
                                    <tr x-show="!hideSubjectPresets || !{{ $sub->is_preset ? 'true' : 'false' }}" class="hover:bg-gray-50/60 transition">
                                        <td class="px-6 py-4 font-semibold text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-gray-100 font-mono text-gray-800 text-xs font-bold rounded-md border border-gray-200">
                                                {{ $sub->code ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 flex items-center gap-2">
                                            <span>{{ $sub->name }}</span>
                                            @if($sub->is_preset)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Preset Kemendikbud
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100">
                                                    Custom
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                            <i class="fa-solid fa-book-open text-3xl mb-2 block text-gray-300"></i>
                                            Belum ada data mata pelajaran terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: JADWAL KBM RUTIN -->
            <div x-show="activeTab === 'schedules'" x-transition class="space-y-6">
                <!-- Header Controller Controls -->
                <div class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl shrink-0">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-brand-text-main">
                                Jadwal KBM: {{ $selectedClass?->full_name ?? 'Pilih Kelas' }}
                            </h3>
                            <p class="text-xs text-brand-text-muted">
                                {{ $selectedClass ? 'Jenjang ' . $selectedClass->jenjang . ' (Tingkat ' . $selectedClass->tingkat . ')' : 'Pilih kelas untuk menampilkan jadwal' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Selector Kelas -->
                        <form method="GET" action="{{ route('class-schedules.index') }}" class="flex items-center gap-2">
                            <input type="hidden" name="tab" value="schedules">
                            <div class="relative inline-flex items-center">
                                <select name="class_id" onchange="this.form.submit()" class="text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl pl-4 pr-10 py-2.5 shadow-sm focus:ring-brand-primary focus:border-brand-primary appearance-none cursor-pointer">
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                            {{ $class->full_name }} ({{ $class->jenjang }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 text-xs">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </form>

                        <!-- Tombol + Mapel Baru (Khusus Operator / Admin) -->
                        @if(in_array(auth()->user()->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin']))
                            <button type="button" @click="showSubjectModal = true" class="px-3.5 py-2.5 bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 text-xs sm:text-sm font-semibold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-book-bookmark text-indigo-600"></i> + Mapel Baru
                            </button>
                        @endif

                        <!-- Tombol + Tambah Jadwal -->
                        @if($selectedClass)
                            <button type="button" @click="openAddSchedule('Senin')" class="px-4 py-2.5 bg-brand-primary hover:bg-red-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-plus"></i> + Tambah Jadwal
                            </button>
                        @endif
                    </div>
                </div>

                <!-- GRID JADWAL MINGGUAN (SENIN - SABTU) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($days as $day)
                        @php $daySchedules = $groupedSchedules[$day] ?? collect(); @endphp
                        <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm flex flex-col overflow-hidden">
                            <!-- Header Hari -->
                            <div class="px-5 py-3.5 bg-gray-50/80 border-b border-brand-border flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $daySchedules->count() > 0 ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                    <h4 class="font-bold text-sm text-gray-800">{{ $day }}</h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-md border border-gray-200">
                                        {{ $daySchedules->count() }} Jam
                                    </span>
                                    @if($selectedClass)
                                        <button type="button" @click="openAddSchedule('{{ $day }}')" title="Tambah Jam pada hari {{ $day }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold px-1.5 py-0.5 rounded hover:bg-indigo-50 transition">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Daftar Jam KBM Hari Ini -->
                            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[500px]">
                                @forelse($daySchedules as $schedule)
                                    <div class="p-3.5 bg-white border border-gray-200/90 hover:border-indigo-300 rounded-xl shadow-xs transition group relative flex flex-col justify-between gap-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-extrabold text-[11px] rounded-md border border-indigo-100">
                                                    Jam ke-{{ $schedule->period_number }}
                                                </span>
                                                <span class="text-xs font-mono font-medium text-gray-500 flex items-center gap-1">
                                                    <i class="fa-regular fa-clock text-gray-400"></i>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </span>
                                            </div>

                                            <!-- Tombol Aksi Edit & Hapus (Selalu Terlihat Eksplisit) -->
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <button type="button" @click="openEditSchedule({{ json_encode($schedule) }})" class="px-2 py-1 bg-gray-100 hover:bg-indigo-50 text-gray-600 hover:text-indigo-600 rounded-md text-xs font-semibold border border-gray-200 transition inline-flex items-center gap-1" title="Edit Jadwal">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    <span>Edit</span>
                                                </button>
                                                <form id="delete-schedule-{{ $schedule->id }}" action="{{ route('class-schedules.destroy', $schedule->id) }}" method="POST" class="inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" @click="confirmDeleteSchedule('delete-schedule-{{ $schedule->id }}')" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-md text-xs font-semibold border border-red-200 transition inline-flex items-center gap-1" title="Hapus Jadwal">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div>
                                            <h5 class="font-bold text-gray-900 text-sm">
                                                {{ $schedule->subject->name }}
                                                @if($schedule->subject->code)
                                                    <span class="text-xs font-mono text-gray-400 font-normal">({{ $schedule->subject->code }})</span>
                                                @endif
                                            </h5>
                                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                <i class="fa-solid fa-user-tie text-gray-400 w-3.5"></i>
                                                {{ $schedule->teacher ? $schedule->teacher->name : 'Guru Pengampu Belum Set' }}
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center border-2 border-dashed border-gray-200 rounded-xl">
                                        <i class="fa-solid fa-calendar-xmark text-gray-300 text-2xl mb-1 block"></i>
                                        <p class="text-xs text-gray-400 font-medium">Belum ada KBM di hari {{ $day }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 3: EKSKUL & KEGIATAN SEKOLAH -->
            <div x-show="activeTab === 'activities'" x-transition class="space-y-4">
                <div class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl shrink-0">
                            <i class="fa-solid fa-people-group"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-brand-text-main">
                                Ekstrakurikuler & Kegiatan Rutin / Insidental
                            </h3>
                            <p class="text-xs text-brand-text-muted">
                                Kelola jam kegiatan ekskul, upacara, atau agenda rutin sekolah yang memerlukan presensi terpisah.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Toggle Switch: Sembunyikan Preset Standar -->
                        <label class="inline-flex items-center cursor-pointer bg-white px-3 py-2 rounded-xl border border-gray-200 shadow-2xs">
                            <input type="checkbox" x-model="hideActivityPresets" class="sr-only peer">
                            <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-amber-500 relative"></div>
                            <span class="ms-2 text-xs font-semibold text-gray-700">Sembunyikan Preset Standar</span>
                        </label>

                        <!-- Dropdown / Action Menu Kelola Preset -->
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button type="button" @click="open = !open" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs sm:text-sm font-semibold rounded-xl transition border border-indigo-200 inline-flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-wand-magic-sparkles text-indigo-600"></i> Kelola Preset <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-64 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
                                <div class="py-1">
                                    <form id="form-load-activity-presets" action="{{ route('activities.presets') }}" method="POST">
                                        @csrf
                                        <button type="button" @click="confirmAction('form-load-activity-presets', 'Muat Preset Kegiatan Standar?', 'Muat 5 preset kegiatan & ekskul sekolah standar (Upacara, Pramuka, Senam, PMR, Sholat)?', 'info', 'Ya, Muat Preset')" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 font-semibold flex items-center gap-2">
                                            <i class="fa-solid fa-cloud-arrow-down text-indigo-600"></i> Muat Preset Kegiatan Standar
                                        </button>
                                    </form>
                                </div>
                                <div class="py-1">
                                    <form id="form-clear-activity-presets" action="{{ route('activities.presets.clear') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="confirmAction('form-clear-activity-presets', 'Hapus Semua Preset Kegiatan?', 'Hapus seluruh data preset kegiatan standar untuk sekolah ini?', 'warning', 'Ya, Hapus Semua')" class="w-full text-left px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 font-semibold flex items-center gap-2">
                                            <i class="fa-solid fa-trash-can text-rose-600"></i> Hapus Semua Preset Standar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol + Kegiatan Baru -->
                        <button type="button" @click="openAddActivity()" class="px-4 py-2 bg-brand-primary hover:bg-red-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-plus"></i> + Kegiatan Baru
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse($activities as $act)
                        <div x-show="!hideActivityPresets || !{{ $act->is_preset ? 'true' : 'false' }}" class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex flex-col justify-between space-y-3 relative group">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-base text-gray-900">{{ $act->name }}</h4>
                                        <div class="flex items-center gap-1.5 flex-wrap mt-1">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                <i class="fa-regular fa-calendar text-[9px] me-1"></i>{{ $act->day_name ?: 'Senin' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                @if(($act->target_scope ?? 'all') === 'all')
                                                    <i class="fa-solid fa-users text-[9px] me-1"></i>Semua Siswa
                                                @elseif(($act->target_scope ?? 'all') === 'class')
                                                    <i class="fa-solid fa-graduation-cap text-[9px] me-1"></i>Kelas Target
                                                @else
                                                    <i class="fa-solid fa-user-check text-[9px] me-1"></i>Anggota Ekskul
                                                @endif
                                            </span>
                                            @if($act->is_preset)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Preset
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="openEditActivity({{ json_encode($act) }})" class="text-gray-400 hover:text-indigo-600 p-1 transition" title="Edit Kegiatan">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>
                                    <form id="delete-activity-{{ $act->id }}" action="{{ route('activities.destroy', $act->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="confirmDeleteActivity('delete-activity-{{ $act->id }}')" class="text-gray-400 hover:text-red-600 p-1 transition" title="Hapus">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="space-y-1 text-xs text-gray-600 pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-500">Jam Pelaksanaan:</span>
                                    <span class="font-mono font-bold text-gray-800 me-1">
                                        {{ \Carbon\Carbon::parse($act->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($act->end_time)->format('H:i') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-500">Toleransi Telat:</span>
                                    <span class="font-bold text-indigo-600">
                                        {{ $act->late_tolerance_minutes }} Menit
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 bg-brand-surface rounded-2xl border-2 border-dashed border-gray-200 text-center">
                            <i class="fa-solid fa-users-rectangle text-4xl text-gray-300 mb-2 block"></i>
                            <h5 class="font-bold text-gray-700 text-sm">Belum Ada Kegiatan / Ekskul Registered</h5>
                            <p class="text-xs text-gray-400 mt-1">Klik "+ Tambah Kegiatan Baru" di atas untuk menambahkan kegiatan rutin atau insidental.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- MODAL 1: TAMBAH / EDIT JADWAL PELAJARAN -->
        <div x-cloak x-show="showScheduleModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="showScheduleModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showScheduleModal = false"></div>
            
            <div x-show="showScheduleModal" x-transition.scale.95 class="relative z-10 w-full max-w-lg bg-brand-surface rounded-2xl p-6 sm:p-8 shadow-xl my-8 border border-brand-border text-left">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-bold text-brand-text-main">
                        <span x-text="isEdit ? 'Edit Jam KBM' : 'Tambah Jam KBM Baru'"></span>
                    </h3>
                    <button type="button" @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('class-schedules.store') }}" method="POST" @submit="validateScheduleForm($event)" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="schedule_id" x-model="scheduleForm.id">
                    <input type="hidden" name="class_id" x-model="scheduleForm.class_id">

                    <template x-if="scheduleTimeError">
                        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600 flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                            <span x-text="scheduleTimeError"></span>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Hari <span class="text-red-500">*</span></label>
                            <select name="day_name" x-model="scheduleForm.day_name" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jam ke- (Period) <span class="text-red-500">*</span></label>
                            <input type="number" name="period_number" x-model="scheduleForm.period_number" min="1" max="15" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select id="select-subject" name="subject_id" x-model="scheduleForm.subject_id" required class="w-full">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }} {{ $subject->code ? '('.$subject->code.')' : '' }}</option>
                            @endforeach
                        </select>
                        @if($subjects->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">Belum ada mata pelajaran. Silakan hubungi Operator Sekolah untuk membuat master mapel.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Guru Pengampu <span class="text-red-500">*</span></label>
                        <select id="select-teacher" name="teacher_id" x-model="scheduleForm.teacher_id" required class="w-full">
                            <option value="">-- Pilih Guru Pengampu --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @if($teachers->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">Belum ada data guru. Silakan daftarkan guru terlebih dahulu di menu Kelola Guru.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="text" id="fp-schedule-start" name="start_time" x-model="scheduleForm.start_time" required placeholder="07:30" class="time-picker-5min mt-1 block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="text" id="fp-schedule-end" name="end_time" x-model="scheduleForm.end_time" required placeholder="08:15" class="time-picker-5min mt-1 block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showScheduleModal = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-brand-primary text-white rounded-lg text-sm font-semibold hover:bg-red-700 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                            <template x-if="isSubmitting">
                                <i class="fa-solid fa-spinner animate-spin text-xs"></i>
                            </template>
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Jadwal'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(in_array(auth()->user()->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin']))
        <!-- MODAL 2: TAMBAH MATA PELAJARAN BARU -->
        <div x-cloak x-show="showSubjectModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="showSubjectModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showSubjectModal = false"></div>
            
            <div x-show="showSubjectModal" x-transition.scale.95 class="relative z-10 w-full max-w-md bg-brand-surface rounded-2xl p-6 sm:p-8 shadow-xl my-8 border border-brand-border text-left">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-bold text-brand-text-main">Tambah Mata Pelajaran Baru</h3>
                    <button type="button" @click="showSubjectModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('subjects.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Matematika Dasar" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Kode Mapel (Opsional)</label>
                        <input type="text" name="code" placeholder="Contoh: MTK-01" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showSubjectModal = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-primary text-white rounded-lg text-sm font-semibold hover:bg-red-700 shadow-sm">
                            Simpan Mapel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- MODAL 3: TAMBAH / EDIT KEGIATAN BARU -->
        <div x-cloak x-show="showActivityModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="showActivityModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showActivityModal = false"></div>
            
            <div x-show="showActivityModal" x-transition.scale.95 class="relative z-10 w-full max-w-lg bg-brand-surface rounded-2xl p-6 sm:p-8 shadow-xl my-8 border border-brand-border text-left">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-bold text-brand-text-main">
                        <span x-text="activityForm.id ? 'Edit Kegiatan / Ekskul' : 'Tambah Kegiatan / Ekskul Baru'"></span>
                    </h3>
                    <button type="button" @click="showActivityModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('activities.store') }}" method="POST" @submit="validateActivityForm($event)" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="activity_id" x-model="activityForm.id">

                    <template x-if="activityTimeError">
                        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600 flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                            <span x-text="activityTimeError"></span>
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Nama Kegiatan / Ekskul <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="activityForm.name" required placeholder="Contoh: Upacara Bendera / Pramuka" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Hari Pelaksanaan <span class="text-red-500">*</span></label>
                            <select name="day_name" x-model="activityForm.day_name" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Toleransi Telat (Menit)</label>
                            <input type="number" name="late_tolerance_minutes" x-model="activityForm.late_tolerance_minutes" min="0" max="180" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="text" id="fp-activity-start" name="start_time" x-model="activityForm.start_time" required placeholder="07:00" class="time-picker-5min mt-1 block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="text" id="fp-activity-end" name="end_time" x-model="activityForm.end_time" required placeholder="08:00" class="time-picker-5min mt-1 block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-brand-primary focus:border-brand-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Target Peserta <span class="text-red-500">*</span></label>
                        <select name="target_scope" x-model="activityForm.target_scope" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-brand-primary focus:border-brand-primary">
                            <option value="all">Semua Siswa Sekolah (Seluruh Rombel)</option>
                            <option value="class">Spesifik Kelas / Rombel Tertentu</option>
                            <option value="members">Siswa Terdaftar / Anggota Ekskul</option>
                        </select>
                        <p x-cloak x-show="activityForm.target_scope === 'members'" class="mt-1.5 text-xs text-indigo-700 font-medium flex items-center gap-1.5 bg-indigo-50 p-2.5 rounded-xl border border-indigo-100">
                            <i class="fa-solid fa-circle-info text-indigo-600"></i>
                            <span>Setelah disimpan, atur daftar siswa melalui tombol <strong>'Kelola Anggota'</strong> di kartu kegiatan.</span>
                        </p>
                    </div>

                    <div x-cloak x-show="activityForm.target_scope === 'class'" class="space-y-1.5 p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <label class="block text-xs font-semibold text-gray-700">Pilih Rombel / Kelas Target:</label>
                        <div class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto pr-1">
                            @foreach($classes as $c)
                                <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer bg-white p-2 rounded-lg border border-gray-200 hover:border-indigo-300">
                                    <input type="checkbox" name="target_class_ids[]" value="{{ $c->id }}" x-model="activityForm.target_class_ids" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $c->nama_kelas }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showActivityModal = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-primary text-white rounded-lg text-sm font-semibold hover:bg-red-700 shadow-sm">
                            Simpan Kegiatan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: KELOLA ANGGOTA EKSKUL & KEGIATAN -->
        <div x-cloak x-show="showMembersModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="showMembersModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showMembersModal = false"></div>
            
            <div x-show="showMembersModal" x-transition.scale.95 class="relative z-10 w-full max-w-lg bg-brand-surface rounded-2xl p-6 sm:p-8 shadow-xl my-8 border border-brand-border text-left">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-brand-text-main flex items-center gap-2">
                            <i class="fa-solid fa-users-gear text-indigo-600"></i>
                            <span>Kelola Anggota Kegiatan</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Kegiatan: <strong class="text-gray-900" x-text="selectedActivity ? selectedActivity.name : ''"></strong>
                        </p>
                    </div>
                    <button type="button" @click="showMembersModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="selectedActivity ? '/dashboard/activities/' + selectedActivity.id + '/members' : '#'" method="POST" class="mt-4 space-y-4">
                    @csrf
                    
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                        <input type="text" x-model="memberSearchQuery" placeholder="Cari nama siswa atau NISN..." class="w-full text-xs ps-9 pe-4 py-2.5 rounded-xl border border-gray-200 focus:ring-brand-primary focus:border-brand-primary">
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-600 font-semibold px-1">
                        <span>Pilih Siswa Terdaftar:</span>
                        <span class="text-indigo-600" x-text="selectedMemberIds.length + ' Siswa Terpilih'"></span>
                    </div>

                    <div class="max-h-64 overflow-y-auto space-y-1.5 p-2 bg-gray-50 rounded-xl border border-gray-200">
                        @forelse($allStudents as $student)
                            <label x-cloak x-show="!memberSearchQuery || '{{ strtolower($student->name) }}'.includes(memberSearchQuery.toLowerCase()) || '{{ strtolower($student->nisn ?? '') }}'.includes(memberSearchQuery.toLowerCase())" class="flex items-center justify-between p-2.5 bg-white hover:bg-indigo-50/50 rounded-lg border border-gray-200 cursor-pointer transition">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" x-model="selectedMemberIds" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">{{ $student->name }}</div>
                                        <div class="text-[11px] text-gray-400 font-mono">
                                            {{ $student->nisn ?: 'Tanpa NISN' }}
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 bg-gray-100 text-gray-600 rounded">
                                    {{ $student->schoolClass ? $student->schoolClass->nama_kelas : 'Tanpa Kelas' }}
                                </span>
                            </label>
                        @empty
                            <div class="p-4 text-center text-xs text-gray-400">Tidak ada siswa terdaftar.</div>
                        @endforelse
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showMembersModal = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-brand-primary text-white rounded-lg text-sm font-semibold hover:bg-red-700 shadow-sm inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Anggota</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('classSubjectHub', () => ({
                activeTab: new URLSearchParams(window.location.search).get('tab') || 'subjects',
                hideSubjectPresets: false,
                hideActivityPresets: false,
                showScheduleModal: {{ $errors->has('start_time') || $errors->has('end_time') || $errors->has('subject_id') ? 'true' : 'false' }},
                showSubjectModal: false,
                showActivityModal: false,
                showMembersModal: false,
                selectedActivity: null,
                selectedMemberIds: [],
                memberSearchQuery: '',
                isEdit: false,
                isSubmitting: false,
                scheduleTimeError: '{{ $errors->first('end_time') ?: '' }}',
                activityTimeError: '',
                openManageMembers(act) {
                    this.selectedActivity = act;
                    this.showMembersModal = true;
                    this.memberSearchQuery = '';
                    const members = act.members || [];
                    this.selectedMemberIds = members.map(m => String(m.id));
                },
                activityForm: {
                    id: null,
                    name: '',
                    day_name: 'Senin',
                    start_time: '15:00',
                    end_time: '17:00',
                    late_tolerance_minutes: 15,
                    target_scope: 'all',
                    target_class_ids: []
                },
                openAddActivity() {
                    this.showActivityModal = true;
                    this.activityTimeError = '';
                    this.activityForm = {
                        id: null,
                        name: '',
                        day_name: 'Senin',
                        start_time: '15:00',
                        end_time: '17:00',
                        late_tolerance_minutes: 15,
                        target_scope: 'all',
                        target_class_ids: []
                    };
                    this.$nextTick(() => {
                        const startInput = document.querySelector('#fp-activity-start');
                        if (startInput && startInput._flatpickr) {
                            startInput._flatpickr.setDate(this.activityForm.start_time);
                        }
                        const endInput = document.querySelector('#fp-activity-end');
                        if (endInput && endInput._flatpickr) {
                            endInput._flatpickr.setDate(this.activityForm.end_time);
                        }
                    });
                },
                openEditActivity(act) {
                    this.showActivityModal = true;
                    this.activityTimeError = '';
                    let classIds = [];
                    if (act.target_class_ids) {
                        classIds = typeof act.target_class_ids === 'string' ? JSON.parse(act.target_class_ids) : act.target_class_ids;
                    }
                    this.activityForm = {
                        id: act.id,
                        name: act.name,
                        day_name: act.day_name || 'Senin',
                        start_time: act.start_time ? act.start_time.substring(0, 5) : '15:00',
                        end_time: act.end_time ? act.end_time.substring(0, 5) : '17:00',
                        late_tolerance_minutes: act.late_tolerance_minutes !== undefined ? act.late_tolerance_minutes : 15,
                        target_scope: act.target_scope || 'all',
                        target_class_ids: Array.isArray(classIds) ? classIds.map(String) : []
                    };
                    this.$nextTick(() => {
                        const startInput = document.querySelector('#fp-activity-start');
                        if (startInput && startInput._flatpickr) {
                            startInput._flatpickr.setDate(this.activityForm.start_time);
                        }
                        const endInput = document.querySelector('#fp-activity-end');
                        if (endInput && endInput._flatpickr) {
                            endInput._flatpickr.setDate(this.activityForm.end_time);
                        }
                    });
                },
                scheduleForm: {
                    id: null,
                    class_id: '{{ $selectedClassId }}',
                    day_name: 'Senin',
                    period_number: 1,
                    subject_id: '',
                    teacher_id: '',
                    start_time: '07:00',
                    end_time: '07:45'
                },
                openAddSchedule(day = 'Senin') {
                    this.showScheduleModal = true;
                    this.isEdit = false;
                    this.isSubmitting = false;
                    this.scheduleTimeError = '';

                    this.scheduleForm = {
                        id: null,
                        class_id: '{{ $selectedClassId }}',
                        day_name: day,
                        period_number: 1,
                        subject_id: '',
                        teacher_id: '',
                        start_time: '07:30',
                        end_time: '08:15'
                    };

                    if (window.tsSubject) window.tsSubject.clear();
                    if (window.tsTeacher) window.tsTeacher.clear();

                    this.$nextTick(() => {
                        const startInput = document.querySelector('#fp-schedule-start') || document.querySelector('input[name="start_time"]');
                        if (startInput && startInput._flatpickr) {
                            startInput._flatpickr.setDate(this.scheduleForm.start_time);
                        }
                        const endInput = document.querySelector('#fp-schedule-end') || document.querySelector('input[name="end_time"]');
                        if (endInput && endInput._flatpickr) {
                            endInput._flatpickr.setDate(this.scheduleForm.end_time);
                        }
                    });
                },
                openEditSchedule(schedule) {
                    this.showScheduleModal = true;
                    this.isEdit = true;
                    this.isSubmitting = false;
                    this.scheduleTimeError = '';
                    
                    this.scheduleForm = {
                        id: schedule.id,
                        class_id: schedule.class_id,
                        day_name: schedule.day_name,
                        period_number: schedule.period_number,
                        subject_id: schedule.subject_id,
                        teacher_id: schedule.teacher_id || '',
                        start_time: schedule.start_time ? schedule.start_time.substring(0, 5) : '',
                        end_time: schedule.end_time ? schedule.end_time.substring(0, 5) : ''
                    };

                    this.$nextTick(() => {
                        // Sync Tom Select
                        if (window.tsSubject) window.tsSubject.setValue(schedule.subject_id);
                        if (window.tsTeacher) window.tsTeacher.setValue(schedule.teacher_id || '');

                        // Sync Flatpickr
                        const startInput = document.querySelector('#fp-schedule-start') || document.querySelector('input[name="start_time"]');
                        if (startInput && startInput._flatpickr) {
                            startInput._flatpickr.setDate(this.scheduleForm.start_time);
                        }
                        const endInput = document.querySelector('#fp-schedule-end') || document.querySelector('input[name="end_time"]');
                        if (endInput && endInput._flatpickr) {
                            endInput._flatpickr.setDate(this.scheduleForm.end_time);
                        }
                    });
                },
                validateScheduleForm(e) {
                    this.scheduleTimeError = '';
                    const start = this.scheduleForm.start_time;
                    const end = this.scheduleForm.end_time;
                    if (start && end && end <= start) {
                        e.preventDefault();
                        this.scheduleTimeError = 'Jam Selesai tidak boleh lebih awal atau sama dengan Jam Mulai!';
                        this.isSubmitting = false;
                        return false;
                    }
                    if (this.isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    this.isSubmitting = true;
                },
                validateActivityForm(e) {
                    this.activityTimeError = '';
                    const start = e.target.start_time.value;
                    const end = e.target.end_time.value;
                    if (start && end && end <= start) {
                        e.preventDefault();
                        this.activityTimeError = 'Jam Selesai tidak boleh lebih awal atau sama dengan Jam Mulai!';
                        return false;
                    }
                },
                confirmDeleteSchedule(formId) {
                    Swal.fire({
                        title: 'Hapus Jadwal KBM?',
                        text: 'Jadwal yang dihapus tidak dapat dikembalikan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-4 py-2 font-semibold',
                            cancelButton: 'rounded-xl px-4 py-2 font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(formId).submit();
                        }
                    });
                },
                confirmDeleteActivity(formId) {
                    Swal.fire({
                        title: 'Hapus Kegiatan?',
                        text: 'Kegiatan yang dihapus tidak dapat dikembalikan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-4 py-2 font-semibold',
                            cancelButton: 'rounded-xl px-4 py-2 font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(formId).submit();
                        }
                    });
                },
                confirmAction(formId, title, text, icon = 'warning', confirmBtnText = 'Ya, Lanjutkan!') {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: icon === 'warning' || icon === 'error' ? '#ef4444' : '#4f46e5',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-4 py-2 font-semibold',
                            cancelButton: 'rounded-xl px-4 py-2 font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(formId).submit();
                        }
                    });
                }
            }));
        });

        let tsSubject, tsTeacher;
        document.addEventListener('DOMContentLoaded', function() {

            // Flatpickr – time-only popup, renders under <body> to avoid modal clipping
            const fpConfig = {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                minuteIncrement: 5,
                allowInput: true,
                appendTo: document.body,
                onChange: function(selectedDates, dateStr, instance) {
                    // Trigger Alpine x-model sync
                    instance.element.dispatchEvent(new Event('input', { bubbles: true }));
                }
            };

            document.querySelectorAll('.time-picker-5min').forEach(function(el) {
                flatpickr(el, fpConfig);
            });

            if (document.getElementById('select-subject')) {
                window.tsSubject = new TomSelect('#select-subject', {
                    create: false,
                    placeholder: 'Cari & Pilih Mata Pelajaran...',
                    dropdownParent: 'body'
                });
                window.tsSubject.on('change', function(val) {
                    let formEl = document.querySelector('[x-data]');
                    if (formEl && formEl._x_dataStack) {
                        formEl._x_dataStack[0].scheduleForm.subject_id = val;
                    }
                });
            }
            if (document.getElementById('select-teacher')) {
                window.tsTeacher = new TomSelect('#select-teacher', {
                    create: false,
                    placeholder: 'Cari & Pilih Guru Pengampu...',
                    dropdownParent: 'body'
                });
                window.tsTeacher.on('change', function(val) {
                    let formEl = document.querySelector('[x-data]');
                    if (formEl && formEl._x_dataStack) {
                        formEl._x_dataStack[0].scheduleForm.teacher_id = val;
                    }
                });
            }
        });
    </script>
</x-app-layout>
