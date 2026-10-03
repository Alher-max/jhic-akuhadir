<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Rapor Digital Anak
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Pemantauan Capaian Belajar & Rapor Resmi Putra-Putri Anda
                </p>
            </div>
            @if($activeYear)
                <div class="px-3.5 py-2 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-bold">
                    TA: {{ $activeYear->formatted_period }}
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Child Selector Tabs (If more than 1 child) -->
            @if($children->count() > 1)
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    @foreach($children as $child)
                        <a href="{{ route('parent.report-card', ['child_id' => $child->id]) }}"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap {{ $selectedChild?->id === $child->id ? 'bg-brand-primary text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-brand-border' }}">
                            <span class="material-symbols-outlined text-[16px]">face</span>
                            <span>{{ $child->name }} ({{ $child->schoolClass?->nama_kelas ?? 'Tanpa Kelas' }})</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(!$selectedChild)
                <div class="bg-brand-surface rounded-3xl border border-brand-border p-12 text-center shadow-sm">
                    <span class="material-symbols-outlined text-4xl text-gray-300">family_restroom</span>
                    <h3 class="font-bold text-base text-brand-text-main mt-2">Tidak Ada Data Anak Tertaut</h3>
                    <p class="text-xs text-brand-text-muted mt-1">Akun orang tua ini belum ditautkan ke data siswa sekolah.</p>
                </div>
            @elseif(!$isPublished)
                <!-- Publication Guard Banner (Draft / Belum Rilis) -->
                <div class="bg-brand-surface rounded-3xl border border-brand-border p-8 text-center shadow-sm space-y-4">
                    <div class="w-16 h-16 rounded-full bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-3xl">hourglass_top</span>
                    </div>
                    <div class="space-y-1 max-w-lg mx-auto">
                        <h3 class="font-extrabold text-lg text-brand-text-main">
                            Rapor {{ $selectedChild->name }} Sedang Dalam Proses Penyusunan
                        </h3>
                        <p class="text-xs text-brand-text-muted leading-relaxed">
                            Rapor semester ini sedang dalam proses penyusunan dan rekapitulasi nilai oleh dewan guru. Lembar rapor resmi beserta nilai capaian belajar ananda akan otomatis dapat diakses setelah dipublikasikan oleh pihak sekolah.
                        </p>
                    </div>
                    <div class="pt-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            Status: Menunggu Publikasi Sekolah
                        </span>
                    </div>
                </div>
            @else
                <!-- Published Report View for Parent -->

                <!-- Highlight & Print Action Banner -->
                <div class="bg-gradient-to-r from-brand-primary to-indigo-800 rounded-3xl p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-white text-xs font-bold tracking-wide">
                            <span class="material-symbols-outlined text-sm">verified</span>
                            <span>Rapor Resmi Telah Diterbitkan</span>
                        </div>
                        <h3 class="text-xl font-black">
                            {{ $selectedChild->name }}
                        </h3>
                        <p class="text-xs text-white/80">
                            Kelas {{ $selectedChild->schoolClass?->nama_kelas ?? '-' }} • NISN: {{ $selectedChild->nisn ?? $selectedChild->nis ?? '-' }} • Semester {{ $activeYear?->semester }} ({{ $activeYear?->name }})
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('parent.report-card.print', ['child_id' => $selectedChild->id]) }}" target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-brand-primary font-black text-xs hover:bg-slate-50 shadow-md transition transform active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">print</span>
                            <span>Buka & Cetak Lembar Rapor Resmi (A4)</span>
                        </a>
                    </div>
                </div>

                <!-- Summary KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-brand-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">grade</span>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-brand-text-muted">Rata-rata Nilai</span>
                            <h4 class="text-xl font-black text-brand-text-main">{{ number_format($reportData['averageScore'], 2) }}</h4>
                        </div>
                    </div>

                    <div class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">menu_book</span>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-brand-text-muted">Mata Pelajaran</span>
                            <h4 class="text-xl font-black text-brand-text-main">{{ $reportData['grades']->count() }} Mapel</h4>
                        </div>
                    </div>

                    <div class="bg-brand-surface p-5 rounded-2xl border border-brand-border shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">event_busy</span>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-brand-text-muted">Total Ketidakhadiran</span>
                            <h4 class="text-xl font-black text-brand-text-main">{{ $reportData['attendance']['total'] }} Hari</h4>
                        </div>
                    </div>
                </div>

                <!-- Academic Grades Table Card -->
                <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-brand-border flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-base text-brand-text-main">Nilai Capaian Kompetensi Mata Pelajaran</h3>
                            <p class="text-xs text-brand-text-muted mt-0.5">Penilaian intrakurikuler ananda pada semester ini</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-brand-border text-brand-text-muted uppercase font-bold text-[10px]">
                                <tr>
                                    <th class="px-4 py-3 w-12 text-center">No</th>
                                    <th class="px-4 py-3 w-48">Mata Pelajaran</th>
                                    <th class="px-4 py-3 w-24 text-center">Nilai Akhir</th>
                                    <th class="px-4 py-3">Capaian Kompetensi Tertinggi & Peningkatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border">
                                @forelse($reportData['grades'] as $idx => $grade)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-3 text-center text-gray-500">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-brand-text-main">{{ $grade->subject->name ?? 'Mapel' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-1 rounded-lg font-black text-xs bg-indigo-50 text-brand-primary">
                                                {{ number_format((float) $grade->score, 0) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-brand-text-muted space-y-1">
                                            @if($grade->highest_achievement)
                                                <div class="text-[11px] leading-relaxed">
                                                    <strong class="text-emerald-700">Tercapai optimal:</strong>
                                                    <span>{{ $grade->highest_achievement }}</span>
                                                </div>
                                            @endif
                                            @if($grade->lowest_achievement)
                                                <div class="text-[11px] leading-relaxed">
                                                    <strong class="text-amber-700">Perlu pendampingan:</strong>
                                                    <span>{{ $grade->lowest_achievement }}</span>
                                                </div>
                                            @endif
                                            @if(!$grade->highest_achievement && !$grade->lowest_achievement)
                                                <span class="italic text-gray-400">Kompetensi tuntas.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">Belum ada nilai yang diinput.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid: Catatan Wali Kelas & Ekstrakurikuler -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Catatan Wali Kelas -->
                    <div class="bg-brand-surface rounded-2xl border border-brand-border p-6 shadow-sm space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-primary">draw</span>
                            <h3 class="font-bold text-sm text-brand-text-main">Catatan Wali Kelas</h3>
                        </div>
                        <div class="p-4 rounded-xl bg-gray-50 border border-brand-border text-xs italic text-brand-text-main leading-relaxed">
                            "{{ $report->homeroom_notes ?: 'Peserta didik menunjukkan perkembangan yang baik dan konsisten selama pembelajaran semester ini. Pertahankan semangat belajar dan prestasinya.' }}"
                        </div>
                        @if($report->promotion_status)
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs">
                                <span class="font-medium text-emerald-800">Status Keputusan:</span>
                                <strong class="font-extrabold text-emerald-900">{{ $report->promotion_status }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Rekap Presensi & Ekstrakurikuler -->
                    <div class="bg-brand-surface rounded-2xl border border-brand-border p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-600">sports_score</span>
                            <h3 class="font-bold text-sm text-brand-text-main">Ekstrakurikuler & Presensi</h3>
                        </div>

                        <!-- Presensi Row -->
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-100">
                                <span class="text-[10px] text-blue-700 block font-bold">Sakit (S)</span>
                                <strong class="text-sm font-black text-blue-900">{{ $reportData['attendance']['sick'] }} Hari</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-100">
                                <span class="text-[10px] text-amber-700 block font-bold">Izin (I)</span>
                                <strong class="text-sm font-black text-amber-900">{{ $reportData['attendance']['permission'] }} Hari</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-100">
                                <span class="text-[10px] text-rose-700 block font-bold">Alpa (A)</span>
                                <strong class="text-sm font-black text-rose-900">{{ $reportData['attendance']['alpha'] }} Hari</strong>
                            </div>
                        </div>

                        <!-- Ekstrakurikuler List -->
                        <div class="space-y-2 pt-2 border-t border-brand-border">
                            <span class="text-xs font-bold text-brand-text-main block">Kegiatan Pengembangan Diri:</span>
                            @forelse($reportData['extracurriculars'] as $ekskul)
                                <div class="p-2.5 rounded-xl bg-gray-50 border border-brand-border flex items-center justify-between text-xs">
                                    <span class="font-bold text-brand-text-main">{{ $ekskul->activity_name }}</span>
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $ekskul->predicate }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic">Tidak ada catatan kegiatan ekstrakurikuler.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>
