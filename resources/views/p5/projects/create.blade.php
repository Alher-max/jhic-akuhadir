<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('p5.projects.index', $class) }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Projek Kelas {{ $class->nama_kelas }}
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Buat Projek Baru</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Buat Projek Profil Pelajar (P5 & P5RA)
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Rombel: Kelas {{ $class->nama_kelas }} &bull; Fase {{ $class->fase ?? 'E' }} &bull; TA {{ $activeYear->formatted_period }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen" x-data="p5ProjectForm()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <span class="material-symbols-outlined text-red-600 text-base">error</span>
                        <span>Terdapat kesalahan pengisian formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('p5.projects.store', $class) }}" class="space-y-6">
                @csrf

                <!-- Section 1: Informasi Dasar Projek -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-5">
                    <h3 class="text-base font-bold text-brand-text-main border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-primary">info</span>
                        <span>1. Informasi Utama Projek</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tema Projek <span class="text-red-500">*</span>
                            </label>
                            <select name="theme" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                                <option value="">-- Pilih Tema Projek --</option>
                                @foreach($themes as $theme)
                                    <option value="{{ $theme }}" {{ old('theme') === $theme ? 'selected' : '' }}>
                                        {{ $theme }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Sesuai panduan Kemendikbudristek & Kemenag.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Koordinator / Fasilitator Projek <span class="text-red-500">*</span>
                            </label>
                            <select name="coordinator_id" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ (old('coordinator_id', auth()->id()) == $teacher->id) ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->role }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Guru yang memfasilitasi dan mengampu asesmen projek ini.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Nama / Judul Projek <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required value="{{ old('title') }}"
                            placeholder="Contoh: Pemanfaatan Sampah Organik Menjadi Pupuk Kompos Ramah Lingkungan"
                            class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Deskripsi Singkat Projek <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="3" required
                            placeholder="Jelaskan secara ringkas latar belakang, permasalahan, dan tujuan pembelajaran yang ingin dicapai melalui projek ini..."
                            class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Section 2: Dimensi & Sub-elemen Target Projek -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-brand-text-main flex items-center gap-2">
                                <span class="material-symbols-outlined text-brand-primary">track_changes</span>
                                <span>2. Target Dimensi & Sub-elemen Capaian</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Tentukan dimensi profil Pancasila dan/atau nilai Rahmatan Lil 'Alamin yang dinilai pada projek ini.
                            </p>
                        </div>

                        <!-- Tombol Tambah Manual & Quick Preset -->
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openPresetModal = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition">
                                <span class="material-symbols-outlined text-[16px]">library_add</span>
                                <span>Pilih dari Preset</span>
                            </button>
                            <button type="button" @click="addTarget('pancasila')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>
                    </div>

                    <!-- Target Items List -->
                    <div class="space-y-4">
                        <template x-for="(target, index) in targets" :key="index">
                            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/60 space-y-3 relative group">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black uppercase tracking-wider text-brand-primary"
                                        x-text="'Target #' + (index + 1) + ' (' + (target.target_type === 'rahmatan_lil_alamin' ? 'P5RA Kemenag' : 'Pancasila') + ')'">
                                    </span>
                                    <button type="button" @click="removeTarget(index)"
                                        class="text-red-500 hover:text-red-700 text-xs font-bold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Tipe Standar</label>
                                        <select :name="'targets[' + index + '][target_type]'" x-model="target.target_type"
                                            class="w-full text-xs rounded-lg border-gray-300">
                                            <option value="pancasila">6 Dimensi Pancasila</option>
                                            <option value="rahmatan_lil_alamin">10 Nilai Rahmatan Lil 'Alamin</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Dimensi / Nilai Utama</label>
                                        <input type="text" :name="'targets[' + index + '][dimension]'" x-model="target.dimension" required
                                            placeholder="Misal: Gotong Royong / Tawassut"
                                            class="w-full text-xs rounded-lg border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Elemen (Opsional)</label>
                                        <input type="text" :name="'targets[' + index + '][element]'" x-model="target.element"
                                            placeholder="Misal: Kolaborasi"
                                            class="w-full text-xs rounded-lg border-gray-300">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Sub-elemen Target <span class="text-red-500">*</span></label>
                                        <input type="text" :name="'targets[' + index + '][sub_element]'" x-model="target.sub_element" required
                                            placeholder="Misal: Kerja Sama dan Koordinasi Positif"
                                            class="w-full text-xs rounded-lg border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Deskripsi Capaian Fase Akhir</label>
                                        <input type="text" :name="'targets[' + index + '][target_description]'" x-model="target.target_description"
                                            placeholder="Target capaian perilaku siswa pada fase ini..."
                                            class="w-full text-xs rounded-lg border-gray-300">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="targets.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl">
                            <span class="material-symbols-outlined text-gray-400 text-3xl mb-1">playlist_add</span>
                            <p class="text-xs text-gray-500">Belum ada target capaian. Klik tombol <strong>Pilih dari Preset</strong> di atas untuk menambahkan target secara cepat.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('p5.projects.index', $class) }}"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Simpan & Buat Projek</span>
                    </button>
                </div>
            </form>

            <!-- Preset Modal Quick Picker -->
            <div x-show="openPresetModal" style="display: none;"
                class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
                <div @click.away="openPresetModal = false"
                    class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h4 class="font-extrabold text-base text-gray-900">Pilih Preset Dimensi & Sub-elemen</h4>
                        <button type="button" @click="openPresetModal = false" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Preset Type Tabs -->
                    <div class="flex items-center gap-2 border-b pb-2">
                        <button type="button" @click="presetTab = 'pancasila'"
                            :class="presetTab === 'pancasila' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                            6 Dimensi Pancasila (Kemendikbud)
                        </button>
                        <button type="button" @click="presetTab = 'kemenag'"
                            :class="presetTab === 'kemenag' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                            10 Nilai Rahmatan Lil 'Alamin (Kemenag)
                        </button>
                    </div>

                    <!-- Preset List -->
                    <div class="overflow-y-auto flex-1 space-y-4 pr-1">
                        <!-- Pancasila Preset -->
                        <div x-show="presetTab === 'pancasila'" class="space-y-4">
                            @foreach($pancasilaDimensions as $dimName => $dimData)
                                <div class="border rounded-xl p-3 bg-gray-50/50">
                                    <h5 class="text-xs font-black text-indigo-900 mb-2">{{ $dimName }}</h5>
                                    <div class="space-y-2">
                                        @foreach($dimData['elements'] as $elemName => $subElems)
                                            <div class="text-[11px] text-gray-600 pl-2 border-l-2 border-indigo-200">
                                                <span class="font-bold text-gray-800">{{ $elemName }}:</span>
                                                <div class="mt-1 flex flex-wrap gap-1.5">
                                                    @foreach($subElems as $subElem)
                                                        <button type="button"
                                                            @click="insertPreset('pancasila', '{{ addslashes($dimName) }}', '{{ addslashes($elemName) }}', '{{ addslashes($subElem) }}')"
                                                            class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50 text-[11px] text-gray-700 transition flex items-center gap-1">
                                                            <span class="material-symbols-outlined text-[13px] text-indigo-500">add</span>
                                                            <span>{{ $subElem }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Kemenag Preset -->
                        <div x-show="presetTab === 'kemenag'" class="space-y-4">
                            @foreach($rahmatanValues as $valName => $valData)
                                <div class="border rounded-xl p-3 bg-gray-50/50">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="text-xs font-black text-emerald-900">{{ $valName }} ({{ $valData['translation'] }})</h5>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($valData['sub_values'] as $subVal)
                                            <button type="button"
                                                @click="insertPreset('rahmatan_lil_alamin', '{{ addslashes($valName . ' (' . $valData['translation'] . ')') }}', 'Nilai Karakter Kemenag', '{{ addslashes($subVal) }}')"
                                                class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-emerald-400 hover:bg-emerald-50 text-[11px] text-gray-700 transition flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[13px] text-emerald-500">add</span>
                                                <span>{{ $subVal }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t pt-3 flex justify-end">
                        <button type="button" @click="openPresetModal = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function p5ProjectForm() {
            return {
                openPresetModal: false,
                presetTab: 'pancasila',
                targets: [
                    {
                        target_type: 'pancasila',
                        dimension: 'Gotong Royong',
                        element: 'Kolaborasi',
                        sub_element: 'Kerja Sama dan Koordinasi Positif',
                        target_description: 'Menunjukkan inisiatif untuk bekerja sama demi mencapai tujuan bersama.'
                    }
                ],
                addTarget(type = 'pancasila') {
                    this.targets.push({
                        target_type: type,
                        dimension: '',
                        element: '',
                        sub_element: '',
                        target_description: ''
                    });
                },
                removeTarget(index) {
                    this.targets.splice(index, 1);
                },
                insertPreset(type, dim, elem, sub) {
                    this.targets.push({
                        target_type: type,
                        dimension: dim,
                        element: elem,
                        sub_element: sub,
                        target_description: 'Mencapai fase perkembangan optimal dalam sub-elemen ini.'
                    });
                    this.openPresetModal = false;
                }
            }
        }
    </script>
</x-app-layout>
