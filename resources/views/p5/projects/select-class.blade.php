<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Projek Penguatan Profil Pelajar Pancasila (P5 & P5RA)
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Pilih Rombel Kelas untuk Mengelola Tema, Target Dimensi, & Asesmen Projek
                </p>
            </div>
            @if($activeYear)
                <div class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                    <span>Tahun Ajaran: {{ $activeYear->formatted_period }}</span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-brand-surface rounded-2xl border border-brand-border p-6 shadow-sm">
                <h3 class="text-base font-bold text-brand-text-main mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary">school</span>
                    <span>Daftar Rombel Kelas</span>
                </h3>

                @if($classes->isEmpty())
                    <div class="text-center py-12">
                        <span class="material-symbols-outlined text-4xl text-gray-400 mb-2">info</span>
                        <p class="text-sm text-gray-500">Belum ada kelas yang terdaftar pada sistem.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($classes as $c)
                            <a href="{{ route('p5.projects.index', $c) }}"
                                class="p-5 rounded-2xl border border-brand-border bg-white hover:border-brand-primary hover:shadow-md transition group flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-indigo-50 text-indigo-700">
                                            Fase {{ $c->fase ?? '-' }}
                                        </span>
                                        <span class="text-xs text-gray-400 font-semibold">Tingkat {{ $c->tingkat }}</span>
                                    </div>
                                    <h4 class="text-lg font-black text-brand-text-main group-hover:text-brand-primary transition">
                                        {{ $c->nama_kelas }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Wali: {{ $c->waliKelas?->name ?? 'Belum Ditentukan' }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-brand-primary font-bold">
                                    <span>Kelola Projek P5</span>
                                    <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition">arrow_forward</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
