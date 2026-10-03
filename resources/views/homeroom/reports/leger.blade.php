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
                    Leger Nilai: Kelas {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Kompilasi Matriks Nilai Akhir Seluruh Mata Pelajaran & Kehadiran Siswa
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('homeroom.reports.export-leger', $class) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">file_download</span>
                    <span>Unduh Leger Nilai (Excel/CSV)</span>
                </a>
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-gray-700 text-xs font-bold rounded-xl border border-gray-300 hover:bg-gray-50 shadow-sm transition">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak Leger</span>
                </button>
                @if($activeYear)
                    <div class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        TA: {{ $activeYear->formatted_period }}
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Sub-Navigation Menu Tabs -->
            <div class="flex items-center gap-2 border-b border-brand-border pb-3 overflow-x-auto text-sm font-bold">
                <a href="{{ route('homeroom.reports.leger', $class) }}"
                    class="px-4 py-2 rounded-xl bg-brand-primary text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
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

            <!-- Action Toolbar for Publication & Printing -->
            <div class="bg-white p-4 rounded-2xl border border-brand-border shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="text-xs text-brand-text-muted flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-indigo-600 text-base">verified</span>
                    <span>Status Rapor Kelas: Publikasikan agar siswa & wali murid dapat mencetak rapor resmi A4.</span>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <form method="POST" action="{{ route('homeroom.reports.publish', $class) }}" onsubmit="return confirm('Publikasikan seluruh rapor kelas {{ $class->nama_kelas }}? Notifikasi WhatsApp resmi akan otomatis dikirimkan ke orang tua dan siswa.');">
                        @csrf
                        <input type="hidden" name="notify_whatsapp" value="1">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">send</span>
                            <span>Publikasikan & Kirim WA</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('homeroom.reports.lock', $class) }}" onsubmit="return confirm('Kunci nilai rapor kelas ini agar tidak dapat diubah kembali?');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition border border-gray-300">
                            <span class="material-symbols-outlined text-[16px]">lock</span>
                            <span>Kunci Rapor</span>
                        </button>
                    </form>

                    <a href="{{ route('homeroom.reports.export-leger', $class) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">file_download</span>
                        <span>Unduh Leger Nilai (Excel/CSV)</span>
                    </a>

                    <a href="{{ route('homeroom.reports.print.batch', $class) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">print</span>
                        <span>Cetak Massal (A4)</span>
                    </a>
                </div>
            </div>

            <!-- Matriks Leger Nilai -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-5 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-base text-brand-text-main">Tabel Kompilasi Leger Nilai Siswa</h3>
                        <p class="text-xs text-brand-text-muted mt-0.5">
                            Menampilkan nilai yang sudah diinput oleh masing-masing guru mata pelajaran pada semester aktif.
                        </p>
                    </div>
                    <div class="text-xs text-gray-500 font-semibold bg-white px-3 py-1.5 rounded-lg border border-brand-border">
                        Total {{ $students->count() }} Siswa &bull; {{ $subjects->count() }} Mata Pelajaran
                    </div>
                </div>

                @if($students->isEmpty())
                    <div class="py-16 text-center text-gray-500 text-sm">
                        Belum ada data siswa di kelas ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50/90 text-gray-600 font-bold uppercase tracking-wider border-b border-brand-border">
                                    <th class="px-3 py-3.5 w-10 text-center">No</th>
                                    <th class="px-4 py-3.5 min-w-[200px]">Nama Siswa</th>
                                    <th class="px-3 py-3.5 w-24">NISN</th>
                                    @foreach($subjects as $subj)
                                        <th class="px-3 py-3.5 text-center min-w-[90px]" title="{{ $subj->name }}">
                                            <span class="block truncate max-w-[80px]">{{ $subj->code ?? $subj->name }}</span>
                                        </th>
                                    @endforeach
                                    <th class="px-3 py-3.5 text-center w-20 bg-gray-100/70 font-extrabold text-gray-800">Rata-Rata</th>
                                    <th class="px-2.5 py-3.5 text-center w-12 text-blue-700 bg-blue-50/40" title="Sakit">S</th>
                                    <th class="px-2.5 py-3.5 text-center w-12 text-amber-700 bg-amber-50/40" title="Izin">I</th>
                                    <th class="px-2.5 py-3.5 text-center w-12 text-red-700 bg-red-50/40" title="Alpa">A</th>
                                    <th class="px-3 py-3.5 text-center w-16">Cetak</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border">
                                @foreach($students as $idx => $student)
                                    @php
                                        $report = $reports->get($student->id);
                                        $avg = $averages[$student->id] ?? 0.0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-3 py-3 text-center text-gray-500 font-medium">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-900 whitespace-nowrap">
                                            {{ $student->name }}
                                        </td>
                                        <td class="px-3 py-3 font-mono text-gray-500">
                                            {{ $student->nisn ?: '-' }}
                                        </td>
                                        @foreach($subjects as $subj)
                                            @php
                                                $score = $matrix[$student->id][$subj->id] ?? null;
                                            @endphp
                                            <td class="px-3 py-3 text-center font-medium whitespace-nowrap">
                                                @if($score !== null)
                                                    <span class="font-bold text-gray-800">{{ number_format((float) $score, 1) }}</span>
                                                @else
                                                    <span class="text-[10px] text-amber-600 font-semibold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                                        Belum
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-3 text-center font-black text-gray-900 bg-gray-50/60 whitespace-nowrap">
                                            {{ number_format((float) $avg, 2) }}
                                        </td>
                                        <td class="px-2.5 py-3 text-center text-blue-700 font-bold bg-blue-50/20">
                                            {{ $report ? $report->sick_count : 0 }}
                                        </td>
                                        <td class="px-2.5 py-3 text-center text-amber-700 font-bold bg-amber-50/20">
                                            {{ $report ? $report->permission_count : 0 }}
                                        </td>
                                        <td class="px-2.5 py-3 text-center text-red-700 font-bold bg-red-50/20">
                                            {{ $report ? $report->alpha_count : 0 }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <a href="{{ route('homeroom.reports.print.single', [$class, $student]) }}" target="_blank"
                                                class="inline-flex p-1.5 rounded-lg text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 transition" title="Cetak Rapor A4">
                                                <span class="material-symbols-outlined text-[18px]">print</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
