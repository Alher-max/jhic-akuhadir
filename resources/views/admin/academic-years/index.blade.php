<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-brand-text-main leading-tight">
                    {{ __('Tahun Ajaran & Semester') }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-1">
                    {{ __('Fondasi Master Data Akademik untuk Modul Rapor Kurikulum Merdeka & Presensi') }}
                </p>
            </div>
            <div>
                <button type="button" @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-primary text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Tambah Tahun Ajaran</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen"
        x-data="{
            showCreateModal: {{ $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false' }},
            showEditModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
            editData: {
                id: '{{ old('_method') === 'PUT' ? old('id', '') : '' }}',
                name: '{{ old('_method') === 'PUT' ? addslashes(old('name', '')) : '' }}',
                semester: '{{ old('_method') === 'PUT' ? old('semester', '1') : '1' }}',
                start_date: '{{ old('_method') === 'PUT' ? old('start_date', '') : '' }}',
                end_date: '{{ old('_method') === 'PUT' ? old('end_date', '') : '' }}',
                is_active: {{ old('_method') === 'PUT' ? (old('is_active') ? 'true' : 'false') : 'false' }}
            },
            openEdit(item) {
                this.editData = {
                    id: item.id,
                    name: item.name,
                    semester: String(item.semester),
                    start_date: item.start_date,
                    end_date: item.end_date,
                    is_active: Boolean(item.is_active)
                };
                this.showEditModal = true;
            }
        }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm animate-fade-in">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm animate-fade-in">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl shadow-sm">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1 text-amber-800">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                        Terdapat kesalahan pada input formulir:
                    </div>
                    <ul class="list-disc list-inside text-xs text-amber-800 space-y-0.5 ml-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Active Year Summary Banner -->
            <div class="bg-gradient-to-r from-red-900 via-brand-primary to-rose-700 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/20 backdrop-blur-sm text-white">
                                Tahun Ajaran Aktif Saat Ini
                            </span>
                        </div>
                        @if($activeYear)
                            <h3 class="text-2xl font-black tracking-tight text-white mt-1">
                                {{ $activeYear->name }} &mdash; {{ $activeYear->semester_label }}
                            </h3>
                            <p class="text-xs text-white/80 flex items-center gap-1.5 pt-1">
                                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                Periode: {{ $activeYear->start_date?->translatedFormat('d F Y') }} s.d. {{ $activeYear->end_date?->translatedFormat('d F Y') }}
                            </p>
                        @else
                            <h3 class="text-xl font-bold tracking-tight text-white/90 mt-1">
                                Belum Ada Tahun Ajaran Aktif
                            </h3>
                            <p class="text-xs text-white/75 pt-1">
                                Silakan aktifkan salah satu tahun ajaran di tabel bawah agar sistem presensi dan modul rapor dapat mengelompokkan nilai dengan benar.
                            </p>
                        @endif
                    </div>
                    @if($activeYear)
                        <div class="flex items-center gap-2 bg-emerald-500/20 backdrop-blur-md px-4 py-2 rounded-xl border border-emerald-300/30 text-emerald-100 self-start md:self-auto">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider text-white">Live di Sistem</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-6 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-lg text-brand-text-main">Daftar Tahun Ajaran & Semester</h3>
                        <p class="text-xs text-brand-text-muted mt-0.5">Satu tenant hanya boleh memiliki 1 tahun ajaran aktif secara bersamaan.</p>
                    </div>
                    <div class="text-xs font-medium text-brand-text-muted bg-white px-3 py-1.5 rounded-lg border border-brand-border">
                        Total: {{ $academicYears->count() }} Periode
                    </div>
                </div>

                @if($academicYears->isEmpty())
                    <!-- Empty State -->
                    <div class="py-16 px-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-red-50 text-brand-primary rounded-2xl flex items-center justify-center border border-red-100">
                            <span class="material-symbols-outlined text-[32px]">date_range</span>
                        </div>
                        <h4 class="text-base font-bold text-brand-text-main">Belum Ada Data Tahun Ajaran</h4>
                        <p class="text-sm text-brand-text-muted max-w-md mx-auto mt-1 mb-6">
                            Mulai atur kalender akademik sekolah dengan menambahkan tahun ajaran dan semester aktif pertama Anda.
                        </p>
                        <button type="button" @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            <span>Tambah Tahun Ajaran Sekarang</span>
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-wider border-b border-brand-border font-semibold">
                                    <th class="px-6 py-4">Tahun Ajaran</th>
                                    <th class="px-6 py-4">Semester</th>
                                    <th class="px-6 py-4">Rentang Tanggal</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border text-sm">
                                @foreach($academicYears as $year)
                                    <tr class="hover:bg-gray-50/60 transition {{ $year->is_active ? 'bg-red-50/20' : '' }}">
                                        <!-- Nama Tahun Ajaran -->
                                        <td class="px-6 py-4 font-bold text-brand-text-main whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $year->name }}</span>
                                                @if($year->is_active)
                                                    <span class="text-[10px] uppercase font-extrabold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        Aktif
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Semester -->
                                        <td class="px-6 py-4 text-brand-text-muted whitespace-nowrap">
                                            <span class="font-medium text-gray-800">{{ $year->semester_label }}</span>
                                        </td>

                                        <!-- Rentang Tanggal -->
                                        <td class="px-6 py-4 text-brand-text-muted whitespace-nowrap">
                                            <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                <span class="font-mono">{{ $year->start_date?->format('d/m/Y') }}</span>
                                                <span class="text-gray-400">&mdash;</span>
                                                <span class="font-mono">{{ $year->end_date?->format('d/m/Y') }}</span>
                                            </div>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @if($year->is_active)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                                    Tidak Aktif
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2 justify-end">
                                                <!-- Tombol Aktifkan (Jika belum aktif) -->
                                                @if(!$year->is_active)
                                                    <form method="POST" action="{{ route('admin.academic-years.activate', $year) }}" class="inline"
                                                        onsubmit="return confirm('Aktifkan tahun ajaran {{ $year->name }} ({{ $year->semester_label }})? Tahun ajaran yang sedang aktif akan otomatis dinonaktifkan.');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition"
                                                            title="Jadikan Tahun Ajaran Aktif">
                                                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                                            Aktifkan
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Tombol Edit -->
                                                <button type="button"
                                                    @click="openEdit({
                                                        id: '{{ $year->id }}',
                                                        name: '{{ addslashes($year->name) }}',
                                                        semester: '{{ $year->semester }}',
                                                        start_date: '{{ $year->start_date?->format('Y-m-d') }}',
                                                        end_date: '{{ $year->end_date?->format('Y-m-d') }}',
                                                        is_active: {{ $year->is_active ? 'true' : 'false' }}
                                                    })"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition"
                                                    title="Edit Data">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                                    Edit
                                                </button>

                                                <!-- Tombol Hapus -->
                                                <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" class="inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran {{ $year->name }} ({{ $year->semester_label }})?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center p-1.5 text-xs font-semibold rounded-lg text-red-600 hover:bg-red-50 border border-transparent hover:border-red-200 transition"
                                                        title="Hapus Data">
                                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- ================= MODAL TAMBAH TAHUN AJARAN ================= -->
        <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showCreateModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showCreateModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-brand-border">

                    <div class="flex justify-between items-center pb-4 border-b border-brand-border">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-primary">add_circle</span>
                            <h3 class="text-lg font-bold text-brand-text-main">Tambah Tahun Ajaran Baru</h3>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.academic-years.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <!-- Nama Tahun Ajaran -->
                        <div>
                            <label for="create_name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Nama Tahun Ajaran <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="create_name" name="name" value="{{ old('name') }}" placeholder="Contoh: 2026/2027" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] text-gray-500 mt-0.5">Format standar format akademik: YYYY/YYYY (contoh: 2026/2027).</p>
                        </div>

                        <!-- Semester -->
                        <div>
                            <label for="create_semester" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Semester <span class="text-red-500">*</span>
                            </label>
                            <select id="create_semester" name="semester" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm @error('semester') border-red-500 @enderror">
                                <option value="1" {{ old('semester', '1') == '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                            </select>
                            @error('semester')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Mulai & Tanggal Selesai -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="create_start_date" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="create_start_date" name="start_date" value="{{ old('start_date') }}" required
                                    class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm @error('start_date') border-red-500 @enderror">
                                @error('start_date')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="create_end_date" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Tanggal Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="create_end_date" name="end_date" value="{{ old('end_date') }}" required
                                    class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm @error('end_date') border-red-500 @enderror">
                                @error('end_date')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Checkbox Aktifkan -->
                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100/60 transition">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                                    class="h-4 w-4 text-brand-primary focus:ring-red-500 border-gray-300 rounded">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-800">Jadikan Sebagai Tahun Ajaran Aktif</span>
                                    <p class="text-gray-500">Tahun ajaran aktif lain dalam tenant ini akan otomatis dinonaktifkan.</p>
                                </div>
                            </label>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                            <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 text-sm font-semibold text-white bg-brand-primary rounded-xl hover:bg-red-700 transition shadow-sm">
                                Simpan Tahun Ajaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= MODAL EDIT TAHUN AJARAN ================= -->
        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showEditModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showEditModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-brand-border">

                    <div class="flex justify-between items-center pb-4 border-b border-brand-border">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-primary">edit_calendar</span>
                            <h3 class="text-lg font-bold text-brand-text-main">Edit Tahun Ajaran</h3>
                        </div>
                        <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form method="POST" :action="'/admin/academic-years/' + editData.id" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" :value="editData.id">

                        <!-- Nama Tahun Ajaran -->
                        <div>
                            <label for="edit_name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Nama Tahun Ajaran <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="edit_name" name="name" x-model="editData.name" placeholder="Contoh: 2026/2027" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                            <p class="text-[11px] text-gray-500 mt-0.5">Format standar format akademik: YYYY/YYYY (contoh: 2026/2027).</p>
                        </div>

                        <!-- Semester -->
                        <div>
                            <label for="edit_semester" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Semester <span class="text-red-500">*</span>
                            </label>
                            <select id="edit_semester" name="semester" x-model="editData.semester" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                                <option value="1">Semester 1 (Ganjil)</option>
                                <option value="2">Semester 2 (Genap)</option>
                            </select>
                        </div>

                        <!-- Tanggal Mulai & Tanggal Selesai -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_start_date" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="edit_start_date" name="start_date" x-model="editData.start_date" required
                                    class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                            </div>
                            <div>
                                <label for="edit_end_date" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Tanggal Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="edit_end_date" name="end_date" x-model="editData.end_date" required
                                    class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                            </div>
                        </div>

                        <!-- Checkbox Aktifkan -->
                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100/60 transition">
                                <input type="checkbox" name="is_active" value="1" x-model="editData.is_active"
                                    class="h-4 w-4 text-brand-primary focus:ring-red-500 border-gray-300 rounded">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-800">Jadikan Sebagai Tahun Ajaran Aktif</span>
                                    <p class="text-gray-500">Tahun ajaran aktif lain dalam tenant ini akan otomatis dinonaktifkan.</p>
                                </div>
                            </label>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                            <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 text-sm font-semibold text-white bg-brand-primary rounded-xl hover:bg-red-700 transition shadow-sm">
                                Perbarui Tahun Ajaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
