<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-brand-text-main leading-tight">
                    {{ __('Buku Nilai Guru Mata Pelajaran') }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-1">
                    {{ __('Input Nilai Formatif/Sumatif & Narasi Capaian Pembelajaran Kurikulum Merdeka') }}
                </p>
            </div>
            @if($activeYear)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Tahun Ajaran: {{ $activeYear->formatted_period }}</span>
                </div>
            @else
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                    <span class="material-symbols-outlined text-[16px]">warning</span>
                    <span>Belum ada Tahun Ajaran Aktif</span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            <!-- Warning if no active academic year -->
            @if(!$activeYear)
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
                        <div>
                            <h4 class="font-bold text-sm text-amber-900">Perhatian: Tahun Ajaran Belum Aktif</h4>
                            <p class="text-xs text-amber-800 mt-1">
                                Saat ini tidak ada tahun ajaran yang berstatus aktif di sekolah Anda. Pengisian nilai dan Tujuan Pembelajaran hanya dapat disimpan jika terdapat tahun ajaran yang aktif. Silakan hubungi Operator Sekolah.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Daftar Kelas & Mapel Binaan -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-6 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-lg text-brand-text-main">Jadwal Mengajar & Kelas Binaan</h3>
                        <p class="text-xs text-brand-text-muted mt-0.5">Pilih kelas dan mata pelajaran untuk membuka lembar penilaian siswa.</p>
                    </div>
                    <div class="text-xs font-semibold text-brand-text-muted bg-white px-3 py-1.5 rounded-lg border border-brand-border">
                        {{ $assignments->count() }} Mata Pelajaran / Kelas
                    </div>
                </div>

                @if($assignments->isEmpty())
                    <div class="py-16 px-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-red-50 text-brand-primary rounded-2xl flex items-center justify-center border border-red-100">
                            <span class="material-symbols-outlined text-[32px]">menu_book</span>
                        </div>
                        <h4 class="text-base font-bold text-brand-text-main">Belum Ada Jadwal Mengajar</h4>
                        <p class="text-sm text-brand-text-muted max-w-md mx-auto mt-1 mb-4">
                            Anda belum ditugaskan mengajar di jadwal pelajaran kelas mana pun. Silakan hubungi operator sekolah untuk pembagian jadwal KBM.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        @foreach($assignments as $assignment)
                            <div class="bg-white rounded-xl border border-brand-border p-5 hover:shadow-md transition group flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="px-2.5 py-1 text-xs font-black uppercase tracking-wider rounded-lg bg-red-50 text-brand-primary border border-red-100">
                                            {{ $assignment->schoolClass?->jenjang ?? 'KBM' }} &bull; Tingkat {{ $assignment->schoolClass?->tingkat ?? '-' }}
                                        </span>
                                        @if($assignment->schoolClass?->fase)
                                            <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-blue-50 text-blue-700 border border-blue-200">
                                                Fase {{ $assignment->schoolClass->fase }}
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="font-extrabold text-lg text-brand-text-main group-hover:text-brand-primary transition">
                                        {{ $assignment->subject?->name ?? 'Mata Pelajaran' }}
                                    </h4>
                                    <p class="text-sm font-semibold text-gray-700 mt-1 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[18px] text-gray-400">group</span>
                                        Kelas: {{ $assignment->schoolClass?->nama_kelas ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">person</span>
                                        Pengampu: {{ $assignment->teacher?->name ?? '-' }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-4 border-t border-brand-border flex items-center justify-between">
                                    <span class="text-xs text-gray-500 font-medium">
                                        Kurikulum: {{ ucfirst(str_replace('_', ' ', $assignment->schoolClass?->curriculum_type ?? 'merdeka')) }}
                                    </span>
                                    <a href="{{ route('teacher.gradebook.show', [$assignment->class_id, $assignment->subject_id]) }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-primary text-white text-xs font-bold rounded-xl hover:bg-red-700 transition shadow-sm">
                                        <span>Buka Buku Nilai</span>
                                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
