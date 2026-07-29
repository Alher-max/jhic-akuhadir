<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Orang Tua & Hubungkan Anak') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showLinkModal: false, 
        activeParentId: null, 
        activeParentName: '',
        searchQuery: '',
        selectedStudentId: '',
        selectedStudentLabel: '',
        showDropdown: false,
        allStudentsList: [
            @foreach($allStudents as $st)
            {
                id: '{{ $st->id }}',
                name: '{{ addslashes($st->name) }}',
                className: '{{ addslashes($st->schoolClass->nama_kelas ?? "Tanpa Kelas") }}',
                nisn: '{{ addslashes($st->nisn ?: "-") }}'
            },
            @endforeach
        ],
        get filteredStudents() {
            if (!this.searchQuery) return this.allStudentsList;
            const q = this.searchQuery.toLowerCase();
            return this.allStudentsList.filter(s => 
                s.name.toLowerCase().includes(q) || 
                s.className.toLowerCase().includes(q) || 
                s.nisn.toLowerCase().includes(q)
            );
        },
        selectStudent(st) {
            this.selectedStudentId = st.id;
            this.selectedStudentLabel = st.name + ' (' + st.className + ') - NISN: ' + st.nisn;
            this.showDropdown = false;
            this.searchQuery = '';
        },
        clearStudentSelection() {
            this.selectedStudentId = '';
            this.selectedStudentLabel = '';
            this.searchQuery = '';
        },
        openLinkModal(parentId, parentName) {
            this.activeParentId = parentId;
            this.activeParentName = parentName;
            this.clearStudentSelection();
            this.showLinkModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-users-line text-brand-primary"></i>
                            Daftar Orang Tua / Wali Siswa
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Kelola data akun orang tua, hubungkan dengan siswa terdaftar, atau atur ulang kata sandi.</p>
                    </div>

                    <form method="GET" action="{{ route('operator.parents.index') }}" class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau HP..." class="bg-gray-50 border border-gray-200 text-xs rounded-xl px-3.5 py-2.5 focus:ring-brand-primary focus:border-brand-primary min-w-[220px]">
                        <button type="submit" class="bg-brand-primary text-white text-xs px-4 py-2.5 rounded-xl font-semibold hover:bg-brand-primary/90 transition shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-magnifying-glass"></i> Cari
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border">Orang Tua / Wali</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border">Kontak</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border">Anak Terhubung & Hubungan</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border text-right">Aksi Management</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($parents as $parent)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-4 font-bold text-gray-900">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold shrink-0">
                                                <i class="fa-solid fa-user-shield text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-900 font-bold">{{ $parent->name }}</div>
                                                <div class="text-[11px] text-gray-400 font-mono">ID: #{{ $parent->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">
                                        <div class="font-medium text-gray-800">{{ $parent->email }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5 flex items-center gap-1">
                                            <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                            {{ $parent->parent_phone ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @forelse($parent->students as $student)
                                                <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-[11px] shadow-2xs">
                                                    <span class="font-bold text-slate-800">{{ $student->name }}</span>
                                                    <span class="text-slate-400">({{ $student->schoolClass->nama_kelas ?? 'Tanpa Kelas' }})</span>
                                                    
                                                    @php
                                                        $rel = $student->pivot->relationship ?? 'Wali';
                                                        $relBadge = match($rel) {
                                                            'Ayah' => 'bg-sky-100 text-sky-800 border-sky-200',
                                                            'Ibu' => 'bg-rose-100 text-rose-800 border-rose-200',
                                                            default => 'bg-amber-100 text-amber-800 border-amber-200'
                                                        };
                                                    @endphp
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $relBadge }}">
                                                        {{ $rel }}
                                                    </span>

                                                    <!-- Tombol Lepas Tautan -->
                                                    <form method="POST" action="{{ route('operator.parents.unlink-student', [$parent->id, $student->id]) }}" class="inline" onsubmit="return confirm('Lepas tautan {{ $student->name }} dari orang tua {{ $parent->name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-rose-600 transition ml-0.5" title="Lepas Tautan">
                                                            <i class="fa-solid fa-circle-xmark text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @empty
                                                <span class="text-gray-400 italic text-[11px]">Belum ada anak terhubung</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Tombol Hubungkan Anak -->
                                            <button type="button" 
                                                    @click="openLinkModal({{ $parent->id }}, '{{ addslashes($parent->name) }}')"
                                                    class="bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 font-semibold px-3 py-1.5 rounded-lg text-xs transition shadow-2xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-link"></i>
                                                <span>Tautkan Anak</span>
                                            </button>

                                            <!-- Tombol Reset Password -->
                                            <form method="POST" action="{{ route('operator.parents.reset-password', $parent->id) }}" class="inline" onsubmit="return confirm('Reset password akun orang tua {{ $parent->name }} ke bawaan?');">
                                                @csrf
                                                <button type="submit" class="bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium px-3 py-1.5 rounded-lg text-xs transition shadow-2xs flex items-center gap-1">
                                                    <i class="fa-solid fa-key text-amber-500"></i>
                                                    <span>Reset Password</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300"></i>
                                            <p class="font-bold text-gray-700">Belum Ada Akun Orang Tua</p>
                                            <p class="text-xs text-gray-400 mt-1">Akun orang tua akan muncul secara otomatis saat pendaftaran siswa baru atau impor siswa.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $parents->links() }}
                </div>
            </div>
        </div>

        <!-- Modal Hubungkan Anak -->
        <div x-show="showLinkModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showLinkModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-link text-brand-primary"></i>
                        <span>Hubungkan Anak ke Orang Tua</span>
                    </h3>
                    <button type="button" @click="showLinkModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times text-base"></i>
                    </button>
                </div>

                <form :action="'{{ url('/operator/parents') }}/' + activeParentId + '/link-student'" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <div class="bg-sky-50 border border-sky-100 rounded-xl p-3 text-xs text-sky-900 font-medium flex items-center justify-between">
                        <div>
                            <span class="text-sky-600">Orang Tua / Wali:</span>
                            <strong class="ml-1" x-text="activeParentName"></strong>
                        </div>
                    </div>

                    <!-- Searchable Combobox Siswa -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pilih Siswa / Anak <span class="text-rose-500">*</span>
                        </label>
                        
                        <!-- Hidden Input bound to selectedStudentId -->
                        <input type="hidden" name="student_id" x-model="selectedStudentId" required>

                        <!-- Box Tampilan Siswa yang Telah Dipilih -->
                        <template x-if="selectedStudentId">
                            <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs text-emerald-900 font-semibold shadow-2xs">
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm shrink-0"></i>
                                    <span class="truncate" x-text="selectedStudentLabel"></span>
                                </div>
                                <button type="button" @click="clearStudentSelection()" class="text-emerald-700 hover:text-rose-600 font-bold p-1 rounded transition shrink-0" title="Ganti Pilihan Siswa">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>
                        </template>

                        <!-- Live Search Input & Dropdown -->
                        <template x-if="!selectedStudentId">
                            <div class="relative" @click.away="showDropdown = false">
                                <div class="relative flex items-center">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 text-gray-400 text-xs"></i>
                                    <input type="text" 
                                           x-model="searchQuery" 
                                           @focus="showDropdown = true" 
                                           @input="showDropdown = true"
                                           placeholder="Ketik nama, NISN, atau kelas siswa..." 
                                           class="w-full bg-white border border-gray-300 rounded-xl text-xs font-medium pl-9 pr-3 py-2.5 focus:ring-brand-primary focus:border-brand-primary">
                                </div>

                                <!-- Dropdown List Siswa Hasil Live Search -->
                                <div x-show="showDropdown" x-cloak class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-56 overflow-y-auto z-50 divide-y divide-gray-100">
                                    <template x-for="st in filteredStudents" :key="st.id">
                                        <div @click="selectStudent(st)" class="p-3 hover:bg-brand-primary/5 cursor-pointer transition flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="font-bold text-gray-900 text-xs truncate" x-text="st.name"></div>
                                                <div class="text-[11px] text-gray-500 font-mono mt-0.5" x-text="'NISN: ' + st.nisn"></div>
                                            </div>
                                            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-semibold border border-slate-200 shrink-0" x-text="st.className"></span>
                                        </div>
                                    </template>
                                    
                                    <template x-if="filteredStudents.length === 0">
                                        <div class="p-4 text-center text-xs text-gray-400 font-medium">
                                            Tidak ditemukan siswa dengan kata kunci "<span class="font-bold text-gray-600" x-text="searchQuery"></span>"
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Hubungan Keluarga <span class="text-rose-500">*</span>
                        </label>
                        <select name="relationship" required class="w-full bg-white border border-gray-300 rounded-xl text-xs font-semibold p-3 focus:ring-brand-primary focus:border-brand-primary">
                            <option value="Ayah">Ayah</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Wali" selected>Wali</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showLinkModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            Simpan Tautan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
