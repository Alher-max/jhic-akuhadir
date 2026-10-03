<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('p5.projects.index.all') }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Daftar Rombel
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Kelas {{ $class->nama_kelas }}</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Projek Profil (P5 & P5RA): Kelas {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Fase {{ $class->fase ?? 'E' }} &bull; Kurikulum {{ strtoupper($class->curriculum_type ?? 'Merdeka') }} &bull; {{ $studentCount }} Siswa
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($isHomeroomOrElevated)
                    <a href="{{ route('p5.projects.create', $class) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        <span>Buat Projek Baru</span>
                    </a>
                @endif
                @if($activeYear)
                    <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        TA: {{ $activeYear->formatted_period }}
                    </div>
                @endif
            </div>
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

            <!-- Daftar Projek Card Grid -->
            @if($projects->isEmpty())
                <div class="bg-white rounded-2xl border border-brand-border p-12 text-center shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl">shapes</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Projek P5 di Kelas Ini</h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                        Buat projek profil baru untuk kelas {{ $class->nama_kelas }} dengan memilih tema Kemendikbudristek atau nilai Rahmatan Lil 'Alamin (Kemenag).
                    </p>
                    @if($isHomeroomOrElevated)
                        <a href="{{ route('p5.projects.create', $class) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            <span>Mulai Buat Projek Pertama</span>
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        @php
                            $targetCount = $project->targets_count;
                            $assessCount = $project->assessments_count;
                            $expected = $studentCount * $targetCount;
                            $percent = $expected > 0 ? (int) round(($assessCount / $expected) * 100) : 0;
                        @endphp
                        <div class="bg-white rounded-2xl border border-brand-border shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-indigo-50 text-indigo-700">
                                        {{ $project->theme }}
                                    </span>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-gray-400">Semester {{ $project->academicYear?->semester }}</span>
                                    </div>
                                </div>

                                <h3 class="font-extrabold text-lg text-brand-text-main line-clamp-2 mb-2 leading-snug">
                                    <a href="{{ route('p5.projects.show', $project) }}" class="hover:text-brand-primary transition">
                                        {{ $project->title }}
                                    </a>
                                </h3>

                                <p class="text-xs text-gray-600 line-clamp-3 mb-4 leading-relaxed">
                                    {{ $project->description }}
                                </p>

                                <div class="space-y-2 py-3 border-y border-gray-100 text-xs">
                                    <div class="flex justify-between text-gray-600">
                                        <span>Fasilitator / Koordinator:</span>
                                        <span class="font-bold text-gray-800">{{ $project->coordinator?->name ?? 'Belum Ditentukan' }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Target Sub-elemen:</span>
                                        <span class="font-bold text-gray-800">{{ $targetCount }} Target</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 items-center pt-1">
                                        <span>Progres Penilaian:</span>
                                        <span class="font-extrabold text-brand-primary">{{ $percent }}% Selesai</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-brand-primary h-1.5 rounded-full transition-all" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-3 flex items-center justify-between gap-2">
                                <a href="{{ route('p5.projects.show', $project) }}"
                                    class="px-3.5 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                                    Detail Target
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('p5.reports.print-batch', $project) }}" target="_blank"
                                        title="Cetak Massal Rapor P5 Kelas Ini"
                                        class="p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition border border-gray-200">
                                        <span class="material-symbols-outlined text-[18px]">print</span>
                                    </a>

                                    <a href="{{ route('p5.assessments.matrix', $project) }}"
                                        class="inline-flex items-center gap-1 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                        <span class="material-symbols-outlined text-[16px]">draw</span>
                                        <span>Input Nilai</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
