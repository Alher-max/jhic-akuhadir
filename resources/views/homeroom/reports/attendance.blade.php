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
                    Rekap Presensi Rapor: Kelas {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Sinkronisasi Otomatis dari Log Presensi HadirYuk & Penyesuaian Angka Kehadiran Siswa
                </p>
            </div>
            @if($activeYear)
                <div class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
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
                    class="px-4 py-2 rounded-xl bg-brand-primary text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">event_available</span>
                    <span>Rekap Presensi</span>
                </a>
                <a href="{{ route('homeroom.reports.notes', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">draw</span>
                    <span>Catatan & Kenaikan</span>
                </a>
                <a href="{{ route('homeroom.reports.extracurricular', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
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

            <!-- Auto-Pull Action Banner -->
            <div class="bg-gradient-to-r from-emerald-800 to-teal-700 rounded-2xl p-6 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h3 class="text-lg font-bold">Auto-Pull Presensi HadirYuk</h3>
                    <p class="text-xs text-white/80 max-w-xl">
                        Tarik otomatis rekap presensi (Sakit, Izin, Alpa) seluruh siswa kelas ini langsung dari data log presensi harian HadirYuk pada rentang tanggal semester aktif:
                        @if($activeYear)
                            <span class="font-bold underline">{{ $activeYear->start_date?->format('d/m/Y') }} s.d. {{ $activeYear->end_date?->format('d/m/Y') }}</span>.
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('homeroom.reports.sync-attendance', $class) }}" onsubmit="return confirm('Tarik data presensi dari HadirYuk? Data kehadiran yang sudah diedit manual akan disesuaikan dengan log terbaru.');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-emerald-800 font-extrabold text-xs rounded-xl hover:bg-emerald-50 shadow-md transition whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px]">sync</span>
                        <span>Tarik Ulang Presensi (Auto-Sync)</span>
                    </button>
                </form>
            </div>

            <!-- Attendance Table Form -->
            <form method="POST" action="{{ route('homeroom.reports.batch-update', $class) }}">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class->id }}">

                <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-base text-brand-text-main">Data Kehadiran Siswa Semester Ini</h3>
                            <p class="text-xs text-brand-text-muted mt-0.5">
                                Anda dapat mengoreksi angka kehadiran secara manual jika ada dispensasi khusus dari pihak madrasah/sekolah.
                            </p>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl hover:bg-red-700 shadow-sm transition">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Simpan Rekap Presensi</span>
                        </button>
                    </div>

                    @if($students->isEmpty())
                        <div class="py-16 text-center text-gray-500 text-sm">
                            Belum ada siswa di kelas ini.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-50/90 text-gray-600 text-xs font-bold uppercase tracking-wider border-b border-brand-border">
                                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                                        <th class="px-4 py-3.5">Nama Siswa</th>
                                        <th class="px-4 py-3.5 w-32">NISN</th>
                                        <th class="px-4 py-3.5 w-28 text-center text-blue-700 bg-blue-50/30">Sakit (Hari)</th>
                                        <th class="px-4 py-3.5 w-28 text-center text-amber-700 bg-amber-50/30">Izin (Hari)</th>
                                        <th class="px-4 py-3.5 w-28 text-center text-red-700 bg-red-50/30">Alpa (Hari)</th>
                                        <th class="px-4 py-3.5 w-28 text-center bg-gray-100/60 font-black">Total Absen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-border">
                                    @foreach($students as $idx => $student)
                                        @php
                                            $report = $reports->get($student->id);
                                            $sick = $report ? $report->sick_count : 0;
                                            $perm = $report ? $report->permission_count : 0;
                                            $alpha = $report ? $report->alpha_count : 0;
                                        @endphp
                                        <tr class="hover:bg-gray-50/70 transition"
                                            x-data="{
                                                sick: {{ $sick }},
                                                perm: {{ $perm }},
                                                alpha: {{ $alpha }},
                                                get total() {
                                                    return (parseInt(this.sick) || 0) + (parseInt(this.perm) || 0) + (parseInt(this.alpha) || 0);
                                                }
                                            }">
                                            <td class="px-4 py-3 text-center text-gray-500 font-medium">{{ $idx + 1 }}</td>
                                            <td class="px-4 py-3 font-bold text-gray-900">
                                                <input type="hidden" name="reports[{{ $idx }}][student_id]" value="{{ $student->id }}">
                                                {{ $student->name }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                                {{ $student->nisn ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-center bg-blue-50/10">
                                                <input type="number" min="0" name="reports[{{ $idx }}][sick_count]"
                                                    x-model.number="sick"
                                                    class="w-20 text-center text-sm font-bold rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                                            </td>
                                            <td class="px-4 py-3 text-center bg-amber-50/10">
                                                <input type="number" min="0" name="reports[{{ $idx }}][permission_count]"
                                                    x-model.number="perm"
                                                    class="w-20 text-center text-sm font-bold rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm">
                                            </td>
                                            <td class="px-4 py-3 text-center bg-red-50/10">
                                                <input type="number" min="0" name="reports[{{ $idx }}][alpha_count]"
                                                    x-model.number="alpha"
                                                    class="w-20 text-center text-sm font-bold rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                                            </td>
                                            <td class="px-4 py-3 text-center font-black text-gray-900 bg-gray-50/50">
                                                <span x-text="total"></span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-5 border-t border-brand-border bg-gray-50 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl hover:bg-red-700 shadow-sm transition">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <span>Simpan Rekap Presensi</span>
                            </button>
                        </div>
                    @endif
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
