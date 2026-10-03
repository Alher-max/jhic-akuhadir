<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('vocational.internships.index', $placement->schoolClass) }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Daftar Penempatan PKL
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Penilaian PKL</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Lembar Penilaian PKL: {{ $placement->student?->name }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    {{ $placement->company_name }} &bull; Kelas {{ $placement->schoolClass?->nama_kelas }} &bull; Periode: {{ $placement->start_date->format('d M Y') }} - {{ $placement->end_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($placement->assessment)
                    <a href="{{ route('vocational.internships.print', $placement) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">print</span>
                        <span>Cetak Nilai Resmi (A4)</span>
                    </a>
                @endif
                <button type="submit" form="assessmentForm"
                    class="inline-flex items-center gap-1.5 px-5 py-2 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Simpan Nilai</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen" x-data="internshipAssessmentForm()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <span class="material-symbols-outlined text-red-600 text-base">error</span>
                        <span>Terdapat kesalahan input:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Ringkasan Penempatan & Geofence Card -->
            <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-extrabold">
                            {{ $placement->company_name }}
                        </span>
                        @if($placement->industryLocation)
                            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">my_location</span>
                                <span>Geofence: {{ $placement->industryLocation->name }}</span>
                            </span>
                        @endif
                    </div>
                    <h3 class="font-extrabold text-base text-gray-900">{{ $placement->student?->name }}</h3>
                    <p class="text-xs text-gray-500">
                        Pembimbing Lapangan: <span class="font-bold text-gray-700">{{ $placement->mentor_name ?? '-' }}</span> ({{ $placement->mentor_position ?? 'DUDI' }}) &bull; 
                        Guru Pembimbing: <span class="font-bold text-gray-700">{{ $placement->teacherSupervisor?->name }}</span>
                    </p>
                </div>

                <!-- Widget Sinkronisasi Presensi HadirYuk -->
                <div class="bg-indigo-50/70 border border-indigo-200 p-3.5 rounded-xl w-full md:w-auto text-right">
                    <div class="text-[11px] text-indigo-700 font-bold mb-1">Presensi HadirYuk Terhitung:</div>
                    <div class="flex items-center justify-end gap-2">
                        <span class="text-2xl font-black text-indigo-900">{{ number_format($calculatedAttendanceScore, 1) }}%</span>
                        <button type="button" @click="syncAttendance()"
                            class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-lg transition shadow-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">sync</span>
                            <span>Terapkan</span>
                        </button>
                    </div>
                    <div class="text-[10px] text-gray-500 mt-1">Dihitung otomatis dari log absensi periode PKL</div>
                </div>
            </div>

            <!-- Form Penilaian PKL -->
            <form id="assessmentForm" method="POST" action="{{ route('vocational.internships.assessment.store', $placement) }}" class="space-y-6">
                @csrf

                <!-- Skor & Bobot Evaluasi -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-5">
                    <h3 class="text-base font-bold text-brand-text-main border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-primary">analytics</span>
                            <span>Komponen Penilaian Terbobot</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-400">Skala 0 - 100</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- 1. Kompetensi Teknis (50%) -->
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-extrabold text-gray-800">1. Aspek Teknis Kejuruan</label>
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-black text-[10px]">Bobot 50%</span>
                            </div>
                            <p class="text-[11px] text-gray-500">Keterampilan teknis, pemecahan masalah, & hasil kerja praktis.</p>
                            <input type="number" step="0.01" min="0" max="100" name="technical_score" required
                                x-model.number="technicalScore" @input="updateCalculations()"
                                placeholder="0 - 100"
                                class="w-full text-base font-bold rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>

                        <!-- 2. Budaya Kerja / Softskill (30%) -->
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-extrabold text-gray-800">2. Budaya Kerja & Softskill</label>
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-black text-[10px]">Bobot 30%</span>
                            </div>
                            <p class="text-[11px] text-gray-500">Kedisiplinan, integritas, komunikasi, K3, & inisiatif kerja.</p>
                            <input type="number" step="0.01" min="0" max="100" name="softskill_score" required
                                x-model.number="softskillScore" @input="updateCalculations()"
                                placeholder="0 - 100"
                                class="w-full text-base font-bold rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>

                        <!-- 3. Rekap Kehadiran (20%) -->
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-extrabold text-gray-800">3. Rekapitulasi Presensi</label>
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-black text-[10px]">Bobot 20%</span>
                            </div>
                            <p class="text-[11px] text-gray-500">Tingkat kehadiran di industri via log HadirYuk.</p>
                            <input type="number" step="0.01" min="0" max="100" name="attendance_score" required
                                x-model.number="attendanceScore" @input="updateCalculations()"
                                placeholder="0 - 100"
                                class="w-full text-base font-bold rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>
                    </div>

                    <!-- Live Calculated Final Score Banner -->
                    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <div class="text-xs font-bold text-indigo-700 uppercase tracking-wide">Kalkulasi Nilai Akhir PKL Terbobot:</div>
                            <div class="text-xs text-indigo-600 mt-0.5">Rumus: (50% &times; Teknis) + (30% &times; Softskill) + (20% &times; Presensi)</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-2xl font-black text-indigo-900" x-text="finalScore.toFixed(2)"></div>
                                <div class="text-[11px] font-extrabold" :class="predicateClass" x-text="predicate"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Evaluasi & Deskripsi Kinerja -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-5">
                    <h3 class="text-base font-bold text-brand-text-main border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-primary">rate_review</span>
                        <span>Catatan & Rekomendasi Instruktur Industri</span>
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Deskripsi Capaian Kompetensi Teknis di Industri
                        </label>
                        <textarea name="technical_notes" rows="3"
                            placeholder="Jelaskan keterampilan kejuruan yang berhasil dikuasai siswa selama penempatan..."
                            class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">{{ old('technical_notes', $placement->assessment?->technical_notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Catatan Budaya Kerja, Etika, & Rekomendasi Karir
                        </label>
                        <textarea name="softskill_notes" rows="3"
                            placeholder="Catatan mengenai kedisiplinan kerja, etika profesional, dan potensi kerja siswa di masa depan..."
                            class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">{{ old('softskill_notes', $placement->assessment?->softskill_notes) }}</textarea>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('vocational.internships.index', $placement->schoolClass) }}"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Simpan Penilaian PKL</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function internshipAssessmentForm() {
            return {
                technicalScore: {{ old('technical_score', $placement->assessment?->technical_score ?? 85) }},
                softskillScore: {{ old('softskill_score', $placement->assessment?->softskill_score ?? 85) }},
                attendanceScore: {{ old('attendance_score', $placement->assessment?->attendance_score ?? $calculatedAttendanceScore) }},
                finalScore: {{ $placement->assessment?->final_score ?? 85 }},
                predicate: '{{ $placement->assessment?->predicate ?? "Sangat Baik" }}',
                predicateClass: 'text-emerald-700',

                init() {
                    this.updateCalculations();
                },

                syncAttendance() {
                    this.attendanceScore = {{ round($calculatedAttendanceScore, 2) }};
                    this.updateCalculations();
                },

                updateCalculations() {
                    const tech = parseFloat(this.technicalScore) || 0;
                    const soft = parseFloat(this.softskillScore) || 0;
                    const att = parseFloat(this.attendanceScore) || 0;

                    const final = (tech * 0.50) + (soft * 0.30) + (att * 0.20);
                    this.finalScore = Math.round(final * 100) / 100;

                    if (this.finalScore >= 85) {
                        this.predicate = 'Sangat Baik';
                        this.predicateClass = 'text-emerald-700';
                    } else if (this.finalScore >= 75) {
                        this.predicate = 'Baik';
                        this.predicateClass = 'text-blue-700';
                    } else {
                        this.predicate = 'Cukup';
                        this.predicateClass = 'text-amber-700';
                    }
                }
            }
        }
    </script>
</x-app-layout>
