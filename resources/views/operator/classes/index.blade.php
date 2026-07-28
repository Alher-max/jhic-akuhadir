<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Kelas / Rombel') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ 
        showCreateModal: {{ $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false' }}, 
        showEditClassModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }}, 
        editClassForm: { 
            id: '{{ old('_method') === 'PUT' ? old('id', '') : '' }}', 
            jenjang: '{{ old('_method') === 'PUT' ? old('jenjang', '') : '' }}', 
            tingkat: '{{ old('_method') === 'PUT' ? old('tingkat', '') : '' }}', 
            nama_kelas: '{{ old('_method') === 'PUT' ? addslashes(old('nama_kelas', '')) : '' }}', 
            wali_kelas_id: '{{ old('_method') === 'PUT' ? old('wali_kelas_id', '') : '' }}' 
        }, 
        showImport: false 
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
                <div class="mb-4 sm:mb-0">
                    <p class="text-sm text-gray-600">Tambah, edit, dan atur rombongan belajar (rombel) di institusi Anda.</p>
                </div>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <button @click="showImport = !showImport" class="px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors border border-gray-300 shadow-sm">
                        Import CSV
                    </button>
                    <button @click="showCreateModal = true" class="px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        + Tambah Kelas Baru
                    </button>
                </div>
            </div>

            <!-- Import CSV Section (Collapsible) -->
            <div x-show="showImport" style="display: none;" class="mb-6 bg-brand-surface shadow-sm sm:rounded-xl border border-brand-border p-6" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Import Massal Data Murid ke Kelas (CSV)</h3>
                    <button @click="showImport = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times"></i></button>
                </div>
                <form action="{{ route('operator.classes.import-students') }}" method="POST" enctype="multipart/form-data"
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
                    
                    <div class="mb-4">
                        <label for="import_class_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas Tujuan <span class="text-red-500">*</span></label>
                        <select name="class_id" id="import_class_id" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($groupedClasses as $jenjang => $tingkatClasses)
                                <optgroup label="Jenjang {{ $jenjang }}">
                                    @foreach($tingkatClasses as $tingkat => $classes)
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->nama_kelas }} (Tingkat {{ $tingkat }})</option>
                                        @endforeach
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition-colors"
                         :class="hasFile ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:bg-gray-50 bg-white'"
                         @dragover.prevent="" @drop.prevent="handleFileDrop($event)">
                        
                        <div class="space-y-1 text-center" x-show="!hasFile">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="students_file_import" class="relative cursor-pointer bg-transparent rounded-md font-medium text-brand-primary hover:text-red-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-brand-primary">
                                    <span>Unggah file CSV</span>
                                    <input id="students_file_import" x-ref="fileInput" @change="handleFileSelect($event)" name="students_file" type="file" accept=".csv" required class="sr-only">
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
                            <button type="button" onclick="alert('Belum ada tautan template murid untuk saat ini.')" class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors inline-flex items-center gap-1.5 border border-gray-300">
                                <i class="fa-solid fa-download"></i> Unduh Format Template (.csv)
                            </button>
                            <button type="button" onclick="document.getElementById('sample-csv-preview-murid').classList.toggle('hidden')" class="px-3 sm:px-4 py-2 bg-blue-50 text-brand-primary text-xs font-semibold rounded-lg hover:bg-blue-100 transition-colors inline-flex items-center gap-1.5 border border-blue-200">
                                <i class="fa-solid fa-table"></i> Lihat Contoh Format
                            </button>
                        </div>
                        <button type="submit" :disabled="!hasFile || isUploading" :class="(!hasFile || isUploading) ? 'bg-gray-400 cursor-not-allowed' : 'bg-brand-primary hover:bg-red-700'" class="px-6 py-2 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5 whitespace-nowrap">
                            <span x-show="!isUploading"><i class="fa-solid fa-upload"></i> Unggah & Proses CSV</span>
                            <span x-show="isUploading" style="display: none;"><i class="fa-solid fa-spinner fa-spin"></i> Memproses CSV...</span>
                        </button>
                    </div>

                    <!-- Preview Table (Hidden by default) -->
                    <div id="sample-csv-preview-murid" class="hidden mt-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 text-xs font-semibold text-gray-600 flex justify-between items-center">
                            <span>Pratinjau Format Ideal (2 Baris Data)</span>
                            <button type="button" onclick="document.getElementById('sample-csv-preview-murid').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times"></i></button>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-4 py-2 font-medium text-gray-500 whitespace-nowrap">Nama Lengkap</th>
                                    <th scope="col" class="px-4 py-2 font-medium text-gray-500 whitespace-nowrap">NISN / NIS / Email</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-2 font-mono text-gray-800 whitespace-nowrap">Budi Santoso</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">0123456789</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 font-mono text-gray-800 whitespace-nowrap">Ani Suryani</td>
                                    <td class="px-4 py-2 font-mono text-gray-600 whitespace-nowrap">ani@example.com</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            @forelse($groupedClasses as $jenjang => $tingkatClasses)
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-brand-primary pb-2 inline-block">Jenjang {{ $jenjang }}</h3>
                    
                    <div class="space-y-6">
                        @foreach($tingkatClasses as $tingkat => $classes)
                            <div class="bg-brand-surface shadow-sm rounded-xl border border-brand-border overflow-hidden">
                                <div class="bg-gray-50 px-6 py-3 border-b border-brand-border flex justify-between items-center">
                                    <h4 class="font-bold text-gray-700">Tingkat {{ $tingkat }}</h4>
                                    <span class="text-xs font-medium bg-white px-2 py-1 rounded border border-gray-200">{{ $classes->count() }} Rombel</span>
                                </div>
                                <div class="p-0 overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="text-gray-500 text-xs uppercase tracking-wider">
                                                <th class="px-6 py-3 font-medium border-b border-brand-border">Nama Kelas</th>
                                                <th class="px-6 py-3 font-medium border-b border-brand-border">Wali Kelas</th>
                                                <th class="px-6 py-3 font-medium border-b border-brand-border text-center">Jumlah Siswa</th>
                                                <th class="px-6 py-3 font-medium border-b border-brand-border text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-brand-border">
                                            @foreach($classes as $class)
                                                <tr class="hover:bg-gray-50/50">
                                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $class->nama_kelas }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-700">
                                                        @if($class->waliKelas)
                                                            <div class="flex items-center">
                                                                <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs mr-2">
                                                                    {{ substr($class->waliKelas->name, 0, 1) }}
                                                                </div>
                                                                <span>{{ $class->waliKelas->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400 italic text-xs">Belum ditugaskan</span>
                                                            <button @click="editClassForm.id = {{ $class->id }}; editClassForm.jenjang = '{{ $class->jenjang }}'; editClassForm.tingkat = '{{ $class->tingkat }}'; editClassForm.nama_kelas = '{{ addslashes($class->nama_kelas) }}'; editClassForm.wali_kelas_id = ''; showEditClassModal = true;" class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded hover:bg-amber-200 shadow-sm transition">Set Wali Kelas</button>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $class->students_count }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right text-sm">
                                                        <a href="{{ route('operator.classes.show', $class->id) }}" class="text-emerald-600 hover:text-emerald-900 font-medium mr-3">Lihat Siswa</a>
                                                        <button @click="editClassForm.id = {{ $class->id }}; editClassForm.jenjang = '{{ $class->jenjang }}'; editClassForm.tingkat = '{{ $class->tingkat }}'; editClassForm.nama_kelas = '{{ addslashes($class->nama_kelas) }}'; editClassForm.wali_kelas_id = '{{ $class->wali_kelas_id }}'; showEditClassModal = true;" class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Edit</button>
                                                        <form action="{{ route('operator.classes.destroy', $class->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium">Hapus</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-10 px-6 text-center bg-brand-surface rounded-xl border border-brand-border shadow-sm">
                    <i class="fa-solid fa-school text-5xl mb-4 text-gray-300"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Rombel</h3>
                    <p class="text-sm text-gray-500">Institusi Anda belum memiliki kelas atau rombongan belajar. Silakan tambahkan kelas pertama Anda.</p>
                    <button @click="showCreateModal = true" class="mt-6 px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm inline-block">
                        + Tambah Kelas Baru
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Modal Tambah Kelas -->
        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showCreateModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title">Tambah Kelas / Rombel Baru</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Masukkan rincian rombongan belajar.</p>
                    </div>
                    
                    <form action="{{ route('operator.classes.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                        @csrf
                        @if($errors->any() && old('_method') !== 'PUT')
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-xs font-semibold text-red-800 mb-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> Gagal menyimpan kelas. Periksa input berikut:</p>
                                <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="jenjang" class="block text-sm font-medium text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                                    <select name="jenjang" id="jenjang" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('jenjang') border-red-500 @enderror">
                                        <option value="">-- Pilih Jenjang --</option>
                                        
                                        <optgroup label="Pendidikan Dasar">
                                            <option value="SD" {{ old('jenjang') == 'SD' ? 'selected' : '' }}>SD / MI / Paket A</option>
                                            <option value="SMP" {{ old('jenjang') == 'SMP' ? 'selected' : '' }}>SMP / MTs / Paket B</option>
                                        </optgroup>

                                        <optgroup label="Pendidikan Menengah">
                                            <option value="SMA" {{ old('jenjang') == 'SMA' ? 'selected' : '' }}>SMA / MA / Paket C</option>
                                            <option value="SMK" {{ old('jenjang') == 'SMK' ? 'selected' : '' }}>SMK / MAK</option>
                                        </optgroup>

                                        <optgroup label="Pendidikan Anak Usia Dini">
                                            <option value="TK" {{ old('jenjang') == 'TK' ? 'selected' : '' }}>TK / RA / PAUD</option>
                                        </optgroup>

                                        <optgroup label="Lainnya">
                                            <option value="LAINNYA" {{ old('jenjang') == 'LAINNYA' ? 'selected' : '' }}>Lainnya / Umum</option>
                                        </optgroup>
                                    </select>
                                    @error('jenjang')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat <span class="text-red-500">*</span></label>
                                    <input type="number" name="tingkat" id="tingkat" value="{{ old('tingkat') }}" min="1" max="13" required placeholder="Cth: 1, 7, 10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('tingkat') border-red-500 @enderror">
                                    @error('tingkat')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="nama_kelas" class="block text-sm font-medium text-gray-700">Nama Kelas / Rombel <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_kelas" id="nama_kelas" value="{{ old('nama_kelas') }}" required placeholder="Cth: IX-A, X-IPA-1, XI-TKR-2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('nama_kelas') border-red-500 @enderror">
                                @error('nama_kelas')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="wali_kelas_id" class="block text-sm font-medium text-gray-700">Wali Kelas <span class="text-red-500">*</span></label>
                                <select name="wali_kelas_id" id="wali_kelas_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('wali_kelas_id') border-red-500 @enderror">
                                    <option value="">-- Pilih Wali Kelas --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('wali_kelas_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                                @error('wali_kelas_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6">
                            <button type="button" @click="showCreateModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Simpan Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Kelas -->
        <div x-show="showEditClassModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditClassModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showEditClassModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showEditClassModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title-edit">Edit Kelas / Rombel</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Perbarui rincian rombongan belajar.</p>
                    </div>
                    
                    <form :action="`{{ url('operator/classes') }}/${editClassForm.id}`" method="POST" class="mt-5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" :value="editClassForm.id">

                        @if($errors->any() && old('_method') === 'PUT')
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-xs font-semibold text-red-800 mb-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> Gagal memperbarui kelas. Periksa input berikut:</p>
                                <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_jenjang" class="block text-sm font-medium text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                                    <select name="jenjang" id="edit_jenjang" x-model="editClassForm.jenjang" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('jenjang') border-red-500 @enderror">
                                        <option value="">-- Pilih Jenjang --</option>
                                        
                                        <optgroup label="Pendidikan Dasar">
                                            <option value="SD">SD / MI / Paket A</option>
                                            <option value="SMP">SMP / MTs / Paket B</option>
                                        </optgroup>

                                        <optgroup label="Pendidikan Menengah">
                                            <option value="SMA">SMA / MA / Paket C</option>
                                            <option value="SMK">SMK / MAK</option>
                                        </optgroup>

                                        <optgroup label="Pendidikan Anak Usia Dini">
                                            <option value="TK">TK / RA / PAUD</option>
                                        </optgroup>

                                        <optgroup label="Lainnya">
                                            <option value="LAINNYA">Lainnya / Umum</option>
                                        </optgroup>
                                    </select>
                                    @error('jenjang')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="edit_tingkat" class="block text-sm font-medium text-gray-700">Tingkat <span class="text-red-500">*</span></label>
                                    <input type="number" name="tingkat" id="edit_tingkat" x-model="editClassForm.tingkat" min="1" max="13" required placeholder="Cth: 1, 7, 10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('tingkat') border-red-500 @enderror">
                                    @error('tingkat')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="edit_nama_kelas" class="block text-sm font-medium text-gray-700">Nama Kelas / Rombel <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_kelas" id="edit_nama_kelas" x-model="editClassForm.nama_kelas" required placeholder="Cth: IX-A, X-IPA-1, XI-TKR-2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('nama_kelas') border-red-500 @enderror">
                                @error('nama_kelas')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="edit_wali_kelas_id" class="block text-sm font-medium text-gray-700">Wali Kelas <span class="text-red-500">*</span></label>
                                <select name="wali_kelas_id" id="edit_wali_kelas_id" x-model="editClassForm.wali_kelas_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm @error('wali_kelas_id') border-red-500 @enderror">
                                    <option value="">-- Pilih Wali Kelas --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                                @error('wali_kelas_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6">
                            <button type="button" @click="showEditClassModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
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
