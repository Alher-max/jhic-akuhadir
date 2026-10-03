<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('homeroom.reports.index') }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Daftar Rapor
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Kelas {{ $class->nama_kelas }}</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Nilai Ekstrakurikuler: Kelas {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Pengelolaan Capaian & Predikat Kegiatan Pengembangan Diri / Ekstrakurikuler Siswa
                </p>
            </div>
            @if($activeYear)
                <div class="px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                    TA: {{ $activeYear->formatted_period }}
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Sub-Navigation Menu Tabs -->
            <div class="flex items-center gap-2 border-b border-brand-border pb-3 overflow-x-auto text-sm font-bold">
                <a href="{{ route('homeroom.reports.leger', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">grid_view</span>
                    <span>Leger Nilai (Matriks)</span>
                </a>
                <a href="{{ route('homeroom.reports.attendance', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">event_available</span>
                    <span>Rekap Presensi</span>
                </a>
                <a href="{{ route('homeroom.reports.notes', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">draw</span>
                    <span>Catatan & Kenaikan</span>
                </a>
                <a href="{{ route('homeroom.reports.extracurricular', $class) }}"
                    class="px-4 py-2 rounded-xl bg-brand-primary text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">sports_score</span>
                    <span>Ekstrakurikuler</span>
                </a>
            </div>

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm space-y-1">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="material-symbols-outlined text-red-600">warning</span>
                        <span>Terdapat kesalahan input:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 ml-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Guide Card -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border p-5 shadow-sm flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">workspace_premium</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-brand-text-main">Panduan Nilai Ekstrakurikuler</h3>
                    <p class="text-xs text-brand-text-muted mt-1 leading-relaxed">
                        Satu siswa dapat memiliki lebih dari satu kegiatan ekstrakurikuler (misal: Pramuka Wajib, PMR, Futsal, dsb.). Anda dapat menambahkan kegiatan baru atau menghapus kegiatan yang sudah ada secara langsung.
                    </p>
                </div>
            </div>

            <!-- Student List with Extracurriculars -->
            <div class="space-y-4">
                @forelse($students as $student)
                    @php
                        $report = $reports->get($student->id);
                        $extracurriculars = $report?->extracurriculars ?? collect();
                    @endphp
                    <div x-data="{ showForm: false }" class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm p-6 hover:border-brand-primary/40 transition">
                        
                        <!-- Student Header Row -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-brand-border gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-sm shrink-0">
                                    {{ substr($student->name, 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-brand-text-main">{{ $student->name }}</h4>
                                    <div class="flex items-center gap-2 text-xs text-brand-text-muted">
                                        <span>NISN: {{ $student->nisn ?? $student->nis ?? '-' }}</span>
                                        <span>•</span>
                                        <span>Total Ekskul: <strong class="text-brand-text-main">{{ $extracurriculars->count() }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            @if($report)
                                <button type="button" @click="showForm = !showForm"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition self-start sm:self-center"
                                    :class="showForm ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-brand-primary text-white hover:bg-brand-primary/90 shadow-sm'">
                                    <span class="material-symbols-outlined text-[16px]" x-text="showForm ? 'close' : 'add'"></span>
                                    <span x-text="showForm ? 'Batal Tambah' : 'Tambah Ekskul'"></span>
                                </button>
                            @endif
                        </div>

                        <!-- Existing Extracurricular Badges / List -->
                        <div class="pt-4 space-y-3">
                            @if($extracurriculars->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($extracurriculars as $ekskul)
                                        <div class="p-3.5 rounded-xl border border-brand-border bg-gray-50/60 flex items-start justify-between gap-3">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-xs text-brand-text-main">{{ $ekskul->activity_name }}</span>
                                                    @php
                                                        $badgeColor = match(strtolower($ekskul->predicate)) {
                                                            'sangat baik', 'a' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                            'baik', 'b' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                            'cukup', 'c' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                            default => 'bg-rose-50 text-rose-700 border-rose-200'
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $badgeColor }}">
                                                        {{ $ekskul->predicate }}
                                                    </span>
                                                </div>
                                                @if($ekskul->description)
                                                    <p class="text-[11px] text-brand-text-muted leading-relaxed">
                                                        {{ $ekskul->description }}
                                                    </p>
                                                @endif
                                            </div>

                                            <!-- Delete Button -->
                                            <form method="POST" action="{{ route('homeroom.reports.extracurricular.destroy', $ekskul) }}" onsubmit="return confirm('Hapus nilai ekskul {{ $ekskul->activity_name }} untuk {{ $student->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Nilai Ekskul">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-brand-text-muted italic py-1">
                                    Belum ada data nilai ekstrakurikuler yang dicatat untuk siswa ini.
                                </p>
                            @endif
                        </div>

                        <!-- Add Extracurricular Form (Alpine Toggled) -->
                        @if($report)
                            <div x-show="showForm" x-transition class="mt-4 pt-4 border-t border-dashed border-brand-border bg-emerald-50/30 -mx-6 -mb-6 p-6 rounded-b-2xl">
                                <form method="POST" action="{{ route('homeroom.reports.extracurricular.store') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="student_report_id" value="{{ $report->id }}">

                                    <div class="flex items-center gap-2 text-xs font-bold text-emerald-800">
                                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                        <span>Tambah Ekstrakurikuler Baru untuk {{ $student->name }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-brand-text-main mb-1">Nama Kegiatan *</label>
                                            <input type="text" name="activity_name" required placeholder="Contoh: Pramuka, PMR, Silat"
                                                class="w-full text-xs rounded-xl border-brand-border bg-white text-brand-text-main p-2.5 focus:ring-brand-primary focus:border-brand-primary">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-brand-text-main mb-1">Predikat *</label>
                                            <select name="predicate" required
                                                class="w-full text-xs rounded-xl border-brand-border bg-white text-brand-text-main p-2.5 focus:ring-brand-primary focus:border-brand-primary">
                                                <option value="Sangat Baik">Sangat Baik</option>
                                                <option value="Baik" selected>Baik</option>
                                                <option value="Cukup">Cukup</option>
                                                <option value="Kurang">Kurang</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-brand-text-main mb-1">Keterangan / Capaian (Opsional)</label>
                                            <input type="text" name="description" placeholder="Contoh: Aktif dan berprestasi"
                                                class="w-full text-xs rounded-xl border-brand-border bg-white text-brand-text-main p-2.5 focus:ring-brand-primary focus:border-brand-primary">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button type="button" @click="showForm = false"
                                            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 bg-white border border-brand-border hover:bg-gray-50 transition">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-emerald-700 hover:bg-emerald-800 shadow-sm transition flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px]">save</span>
                                            <span>Simpan Ekskul</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="bg-brand-surface rounded-2xl border border-brand-border p-12 text-center shadow-sm">
                        <span class="material-symbols-outlined text-4xl text-gray-300">group_off</span>
                        <h4 class="font-bold text-base text-brand-text-main mt-2">Tidak Ada Siswa di Kelas Ini</h4>
                        <p class="text-xs text-brand-text-muted mt-1">Tambahkan siswa terlebih dahulu pada rombel kelas ini.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
