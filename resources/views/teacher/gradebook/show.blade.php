<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('teacher.gradebook.index') }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Kembali ke Daftar Kelas
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Kelas {{ $class->nama_kelas }}</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Buku Nilai: {{ $subject->name }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Kelas {{ $class->nama_kelas }} ({{ $class->jenjang }} - Tingkat {{ $class->tingkat }}{{ $class->fase ? ' / Fase ' . $class->fase : '' }}) &bull; {{ $students->count() }} Siswa Terdaftar
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div x-data="{ exportOpen: false }" class="relative">
                    <button type="button" @click="exportOpen = !exportOpen" @click.away="exportOpen = false"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-gray-700 text-xs font-bold rounded-xl border border-gray-300 hover:bg-gray-50 shadow-sm transition">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">download</span>
                        <span>Ekspor Nilai</span>
                        <span class="material-symbols-outlined text-[16px] text-gray-400">expand_more</span>
                    </button>
                    <div x-show="exportOpen" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-30">
                        <a href="{{ route('teacher.gradebook.export-erapor', [$class, $subject]) }}"
                            class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 font-semibold transition">
                            <span class="material-symbols-outlined text-[18px] text-indigo-600">table_view</span>
                            <span>Format e-Rapor SP (CSV)</span>
                        </a>
                        <a href="{{ route('teacher.gradebook.export-rdm', [$class, $subject]) }}"
                            class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-semibold transition">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
                            <span>Format RDM Kemenag (CSV)</span>
                        </a>
                    </div>
                </div>

                <button type="button" @click="showTpModal = true"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white text-gray-700 text-xs font-bold rounded-xl border border-gray-300 hover:bg-gray-50 shadow-sm transition">
                    <span class="material-symbols-outlined text-[18px] text-brand-primary">playlist_add</span>
                    <span>Kelola / Tambah TP</span>
                </button>
                @if($activeYear)
                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        TA: {{ $activeYear->formatted_period }}
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen"
        x-data="{
            showTpModal: false,
            learningObjectives: {{ Js::from($learningObjectives) }},
            students: {{ Js::from($students->map(function($student) use ($existingGrades) {
                $grade = $existingGrades->get($student->id);
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? '-',
                    'score' => $grade ? (float) $grade->score : '',
                    'highest_achievement' => $grade ? ($grade->highest_achievement ?? '') : '',
                    'lowest_achievement' => $grade ? ($grade->lowest_achievement ?? '') : '',
                    'selected_highest_tp' => '',
                    'selected_lowest_tp' => '',
                ];
            })) }},
            applyHighestTp(idx, tpId) {
                const tp = this.learningObjectives.find(t => String(t.id) === String(tpId));
                if (tp) {
                    const desc = tp.description.trim();
                    const text = 'Menunjukkan penguasaan yang sangat baik dalam ' + desc.charAt(0).toLowerCase() + desc.slice(1);
                    this.students[idx].highest_achievement = text;
                }
            },
            applyLowestTp(idx, tpId) {
                const tp = this.learningObjectives.find(t => String(t.id) === String(tpId));
                if (tp) {
                    const desc = tp.description.trim();
                    const text = 'Perlu bimbingan dan peningkatan dalam ' + desc.charAt(0).toLowerCase() + desc.slice(1);
                    this.students[idx].lowest_achievement = text;
                }
            }
        }">
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

            @if ($errors->any())
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl shadow-sm">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1 text-amber-800">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                        Perhatian: Terdapat kesalahan pengisian nilai:
                    </div>
                    <ul class="list-disc list-inside text-xs text-amber-800 space-y-0.5 ml-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Warning if no learning objectives registered -->
            <template x-if="learningObjectives.length === 0">
                <div class="p-4 bg-sky-50 border border-sky-200 rounded-xl shadow-sm flex items-start gap-3">
                    <span class="material-symbols-outlined text-sky-600 mt-0.5">tips_and_updates</span>
                    <div class="text-xs text-sky-900">
                        <span class="font-bold">Tips Smart Generator:</span> Belum ada Tujuan Pembelajaran (TP) yang didaftarkan untuk mata pelajaran ini. Klik tombol <button type="button" @click="showTpModal = true" class="underline font-bold text-sky-700 hover:text-sky-900">"Kelola / Tambah TP"</button> di atas agar Anda dapat memilih TP untuk menghasilkan narasi capaian otomatis secara instan.
                    </div>
                </div>
            </template>

            <!-- Grade Sheet Table Form -->
            <form method="POST" action="{{ route('teacher.gradebook.store') }}">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class->id }}">
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">

                <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-base text-brand-text-main">Lembar Penilaian Siswa</h3>
                            <p class="text-xs text-brand-text-muted mt-0.5">
                                Nilai berskala 0 hingga 100. Pilihan TP akan otomatis mengisi draft deskripsi capaian siswa.
                            </p>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary text-white text-sm font-bold rounded-xl hover:bg-red-700 shadow-sm transition">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Simpan Semua Nilai</span>
                        </button>
                    </div>

                    @if($students->isEmpty())
                        <div class="py-16 px-6 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-[32px]">group_off</span>
                            </div>
                            <h4 class="text-base font-bold text-brand-text-main">Tidak Ada Siswa di Kelas Ini</h4>
                            <p class="text-sm text-brand-text-muted max-w-md mx-auto mt-1">
                                Belum ada siswa yang dimasukkan ke dalam rombel {{ $class->nama_kelas }}. Hubungi operator untuk penempatan siswa.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/90 text-gray-600 text-xs uppercase tracking-wider border-b border-brand-border font-bold">
                                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                                        <th class="px-4 py-3.5 w-64">Nama & NISN</th>
                                        <th class="px-4 py-3.5 w-28 text-center">Nilai (0-100)</th>
                                        <th class="px-4 py-3.5">Capaian Kompetensi Tertinggi</th>
                                        <th class="px-4 py-3.5">Capaian Kompetensi Terendah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-border text-sm">
                                    <template x-for="(st, idx) in students" :key="st.id">
                                        <tr class="hover:bg-gray-50/70 transition align-top">
                                            <!-- Nomor Urut -->
                                            <td class="px-4 py-4 text-center font-bold text-gray-500" x-text="idx + 1"></td>

                                            <!-- Identitas Siswa -->
                                            <td class="px-4 py-4">
                                                <input type="hidden" :name="'grades[' + idx + '][student_id]'" :value="st.id">
                                                <div class="font-bold text-brand-text-main" x-text="st.name"></div>
                                                <div class="text-xs text-gray-500 font-mono mt-0.5">
                                                    NISN: <span x-text="st.nisn"></span>
                                                </div>
                                            </td>

                                            <!-- Nilai Akhir -->
                                            <td class="px-4 py-4 text-center">
                                                <input type="number" step="0.01" min="0" max="100" required
                                                    :name="'grades[' + idx + '][score]'"
                                                    x-model="st.score"
                                                    placeholder="0-100"
                                                    class="w-24 text-center text-sm font-bold rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                                            </td>

                                            <!-- Capaian Tertinggi -->
                                            <td class="px-4 py-4 space-y-2">
                                                <!-- Generator Picker TP Tertinggi -->
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[14px]">arrow_upward</span>
                                                        Pilih TP:
                                                    </span>
                                                    <select class="text-xs rounded-lg border-gray-200 py-1 px-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-700 bg-emerald-50/30 flex-1"
                                                        @change="applyHighestTp(idx, $event.target.value)">
                                                        <option value="">-- Pilih TP Tertinggi --</option>
                                                        <template x-for="tp in learningObjectives" :key="tp.id">
                                                            <option :value="tp.id" x-text="tp.code + ': ' + tp.description.substring(0, 50) + '...'"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <!-- Textarea Capaian Tertinggi -->
                                                <textarea :name="'grades[' + idx + '][highest_achievement]'"
                                                    x-model="st.highest_achievement"
                                                    rows="2"
                                                    placeholder="Deskripsi capaian penguasaan materi terbaik siswa..."
                                                    class="w-full text-xs rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"></textarea>
                                            </td>

                                            <!-- Capaian Terendah -->
                                            <td class="px-4 py-4 space-y-2">
                                                <!-- Generator Picker TP Terendah -->
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[14px]">arrow_downward</span>
                                                        Pilih TP:
                                                    </span>
                                                    <select class="text-xs rounded-lg border-gray-200 py-1 px-2 focus:ring-rose-500 focus:border-rose-500 text-gray-700 bg-rose-50/30 flex-1"
                                                        @change="applyLowestTp(idx, $event.target.value)">
                                                        <option value="">-- Pilih TP Perlu Peningkatan --</option>
                                                        <template x-for="tp in learningObjectives" :key="tp.id">
                                                            <option :value="tp.id" x-text="tp.code + ': ' + tp.description.substring(0, 50) + '...'"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <!-- Textarea Capaian Terendah -->
                                                <textarea :name="'grades[' + idx + '][lowest_achievement]'"
                                                    x-model="st.lowest_achievement"
                                                    rows="2"
                                                    placeholder="Deskripsi materi yang membutuhkan pendampingan..."
                                                    class="w-full text-xs rounded-xl border-gray-200 focus:border-rose-500 focus:ring-rose-500 shadow-sm"></textarea>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-5 border-t border-brand-border bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <span class="text-xs text-gray-500">
                                Pastikan seluruh nilai terisi dengan teliti sebelum menyimpan.
                            </span>
                            <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-brand-primary text-white text-sm font-bold rounded-xl hover:bg-red-700 shadow-sm transition">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <span>Simpan Semua Nilai Rombel</span>
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- ================= MODAL KELOLA TUJUAN PEMBELAJARAN (TP) ================= -->
        <div x-cloak x-show="showTpModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showTpModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showTpModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showTpModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-brand-border">

                    <div class="flex justify-between items-center pb-4 border-b border-brand-border">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-primary">playlist_add</span>
                            <div>
                                <h3 class="text-lg font-bold text-brand-text-main">Tujuan Pembelajaran (TP)</h3>
                                <p class="text-xs text-brand-text-muted">Mata Pelajaran: {{ $subject->name }} &bull; Rombel: {{ $class->nama_kelas }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showTpModal = false" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Form Tambah TP -->
                    <form method="POST" action="{{ route('teacher.learning-objectives.store') }}" class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <input type="hidden" name="class_id" value="{{ $class->id }}">

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kode TP *</label>
                                <input type="text" name="code" placeholder="TP 1" required
                                    class="w-full text-xs rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi Kompetensi *</label>
                                <input type="text" name="description" placeholder="Contoh: menganalisis struktur teks eksplanasi secara kritis" required
                                    class="w-full text-xs rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-primary text-white text-xs font-bold rounded-xl hover:bg-red-700 shadow-sm transition">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                <span>Tambah TP</span>
                            </button>
                        </div>
                    </form>

                    <!-- Daftar TP Eksisting -->
                    <div class="mt-6">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Daftar TP Terdaftar:</h4>
                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                            <template x-for="tp in learningObjectives" :key="tp.id">
                                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-200 hover:border-gray-300 transition gap-3">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 text-[10px] font-black rounded bg-red-50 text-brand-primary border border-red-100" x-text="tp.code"></span>
                                            <span class="text-xs text-gray-500" x-text="tp.class_id ? 'Khusus Kelas {{ $class->nama_kelas }}' : 'Berlaku Seluruh Kelas'"></span>
                                        </div>
                                        <p class="text-xs text-gray-800" x-text="tp.description"></p>
                                    </div>
                                    <form :action="'/teacher/learning-objectives/' + tp.id" method="POST" onsubmit="return confirm('Hapus TP ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-gray-400 hover:text-red-600 rounded transition" title="Hapus TP">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </template>
                            <template x-if="learningObjectives.length === 0">
                                <p class="text-xs text-gray-400 italic text-center py-4">Belum ada TP yang ditambahkan.</p>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-brand-border flex justify-end">
                        <button type="button" @click="showTpModal = false"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
