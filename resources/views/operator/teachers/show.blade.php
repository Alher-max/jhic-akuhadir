<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('operator.teachers.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors" title="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profil Pendidik & Staf') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ showEditModal: false }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 md:p-8">
                
                <!-- Profil Header -->
                <div class="flex flex-col md:flex-row gap-6 items-start md:items-center border-b border-gray-100 pb-8 mb-8">
                    <!-- Avatar -->
                    <div class="h-24 w-24 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-3xl shadow-inner border border-indigo-200">
                        @if($teacher->avatar)
                            <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="{{ $teacher->name }}" class="h-full w-full rounded-full object-cover">
                        @else
                            {{ substr(trim($teacher->name, '"'), 0, 1) }}
                        @endif
                    </div>
                    
                    <div class="flex-grow">
                        <h1 class="text-2xl font-bold text-gray-900">{{ trim($teacher->name, '"') }}</h1>
                        <p class="text-gray-500 font-mono mt-1"><i class="fa-regular fa-id-card w-5"></i> {{ $teacher->nisn ?? 'Belum ada NUPTK/NIP' }}</p>
                        <p class="text-gray-500 mt-1"><i class="fa-regular fa-envelope w-5"></i> {{ $teacher->email }}</p>
                    </div>
                    
                    <div class="flex flex-col gap-2 min-w-[140px]">
                        @php
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
                            $labelText = $roleLabels[$teacher->role] ?? ucfirst(str_replace('_', ' ', $teacher->role));
                        @endphp
                        <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 shadow-sm">
                            {{ $labelText }}
                        </span>

                        @if($teacher->is_active)
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">Aktif</span>
                        @else
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200 shadow-sm">Nonaktif</span>
                        @endif
                        
                        <button type="button" @click="showEditModal = true" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors cursor-pointer gap-1 mt-1">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Data Guru
                        </button>
                    </div>
                </div>

                <!-- Status Keamanan Akun -->
                <div class="mb-8">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-l-4 border-brand-primary pl-3">Status Keamanan Akun</h3>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            @if($teacher->must_change_password)
                                <div class="flex items-center text-yellow-600 font-semibold mb-1">
                                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Kredensial Default
                                </div>
                                <p class="text-sm text-gray-500">Pengguna belum pernah login atau belum mengganti kata sandi bawaannya.</p>
                            @else
                                <div class="flex items-center text-emerald-600 font-semibold mb-1">
                                    <i class="fa-solid fa-shield-check mr-2"></i> Kata Sandi Diperbarui
                                </div>
                                <p class="text-sm text-gray-500">Pengguna telah menggunakan kata sandi pribadi yang aman.</p>
                            @endif
                        </div>
                        <form action="{{ route('operator.teachers.reset-password', $teacher->id) }}" method="POST" onsubmit="return confirm('Reset kata sandi ke bawaan sistem?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-amber-600 shadow-sm transition-colors">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Reset Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Kelas yang Diampu -->
                <div>
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-l-4 border-brand-primary pl-3">Tugas Wali Kelas</h3>
                    
                    @if($teacher->homeroomClasses && $teacher->homeroomClasses->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($teacher->homeroomClasses as $class)
                                <a href="{{ route('operator.classes.show', $class->id) }}" class="block p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all group">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $class->nama_kelas }}</h4>
                                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $class->jenjang }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Tingkat {{ $class->tingkat }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500">
                            <i class="fa-solid fa-chalkboard-user text-3xl mb-2 text-gray-400"></i>
                            <p class="text-sm">Tidak ada kelas yang diampu sebagai Wali Kelas saat ini.</p>
                        </div>
                    @endif
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
                    
                    <form action="{{ route('operator.teachers.update', $teacher->id) }}" method="POST" enctype="multipart/form-data" class="mt-5" x-data="{ editPhotoPreview: null }">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="edit_name" class="block text-sm font-medium text-gray-700">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="edit_name" value="{{ old('name', $teacher->name) }}" required placeholder="Cth: Drs. Budi Santoso, M.Pd." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_nip" class="block text-sm font-medium text-gray-700">NIP / NIK / NUPTK</label>
                                    <input type="text" name="nip" id="edit_nip" value="{{ old('nip', $teacher->nisn) }}" placeholder="Masukkan NIP/NIK/NUPTK" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                </div>
                                
                                <div>
                                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Surel / Email</label>
                                    <input type="email" name="email" id="edit_email" value="{{ old('email', $teacher->email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="email@sekolah.sch.id">
                                </div>
                            </div>

                            <div>
                                <label for="edit_role" class="block text-sm font-medium text-gray-700">Peran / Jabatan <span class="text-red-500">*</span></label>
                                <select name="role" id="edit_role" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <optgroup label="-- Pendidik (Guru) --">
                                        <option value="guru_kelas" {{ old('role', $teacher->role) === 'guru_kelas' ? 'selected' : '' }}>Guru Kelas</option>
                                        <option value="guru" {{ old('role', $teacher->role) === 'guru' ? 'selected' : '' }}>Guru Mata Pelajaran</option>
                                        <option value="guru_bk" {{ old('role', $teacher->role) === 'guru_bk' ? 'selected' : '' }}>Guru Bimbingan dan Konseling (BK)</option>
                                        <option value="guru_inklusi" {{ old('role', $teacher->role) === 'guru_inklusi' ? 'selected' : '' }}>Guru Pembimbing Khusus (Inklusi)</option>
                                        <option value="guru_kejuruan" {{ old('role', $teacher->role) === 'guru_kejuruan' ? 'selected' : '' }}>Guru Produktif (Kejuruan)</option>
                                        <option value="wali_kelas" {{ old('role', $teacher->role) === 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                                    </optgroup>
                                    <optgroup label="-- Tenaga Kependidikan --">
                                        <option value="headmaster" {{ old('role', $teacher->role) === 'headmaster' ? 'selected' : '' }}>Kepala Sekolah</option>
                                        <option value="manager_teacher" {{ old('role', $teacher->role) === 'manager_teacher' ? 'selected' : '' }}>Wakil Kepala Sekolah / Manajemen</option>
                                        <option value="staff" {{ old('role', $teacher->role) === 'staff' ? 'selected' : '' }}>Tenaga Administrasi Sekolah (Tata Usaha)</option>
                                        <option value="pustakawan" {{ old('role', $teacher->role) === 'pustakawan' ? 'selected' : '' }}>Tenaga Perpustakaan (Pustakawan)</option>
                                        <option value="laboran" {{ old('role', $teacher->role) === 'laboran' ? 'selected' : '' }}>Tenaga Laboratorium (Laboran)</option>
                                        <option value="it_support" {{ old('role', $teacher->role) === 'it_support' ? 'selected' : '' }}>Teknisi Sumber Belajar / IT Support</option>
                                        <option value="satpam" {{ old('role', $teacher->role) === 'satpam' ? 'selected' : '' }}>Petugas Keamanan (Satpam)</option>
                                        <option value="caraka" {{ old('role', $teacher->role) === 'caraka' ? 'selected' : '' }}>Tenaga Kebersihan (Caraka)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Penugasan Wali Kelas -->
                            <div>
                                <label for="edit_class_id" class="block text-sm font-medium text-gray-700">Penugasan Wali Kelas (Opsional)</label>
                                @php $currentHomeroomId = $teacher->homeroomClasses->first()?->id; @endphp
                                <select name="class_id" id="edit_class_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="">-- Belum Ditugaskan ke Kelas --</option>
                                    @foreach($classes ?? [] as $cls)
                                        <option value="{{ $cls->id }}" {{ old('class_id', $currentHomeroomId) == $cls->id ? 'selected' : '' }}>
                                            {{ $cls->nama_kelas }} ({{ $cls->jenjang }} {{ $cls->tingkat }})
                                            @if($cls->wali_kelas_id && $cls->wali_kelas_id != 0 && $cls->wali_kelas_id != $teacher->id)
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
                                <select name="is_active" id="edit_is_active" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="1" {{ old('is_active', $teacher->is_active) ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active', $teacher->is_active) ? '' : 'selected' }}>Nonaktif</option>
                                </select>
                            </div>

                            <div>
                                <label for="edit_teacher_avatar" class="block text-sm font-medium text-gray-700">Foto Profil / Avatar Baru (Opsional)</label>
                                <div class="mt-1 flex items-center">
                                    <template x-if="!editPhotoPreview">
                                        <span class="inline-block h-12 w-12 flex-shrink-0 rounded-full overflow-hidden bg-gray-100">
                                            @if($teacher->avatar)
                                                <img src="{{ asset('storage/' . $teacher->avatar) }}" alt="Avatar Saat Ini" class="w-12 h-12 rounded-full object-cover">
                                            @else
                                                <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            @endif
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
