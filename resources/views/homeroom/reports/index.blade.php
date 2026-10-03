<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-brand-text-main leading-tight">
                    {{ __('Dasbor Rapor Wali Kelas') }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-1">
                    {{ __('Kompilasi Nilai Leger, Rekap Presensi HadirYuk, Catatan Karakter, & Ekstrakurikuler') }}
                </p>
            </div>
            @if($activeYear)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Tahun Ajaran: {{ $activeYear->formatted_period }}</span>
                </div>
            @else
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
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

            <!-- Daftar Kelas Asuhan -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-6 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-lg text-brand-text-main">Kelas Asuhan Binaan</h3>
                        <p class="text-xs text-brand-text-muted mt-0.5">Kelola kelengkapan data rapor siswa rombel Anda.</p>
                    </div>
                    <div class="text-xs font-semibold text-brand-text-muted bg-white px-3 py-1.5 rounded-lg border border-brand-border">
                        {{ $classes->count() }} Rombel Terdaftar
                    </div>
                </div>

                @if($classes->isEmpty())
                    <div class="py-16 px-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-red-50 text-brand-primary rounded-2xl flex items-center justify-center border border-red-100">
                            <span class="material-symbols-outlined text-[32px]">assignment_ind</span>
                        </div>
                        <h4 class="text-base font-bold text-brand-text-main">Tidak Ada Kelas Binaan</h4>
                        <p class="text-sm text-brand-text-muted max-w-md mx-auto mt-1">
                            Anda belum ditetapkan sebagai wali kelas untuk rombel mana pun pada tahun ajaran ini. Silakan hubungi operator sekolah jika ada kekeliruan pembagian tugas.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        @foreach($classes as $class)
                            <div class="bg-white rounded-2xl border border-brand-border p-5 hover:shadow-md transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="px-2.5 py-1 text-xs font-black uppercase tracking-wider rounded-lg bg-red-50 text-brand-primary border border-red-100">
                                            {{ $class->jenjang }} &bull; Tingkat {{ $class->tingkat }}
                                        </span>
                                        @if($class->fase)
                                            <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-blue-50 text-blue-700 border border-blue-200">
                                                Fase {{ $class->fase }}
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="font-extrabold text-xl text-brand-text-main">
                                        Kelas {{ $class->nama_kelas }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">group</span>
                                        Jumlah: <span class="font-bold text-gray-700">{{ $class->students->count() }} Siswa</span>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">person</span>
                                        Wali Kelas: {{ $class->waliKelas?->name ?? 'Belum Ditentukan' }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-4 border-t border-brand-border space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('homeroom.reports.leger', $class) }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold rounded-xl transition border border-indigo-200 shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">grid_view</span>
                                            <span>Leger Nilai</span>
                                        </a>

                                        <a href="{{ route('homeroom.reports.attendance', $class) }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold rounded-xl transition border border-emerald-200 shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">event_available</span>
                                            <span>Presensi</span>
                                        </a>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('homeroom.reports.notes', $class) }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 hover:bg-amber-100 text-xs font-bold rounded-xl transition border border-amber-200 shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">draw</span>
                                            <span>Catatan</span>
                                        </a>

                                        <a href="{{ route('homeroom.reports.extracurricular', $class) }}"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold rounded-xl transition border border-rose-200 shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">sports_score</span>
                                            <span>Ekskul</span>
                                        </a>
                                    </div>

                                    <div class="pt-2 border-t border-brand-border flex items-center justify-between gap-2">
                                        <form method="POST" action="{{ route('homeroom.reports.publish', $class) }}" onsubmit="return confirm('Publikasikan rapor kelas {{ $class->nama_kelas }}? Notifikasi WhatsApp akan otomatis dikirimkan ke orang tua dan siswa.');" class="w-1/2">
                                            @csrf
                                            <input type="hidden" name="notify_whatsapp" value="1">
                                            <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-extrabold rounded-xl transition shadow-xs">
                                                <span class="material-symbols-outlined text-[15px]">send</span>
                                                <span>Publikasikan</span>
                                            </button>
                                        </form>

                                        <a href="{{ route('homeroom.reports.print.batch', $class) }}" target="_blank"
                                            class="w-1/2 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-extrabold rounded-xl transition shadow-xs">
                                            <span class="material-symbols-outlined text-[15px]">print</span>
                                            <span>Cetak A4</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
