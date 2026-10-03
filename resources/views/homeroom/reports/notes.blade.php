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
                    Catatan Perkembangan & Kenaikan Kelas: {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Input Catatan Wali Kelas, Motivasi Karakter, & Keputusan Status Kenaikan Kelas Siswa
                </p>
            </div>
            @if($activeYear)
                <div class="px-3.5 py-2 rounded-xl bg-purple-50 border border-purple-200 text-purple-800 text-xs font-bold">
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
                    class="px-4 py-2 rounded-xl bg-brand-primary text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
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

            <!-- Info Guide Card -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border p-5 shadow-sm flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">psychology</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-brand-text-main">Panduan Catatan Wali Kelas & Status Kenaikan</h3>
                    <p class="text-xs text-brand-text-muted mt-1 leading-relaxed">
                        Catatan wali kelas memuat narasi deskriptif mengenai sikap, kebiasaan belajar, dan motivasi peserta didik untuk dicetak pada lembar rapor resmi. Status kenaikan umumnya diisi pada semester genap (Semester 2), atau saat kelulusan tingkat akhir.
                    </p>
                </div>
            </div>

            <!-- Main Notes Form -->
            <form method="POST" action="{{ route('homeroom.reports.batch-update', $class) }}">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class->id }}">

                <div class="space-y-4">
                    @forelse($students as $index => $student)
                        @php
                            $report = $reports->get($student->id);
                        @endphp
                        <div class="bg-brand-surface rounded-2xl border border-brand-border p-6 shadow-sm hover:border-brand-primary/40 transition">
                            <input type="hidden" name="reports[{{ $index }}][student_id]" value="{{ $student->id }}">

                            <!-- Student Header Information -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-brand-border gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm shrink-0">
                                        {{ substr($student->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-brand-text-main">{{ $student->name }}</h4>
                                        <div class="flex items-center gap-2 text-xs text-brand-text-muted">
                                            <span>NISN: {{ $student->nisn ?? $student->nis ?? '-' }}</span>
                                            <span>•</span>
                                            <span class="capitalize">Status Lembar: </span>
                                            <span class="font-semibold {{ ($report?->status === 'locked') ? 'text-rose-600' : (($report?->status === 'submitted') ? 'text-indigo-600' : 'text-amber-600') }}">
                                                {{ ucfirst($report?->status ?? 'draft') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <!-- Status Rapor -->
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-medium text-brand-text-muted">Status Rapor:</label>
                                        <select name="reports[{{ $index }}][status]"
                                            class="text-xs font-semibold rounded-lg border-brand-border py-1.5 px-3 bg-brand-bg text-brand-text-main focus:ring-brand-primary focus:border-brand-primary">
                                            <option value="draft" {{ ($report?->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="submitted" {{ ($report?->status ?? '') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                            <option value="locked" {{ ($report?->status ?? '') === 'locked' ? 'selected' : '' }}>Locked</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Fields Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-4">
                                <!-- Homeroom Notes (2 cols) -->
                                <div class="lg:col-span-2 space-y-1.5">
                                    <label class="block text-xs font-bold text-brand-text-main flex items-center justify-between">
                                        <span>Catatan Perkembangan Karakter & Motivasi Wali Kelas</span>
                                        <span class="text-[11px] font-normal text-brand-text-muted">Maks. 2000 karakter</span>
                                    </label>
                                    <textarea name="reports[{{ $index }}][homeroom_notes]" rows="3"
                                        placeholder="Contoh: Ananda menunjukkan perkembangan yang sangat baik dalam kemandirian belajar dan kerja sama tim. Tingkatkan terus kedisiplinan dan rasa percaya diri dalam berdiskusi."
                                        class="w-full text-xs rounded-xl border-brand-border bg-white text-brand-text-main placeholder-gray-400 p-3 focus:ring-brand-primary focus:border-brand-primary transition leading-relaxed">{{ old("reports.{$index}.homeroom_notes", $report?->homeroom_notes ?? '') }}</textarea>
                                </div>

                                <!-- Promotion Status (1 col) -->
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-brand-text-main">
                                        Keputusan Kenaikan / Kelulusan
                                    </label>
                                    <input type="text" name="reports[{{ $index }}][promotion_status]"
                                        value="{{ old("reports.{$index}.promotion_status", $report?->promotion_status ?? '') }}"
                                        placeholder="Contoh: Naik ke Kelas XI / Lulus"
                                        class="w-full text-xs rounded-xl border-brand-border bg-white text-brand-text-main placeholder-gray-400 p-3 focus:ring-brand-primary focus:border-brand-primary transition">
                                    <p class="text-[11px] text-brand-text-muted leading-tight">
                                        Kosongkan jika masih berada di Semester Ganjil (Semester 1).
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-brand-surface rounded-2xl border border-brand-border p-12 text-center shadow-sm">
                            <span class="material-symbols-outlined text-4xl text-gray-300">group_off</span>
                            <h4 class="font-bold text-base text-brand-text-main mt-2">Tidak Ada Siswa di Kelas Ini</h4>
                            <p class="text-xs text-brand-text-muted mt-1">Tambahkan siswa terlebih dahulu pada rombel kelas ini.</p>
                        </div>
                    @endforelse
                </div>

                @if($students->isNotEmpty())
                    <!-- Sticky Save Bar -->
                    <div class="sticky bottom-4 mt-6 z-10">
                        <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-brand-border p-4 shadow-xl flex items-center justify-between">
                            <div class="text-xs text-brand-text-muted flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600 text-base">verified</span>
                                <span>Pastikan seluruh catatan sudah ditinjau sebelum menyimpan.</span>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-primary text-white font-extrabold text-xs rounded-xl hover:bg-brand-primary/90 shadow-md transition">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <span>Simpan Semua Catatan & Status</span>
                            </button>
                        </div>
                    </div>
                @endif
            </form>

        </div>
    </div>
</x-app-layout>
