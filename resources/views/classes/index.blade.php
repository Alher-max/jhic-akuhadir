<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Kelas / Rombel') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ showModal: false, showTeacherModal: false, showEditModal: false, editForm: { id: '', jenjang: '', tingkat: '', nama_kelas: '', wali_kelas_id: '' } }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
                <div class="mb-4 sm:mb-0">
                    <p class="text-sm text-gray-600">Tambah, edit, dan atur rombongan belajar (rombel) di institusi Anda.</p>
                </div>
                <div>
                    <button @click="showModal = true" class="px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        + Tambah Kelas Baru
                    </button>
                </div>
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
                                                            <button @click="editForm.id = {{ $class->id }}; editForm.jenjang = '{{ $class->jenjang }}'; editForm.tingkat = '{{ $class->tingkat }}'; editForm.nama_kelas = '{{ addslashes($class->nama_kelas) }}'; editForm.wali_kelas_id = ''; showEditModal = true;" class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded hover:bg-amber-200 shadow-sm transition">Set Wali Kelas</button>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $class->students->count() }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right text-sm">
                                                        <button @click="editForm.id = {{ $class->id }}; editForm.jenjang = '{{ $class->jenjang }}'; editForm.tingkat = '{{ $class->tingkat }}'; editForm.nama_kelas = '{{ addslashes($class->nama_kelas) }}'; editForm.wali_kelas_id = '{{ $class->wali_kelas_id }}'; showEditModal = true;" class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Edit</button>
                                                        <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
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
                    <button @click="showModal = true" class="mt-6 px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm inline-block">
                        + Tambah Kelas Baru
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Modal Tambah Kelas -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title">Tambah Kelas / Rombel Baru</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Masukkan rincian rombongan belajar.</p>
                    </div>
                    
                    <form action="{{ route('classes.store') }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="jenjang" class="block text-sm font-medium text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                                    <select name="jenjang" id="jenjang" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="SD">SD (Sekolah Dasar)</option>
                                        <option value="SMP">SMP (Menengah Pertama)</option>
                                        <option value="SMA">SMA (Menengah Atas)</option>
                                        <option value="SMK">SMK (Kejuruan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat <span class="text-red-500">*</span></label>
                                    <input type="number" name="tingkat" id="tingkat" min="1" max="13" required placeholder="Cth: 1, 7, 10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label for="nama_kelas" class="block text-sm font-medium text-gray-700">Nama Kelas / Rombel <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_kelas" id="nama_kelas" required placeholder="Cth: IX-A, X-IPA-1, XI-TKR-2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="wali_kelas_id" class="block text-sm font-medium text-gray-700">Wali Kelas (Opsional)</label>
                                <select name="wali_kelas_id" id="wali_kelas_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="">-- Belum Ditugaskan --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2 flex justify-between items-center">
                                    <p class="text-xs text-gray-500">Anda dapat menugaskan wali kelas nanti.</p>
                                    <button type="button" @click="showTeacherModal = true" class="text-xs text-brand-primary font-semibold hover:text-red-700 flex items-center gap-1">
                                        <i class="fa-solid fa-plus"></i> Tambah Guru Baru
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6">
                            <button type="button" @click="showModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
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
        <!-- Modal Tambah Guru Cepat -->
        <div x-show="showTeacherModal" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title-teacher" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showTeacherModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showTeacherModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showTeacherModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border relative z-[61]">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title-teacher">Tambah Guru Cepat</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Tambahkan guru baru tanpa meninggalkan halaman ini.</p>
                    </div>
                    
                    <form action="{{ route('teachers.quick-store') }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="teacher_name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="teacher_name" required placeholder="Cth: Dr. Ani Wijaya" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm z-50">
                            </div>
                            
                            <div>
                                <label for="teacher_email" class="block text-sm font-medium text-gray-700">Surel / No. HP (Opsional)</label>
                                <input type="text" name="email" id="teacher_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm z-50" placeholder="Otomatis jika kosong">
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6 relative z-50">
                            <button type="button" @click="showTeacherModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Simpan & Pilih
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Kelas -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showEditModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title-edit">Edit Kelas / Rombel</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Perbarui rincian rombongan belajar.</p>
                    </div>
                    
                    <form :action="`{{ url('dashboard/classes') }}/${editForm.id}`" method="POST" class="mt-5">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_jenjang" class="block text-sm font-medium text-gray-700">Jenjang <span class="text-red-500">*</span></label>
                                    <select name="jenjang" id="edit_jenjang" x-model="editForm.jenjang" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="SD">SD (Sekolah Dasar)</option>
                                        <option value="SMP">SMP (Menengah Pertama)</option>
                                        <option value="SMA">SMA (Menengah Atas)</option>
                                        <option value="SMK">SMK (Kejuruan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="edit_tingkat" class="block text-sm font-medium text-gray-700">Tingkat <span class="text-red-500">*</span></label>
                                    <input type="number" name="tingkat" id="edit_tingkat" x-model="editForm.tingkat" min="1" max="13" required placeholder="Cth: 1, 7, 10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                </div>
                            </div>
                            
                            <div>
                                <label for="edit_nama_kelas" class="block text-sm font-medium text-gray-700">Nama Kelas / Rombel <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_kelas" id="edit_nama_kelas" x-model="editForm.nama_kelas" required placeholder="Cth: IX-A, X-IPA-1, XI-TKR-2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="edit_wali_kelas_id" class="block text-sm font-medium text-gray-700">Wali Kelas</label>
                                <select name="wali_kelas_id" id="edit_wali_kelas_id" x-model="editForm.wali_kelas_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="">-- Belum Ditugaskan --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
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
