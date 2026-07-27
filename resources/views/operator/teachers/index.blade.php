<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pendidik & Tenaga Kependidikan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ showModal: false, showImport: false, showEditModal: false, editForm: { id: null, name: '', nip: '', email: '', role: 'guru', is_active: 1, class_id: '' }, openEdit(data) { this.editForm = data; this.showEditModal = true; } }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
                <div class="mb-4 sm:mb-0">
                    <p class="text-sm text-gray-600">Tambah, edit, dan atur data guru dan staf di institusi Anda.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button @click="showImport = !showImport" class="px-4 py-2 bg-white border border-brand-primary text-brand-primary text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm inline-flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-file-csv"></i> Import CSV
                    </button>
                    <button @click="showModal = true" class="px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm inline-flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-plus"></i> Tambah Guru / Staf
                    </button>
                </div>
            </div>

            <!-- Import CSV Section (Collapsible) -->
            <div x-show="showImport" style="display: none;" class="mb-6 bg-brand-surface shadow-sm sm:rounded-xl border border-brand-border p-6" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Import Massal Data Pendidik & Staf (CSV)</h3>
                    <button @click="showImport = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times"></i></button>
                </div>
                <form action="{{ route('operator.teachers.store') }}" method="POST" enctype="multipart/form-data"
                      x-data="{ 
                          fileName: '', 
                          fileSize: '', 
                          hasFile: false,
                          isUploading: false,
                          handleFileDrop(e) {
                              if(e.dataTransfer.files.length > 0) {
                                  this.processFile(e.dataTransfer.files[0]);
                                  this.$refs.fileInput.files = e.dataTransfer.files;
                              }
                          },
                          handleFileSelect(e) {
                              if(e.target.files.length > 0) {
                                  this.processFile(e.target.files[0]);
                              }
                          },
                          processFile(file) {
                              this.fileName = file.name;
                              this.fileSize = (file.size / 1024).toFixed(2) + ' KB';
                              this.hasFile = true;
                          },
                          clearFile() {
                              this.fileName = '';
                              this.fileSize = '';
                              this.hasFile = false;
                              this.$refs.fileInput.value = '';
                          }
                      }"
                      @submit="if(!hasFile) { $event.preventDefault(); return; } isUploading = true"
                >
                    @csrf
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition-colors"
                         :class="hasFile ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:bg-gray-50 bg-white'"
                         @dragover.prevent="" @drop.prevent="handleFileDrop($event)">
                        
                        <div class="space-y-1 text-center" x-show="!hasFile">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="teachers_file" class="relative cursor-pointer bg-transparent rounded-md font-medium text-brand-primary hover:text-red-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-brand-primary">
                                    <span>Unggah file CSV</span>
                                    <input id="teachers_file" x-ref="fileInput" @change="handleFileSelect($event)" name="teachers_file" type="file" accept=".csv" required class="sr-only">
                                </label>
                                <p class="pl-1">atau tarik & lepas</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                Hanya mendukung format CSV.
                            </p>
                        </div>
                        
                        <div class="space-y-3 text-center w-full" x-show="hasFile" style="display: none;">
                            <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-2">
                                <i class="fa-solid fa-file-csv text-green-600 text-xl"></i>
                            </div>
                            <div class="text-sm font-semibold text-gray-800" x-text="fileName"></div>
                            <div class="text-xs text-gray-500" x-text="fileSize"></div>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check mr-1.5"></i> Ready to Upload
                                </span>
                            </div>
                            <div class="pt-2">
                                <button type="button" @click="clearFile()" class="text-xs text-red-600 hover:text-red-800 font-medium">Batal / Ganti File</button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <a href="{{ route('operator.teachers.download-template') }}" class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors inline-flex items-center gap-1.5 border border-gray-300">
                                <i class="fa-solid fa-download"></i> Unduh Format Template (.csv)
                            </a>
                            <button type="button" onclick="document.getElementById('sample-csv-preview').classList.toggle('hidden')" class="px-3 sm:px-4 py-2 bg-blue-50 text-brand-primary text-xs font-semibold rounded-lg hover:bg-blue-100 transition-colors inline-flex items-center gap-1.5 border border-blue-200">
                                <i class="fa-solid fa-table"></i> Lihat Contoh Format
                            </button>
                        </div>
                        <button type="submit" :disabled="!hasFile || isUploading" :class="(!hasFile || isUploading) ? 'bg-gray-400 cursor-not-allowed' : 'bg-brand-primary hover:bg-red-700'" class="px-6 py-2 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5 whitespace-nowrap">
                            <span x-show="!isUploading"><i class="fa-solid fa-upload"></i> Unggah & Proses CSV</span>
                            <span x-show="isUploading" style="display: none;"><i class="fa-solid fa-spinner fa-spin"></i> Memproses CSV...</span>
                        </button>
                    </div>

                    <!-- Preview Table (Hidden by default) -->
                    <div id="sample-csv-preview" class="hidden mt-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 text-xs font-semibold text-gray-600 flex justify-between items-center">
                            <span>Pratinjau Format Ideal (3 Baris Data)</span>
                            <button type="button" onclick="document.getElementById('sample-csv-preview').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times"></i></button>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-4 py-2 font-medium text-gray-500 whitespace-nowrap">Nama Lengkap</th>
                                    <th scope="col" class="px-4 py-2 font-medium text-gray-500 whitespace-nowrap">NUPTK</th>
                                    <th scope="col" class="px-4 py-2 font-medium text-gray-500 whitespace-nowrap">Email</th>
                                    <th scope="col" class="px-4 py-2 font-medium text-gray-500 whitespace-nowrap">Peran</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-2 font-mono text-gray-800 whitespace-nowrap">Drs. Supriyadi, M.Si.</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">197501122000031001</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">supriyadi@smkn2garuda.sch.id</td>
                                    <td class="px-4 py-2 font-mono text-brand-primary font-medium whitespace-nowrap">headmaster</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 font-mono text-gray-800 whitespace-nowrap">Hendra Gunawan, S.Kom.</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">198807092015031002</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">hendra.gunawan@smkn2garuda.sch.id</td>
                                    <td class="px-4 py-2 font-mono text-brand-primary font-medium whitespace-nowrap">it_support</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 font-mono text-gray-800 whitespace-nowrap">Siti Rahmah, S.Ag.</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">198203112006042005</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">siti.rahmah@smkn2garuda.sch.id</td>
                                    <td class="px-4 py-2 font-mono text-brand-primary font-medium whitespace-nowrap">guru</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <!-- Tabel Data Guru -->
            <form method="GET" action="{{ route('operator.teachers.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
                <!-- Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIP, atau email..." class="pl-10 pr-4 py-2 w-64 rounded-lg border-gray-300 text-sm focus:ring-rose-500 focus:border-rose-500">
                </div>
                
                <!-- Dropdown Filter Role -->
                <select name="role" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:ring-rose-500 focus:border-rose-500">
                    <option value="">-- Semua Peran / Jabatan --</option>
                    <optgroup label="Pendidik (Guru)">
                        <option value="headmaster" {{ request('role') == 'headmaster' ? 'selected' : '' }}>Kepala Sekolah</option>
                        <option value="manager_teacher" {{ request('role') == 'manager_teacher' ? 'selected' : '' }}>Guru Penggerak / Koordinator</option>
                        <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru Mapel</option>
                        <option value="guru_kelas" {{ request('role') == 'guru_kelas' ? 'selected' : '' }}>Guru Kelas</option>
                        <option value="guru_kejuruan" {{ request('role') == 'guru_kejuruan' ? 'selected' : '' }}>Guru Kejuruan</option>
                        <option value="guru_bk" {{ request('role') == 'guru_bk' ? 'selected' : '' }}>Guru BK</option>
                        <option value="guru_inklusi" {{ request('role') == 'guru_inklusi' ? 'selected' : '' }}>Guru Inklusi</option>
                        <option value="wali_kelas" {{ request('role') == 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                    </optgroup>
                    <optgroup label="Tenaga Kependidikan (Staf)">
                        <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Tata Usaha / Staf Admin</option>
                        <option value="pustakawan" {{ request('role') == 'pustakawan' ? 'selected' : '' }}>Pustakawan</option>
                        <option value="laboran" {{ request('role') == 'laboran' ? 'selected' : '' }}>Laboran</option>
                        <option value="it_support" {{ request('role') == 'it_support' ? 'selected' : '' }}>IT Support / Tim Teknis</option>
                        <option value="satpam" {{ request('role') == 'satpam' ? 'selected' : '' }}>Petugas Keamanan (Satpam)</option>
                        <option value="caraka" {{ request('role') == 'caraka' ? 'selected' : '' }}>Petugas Kebersihan (Caraka)</option>
                    </optgroup>
                </select>

                <!-- Tombol Cari & Reset Filter -->
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                    Cari
                </button>
                @if(request('role') || request('search'))
                    <a href="{{ route('operator.teachers.index') }}" class="text-xs text-rose-600 hover:underline">
                        ✕ Reset Filter
                    </a>
                @endif
            </form>

            <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Nama Lengkap</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Surel</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Peran / Jabatan</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Kelas yang Diampu (Wali)</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Status</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Status Akun</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            @forelse($teachers as $teacher)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        <div class="flex items-center gap-x-3">
                                            @php
                                                $cleanName = trim($teacher->name, '"');
                                                $nameParts = explode(' ', $cleanName);
                                                $initials = strtoupper(substr($nameParts[0], 0, 1));
                                                if (count($nameParts) > 1) {
                                                    $initials .= strtoupper(substr($nameParts[1], 0, 1));
                                                }
                                            @endphp
                                            @if($teacher->avatar || $teacher->master_photo)
                                                <img src="{{ Storage::url($teacher->avatar ?: $teacher->master_photo) }}" alt="{{ $cleanName }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-slate-200">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-700 font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                            <div>
                                                <div>{{ $cleanName }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $teacher->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @php
                                            $roleColors = [
                                                'guru_kelas' => 'bg-purple-100 text-purple-800',
                                                'guru' => 'bg-purple-100 text-purple-800',
                                                'guru_bk' => 'bg-indigo-100 text-indigo-800',
                                                'guru_inklusi' => 'bg-teal-100 text-teal-800',
                                                'guru_kejuruan' => 'bg-orange-100 text-orange-800',
                                                'wali_kelas' => 'bg-blue-100 text-blue-800',
                                                'headmaster' => 'bg-red-100 text-red-800',
                                                'manager_teacher' => 'bg-amber-100 text-amber-800',
                                                'staff' => 'bg-gray-100 text-gray-800',
                                                'pustakawan' => 'bg-cyan-100 text-cyan-800',
                                                'laboran' => 'bg-lime-100 text-lime-800',
                                                'it_support' => 'bg-slate-100 text-slate-800',
                                                'satpam' => 'bg-stone-100 text-stone-800',
                                                'caraka' => 'bg-neutral-100 text-neutral-800',
                                            ];
                                            $roleLabels = [
                                                'guru_kelas' => 'Guru Kelas',
                                                'guru' => 'Guru Mapel',
                                                'guru_bk' => 'Guru BK',
                                                'guru_inklusi' => 'Guru Inklusi',
                                                'guru_kejuruan' => 'Guru Kejuruan',
                                                'wali_kelas' => 'Wali Kelas',
                                                'headmaster' => 'Kepala Sekolah',
                                                'manager_teacher' => 'Wakasek / Manajemen',
                                                'staff' => 'Staf TU / Ops',
                                                'pustakawan' => 'Pustakawan',
                                                'laboran' => 'Laboran',
                                                'it_support' => 'IT Support',
                                                'satpam' => 'Satpam',
                                                'caraka' => 'Caraka',
                                            ];
                                            $colorClass = $roleColors[$teacher->role] ?? 'bg-gray-100 text-gray-800';
                                            $labelText = $roleLabels[$teacher->role] ?? ucfirst(str_replace('_', ' ', $teacher->role));
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium {{ $colorClass }}">{{ $labelText }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($teacher->homeroomClasses->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($teacher->homeroomClasses as $class)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $class->nama_kelas }} ({{ $class->jenjang }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($teacher->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($teacher->must_change_password)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fa-solid fa-key mr-1"></i> Kredensial Default
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i class="fa-solid fa-check-circle mr-1"></i> Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <div class="flex items-center gap-x-3 justify-end">
                                            <button type="button" @click="openEdit({ id: {{ $teacher->id }}, name: '{{ addslashes(trim($teacher->name, '"')) }}', nip: '{{ addslashes($teacher->nisn ?? '') }}', email: '{{ addslashes($teacher->email) }}', role: '{{ $teacher->role }}', is_active: {{ $teacher->is_active ? 1 : 0 }}, class_id: '{{ $teacher->homeroomClasses->first()?->id ?? '' }}' })" class="text-indigo-600 hover:text-indigo-700 font-medium text-xs transition-colors flex items-center gap-1" title="Edit Data Pendidik">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <a href="{{ route('operator.teachers.show', $teacher->id) }}" class="text-sky-600 hover:text-sky-700 font-medium text-xs transition-colors" title="Detail Profil"><i class="fa-solid fa-eye"></i> Detail</a>
                                            <form action="{{ route('operator.teachers.reset-password', $teacher->id) }}" method="POST" class="inline" onsubmit="return confirm('Reset kata sandi ke bawaan sistem?');">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-700 font-medium text-xs transition-colors" title="Reset Password"><i class="fa-solid fa-rotate-left"></i> Reset</button>
                                            </form>
                                            <form action="{{ route('operator.teachers.destroy', $teacher->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data staf ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 font-medium text-xs transition-colors"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-chalkboard-user text-4xl mb-3 text-gray-300"></i>
                                            <p class="font-medium">Belum ada data guru / tenaga pendidik.</p>
                                            <p class="text-sm mt-1">Silakan tambah guru baru melalui tombol di atas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 px-2">
                {{ $teachers->links() }}
            </div>
        </div>

        <!-- Modal Tambah Guru -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title">Tambah Guru / Pendidik Baru</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Masukkan informasi guru di bawah ini.</p>
                    </div>
                    
                    <form action="{{ route('operator.teachers.store') }}" method="POST" enctype="multipart/form-data" class="mt-5" x-data="{ createPhotoPreview: null }">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" required placeholder="Cth: Budi Santoso, S.Pd." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="nip" class="block text-sm font-medium text-gray-700">NUPTK (Opsional)</label>
                                <input type="text" name="nip" id="nip" placeholder="Masukkan NUPTK" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Surel (Opsional)</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Otomatis jika kosong">
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700">Peran / Jabatan</label>
                                <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <optgroup label="-- Pendidik (Guru) --">
                                        <option value="guru_kelas">Guru Kelas</option>
                                        <option value="guru">Guru Mata Pelajaran</option>
                                        <option value="guru_bk">Guru Bimbingan dan Konseling (BK)</option>
                                        <option value="guru_inklusi">Guru Pembimbing Khusus (Inklusi)</option>
                                        <option value="guru_kejuruan">Guru Produktif (Kejuruan)</option>
                                        <option value="wali_kelas">Wali Kelas</option>
                                    </optgroup>
                                    <optgroup label="-- Tenaga Kependidikan --">
                                        <option value="headmaster">Kepala Sekolah</option>
                                        <option value="manager_teacher">Wakil Kepala Sekolah / Manajemen</option>
                                        <option value="staff">Tenaga Administrasi Sekolah (Tata Usaha)</option>
                                        <option value="pustakawan">Tenaga Perpustakaan (Pustakawan)</option>
                                        <option value="laboran">Tenaga Laboratorium (Laboran)</option>
                                        <option value="it_support">Teknisi Sumber Belajar / IT Support</option>
                                        <option value="satpam">Petugas Keamanan (Satpam)</option>
                                        <option value="caraka">Tenaga Kebersihan (Caraka)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div>
                                <label for="teacher_avatar" class="block text-sm font-medium text-gray-700">Foto Profil / Avatar (Opsional)</label>
                                <div class="mt-1 flex items-center">
                                    <template x-if="!createPhotoPreview">
                                        <span class="inline-block h-12 w-12 flex-shrink-0 rounded-full overflow-hidden bg-gray-100">
                                            <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="createPhotoPreview">
                                        <img :src="createPhotoPreview" class="w-12 h-12 flex-shrink-0 rounded-full object-cover border border-emerald-500 shadow-sm" alt="Pratinjau Foto">
                                    </template>
                                    <input type="file" name="avatar" id="teacher_avatar" accept="image/*" @change="compressFileInput($event, (url) => { createPhotoPreview = url })" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary w-full">
                                </div>
                                <template x-if="createPhotoPreview">
                                    <div class="mt-2 flex items-center text-xs text-emerald-600 font-medium">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Pratinjau Foto Siap (Telah dikompresi secara otomatis)
                                    </div>
                                </template>
                                <p class="mt-1.5 text-xs text-gray-500">Format: JPG, PNG, WEBP. (Otomatis dikompresi di sisi klien &lt; 200KB).</p>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6">
                            <button type="button" @click="showModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal Edit Guru -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-edit-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showEditModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-edit-title">Edit Data Guru / Pendidik</h3>
                        <p class="mt-1 text-sm text-brand-text-muted">Perbarui informasi dan peran pendidik di bawah ini.</p>
                    </div>
                    
                    <form :action="'/operator/teachers/' + editForm.id" method="POST" enctype="multipart/form-data" class="mt-5" x-data="{ editPhotoPreview: null }">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="edit_name" class="block text-sm font-medium text-gray-700">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="edit_name" x-model="editForm.name" required placeholder="Cth: Drs. Budi Santoso, M.Pd." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_nip" class="block text-sm font-medium text-gray-700">NIP / NIK / NUPTK</label>
                                    <input type="text" name="nip" id="edit_nip" x-model="editForm.nip" placeholder="Masukkan NIP/NIK/NUPTK" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                </div>
                                
                                <div>
                                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Surel / Email</label>
                                    <input type="email" name="email" id="edit_email" x-model="editForm.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="email@sekolah.sch.id">
                                </div>
                            </div>

                            <div>
                                <label for="edit_role" class="block text-sm font-medium text-gray-700">Peran / Jabatan <span class="text-red-500">*</span></label>
                                <select name="role" id="edit_role" x-model="editForm.role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <optgroup label="-- Pendidik (Guru) --">
                                        <option value="guru_kelas">Guru Kelas</option>
                                        <option value="guru">Guru Mata Pelajaran</option>
                                        <option value="guru_bk">Guru Bimbingan dan Konseling (BK)</option>
                                        <option value="guru_inklusi">Guru Pembimbing Khusus (Inklusi)</option>
                                        <option value="guru_kejuruan">Guru Produktif (Kejuruan)</option>
                                        <option value="wali_kelas">Wali Kelas</option>
                                    </optgroup>
                                    <optgroup label="-- Tenaga Kependidikan --">
                                        <option value="headmaster">Kepala Sekolah</option>
                                        <option value="manager_teacher">Wakil Kepala Sekolah / Manajemen</option>
                                        <option value="staff">Tenaga Administrasi Sekolah (Tata Usaha)</option>
                                        <option value="pustakawan">Tenaga Perpustakaan (Pustakawan)</option>
                                        <option value="laboran">Tenaga Laboratorium (Laboran)</option>
                                        <option value="it_support">Teknisi Sumber Belajar / IT Support</option>
                                        <option value="satpam">Petugas Keamanan (Satpam)</option>
                                        <option value="caraka">Tenaga Kebersihan (Caraka)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Penugasan Wali Kelas -->
                            <div>
                                <label for="edit_class_id" class="block text-sm font-medium text-gray-700">Penugasan Wali Kelas (Opsional)</label>
                                <select name="class_id" id="edit_class_id" x-model="editForm.class_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="">-- Belum Ditugaskan ke Kelas --</option>
                                    @foreach($classes ?? [] as $cls)
                                        <option value="{{ $cls->id }}">
                                            {{ $cls->nama_kelas }} ({{ $cls->jenjang }} {{ $cls->tingkat }})
                                            @if($cls->wali_kelas_id && $cls->wali_kelas_id != 0)
                                                - Wali saat ini: {{ $cls->waliKelas?->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Pilih kelas jika pendidik ditugaskan sebagai Wali Kelas.</p>
                            </div>

                            <!-- Status Akun -->
                            <div>
                                <label for="edit_is_active" class="block text-sm font-medium text-gray-700">Status Akun <span class="text-red-500">*</span></label>
                                <select name="is_active" id="edit_is_active" x-model="editForm.is_active" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>

                            <div>
                                <label for="edit_teacher_avatar" class="block text-sm font-medium text-gray-700">Foto Profil / Avatar Baru (Opsional)</label>
                                <div class="mt-1 flex items-center">
                                    <template x-if="!editPhotoPreview">
                                        <span class="inline-block h-12 w-12 flex-shrink-0 rounded-full overflow-hidden bg-gray-100">
                                            <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="editPhotoPreview">
                                        <img :src="editPhotoPreview" class="w-12 h-12 flex-shrink-0 rounded-full object-cover border border-emerald-500 shadow-sm" alt="Pratinjau Foto">
                                    </template>
                                    <input type="file" name="avatar" id="edit_teacher_avatar" accept="image/*" @change="compressFileInput($event, (url) => { editPhotoPreview = url })" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary w-full">
                                </div>
                                <template x-if="editPhotoPreview">
                                    <div class="mt-2 flex items-center text-xs text-emerald-600 font-medium">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Pratinjau Foto Siap (Telah dikompresi secara otomatis)
                                    </div>
                                </template>
                                <p class="mt-1.5 text-xs text-gray-500">Format: JPG, PNG, WEBP. (Otomatis dikompresi di sisi klien &lt; 200KB).</p>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6">
                            <button type="button" @click="showEditModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
