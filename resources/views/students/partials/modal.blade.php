<!-- Partial Modal Tambah, Edit, & Import Siswa Terpusat -->

<!-- 1. MODAL TAMBAH SISWA BARU (TABBED INTERFACE) -->
<div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" aria-labelledby="modal-title-create" role="dialog" aria-modal="true">
    <!-- Backdrop Overlay -->
    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showModal = false"></div>

    <!-- Modal Box -->
    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 w-full max-w-2xl bg-brand-surface rounded-2xl shadow-xl my-8 border border-brand-border flex flex-col max-h-[90vh] overflow-hidden text-left">
        <div class="p-6 pb-0 sm:p-8 sm:pb-0">
            <div>
                <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title-create">Tambah Siswa Baru</h3>
                <p class="mt-1 text-sm text-brand-text-muted">Masukkan rincian informasi siswa sesuai dengan tab kategori di bawah ini.</p>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="flex border-b border-gray-200 mt-4 gap-1 overflow-x-auto">
                <button type="button" @click="activeTab = 'utama'" :class="activeTab === 'utama' ? 'border-brand-primary text-brand-primary font-bold bg-brand-primary/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 rounded-t-lg transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-id-card"></i> 📌 Data Utama
                </button>
                <button type="button" @click="activeTab = 'ortu'" :class="activeTab === 'ortu' ? 'border-brand-primary text-brand-primary font-bold bg-brand-primary/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 rounded-t-lg transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-user-group"></i> 👨‍👩‍👧 Orang Tua / Wali
                </button>
                <button type="button" @click="activeTab = 'detail'" :class="activeTab === 'detail' ? 'border-brand-primary text-brand-primary font-bold bg-brand-primary/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 rounded-t-lg transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-house-medical"></i> 🏠 Alamat & Kesehatan
                </button>
            </div>
        </div>
        
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
            @csrf
            <div class="space-y-4 overflow-y-auto flex-1 p-6 sm:p-8">
                
                <!-- TAB 1: DATA UTAMA & AKADEMIK -->
                <div x-show="activeTab === 'utama'" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: Budi Santoso">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nisn" class="block text-sm font-medium text-gray-700">NISN <span class="text-red-500">*</span></label>
                            <input type="text" name="nisn" id="nisn" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 0081234567">
                        </div>
                        <div>
                            <label for="nis" class="block text-sm font-medium text-gray-700">NIS (Lokal Sekolah)</label>
                            <input type="text" name="nis" id="nis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 202410012">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nik" class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                            <input type="text" name="nik" id="nik" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 3402121508080001">
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="birth_place" class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                            <input type="text" name="birth_place" id="birth_place" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: Sleman">
                        </div>
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="birth_date" id="birth_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="class_id" class="block text-sm font-medium text-gray-700">Kelas / Rombel <span class="text-red-500">*</span></label>
                            <select name="class_id" id="class_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->full_name }} ({{ $kelas->jenjang }} - Tingkat {{ $kelas->tingkat }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Surel Siswa (Opsional)</label>
                            <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: siswa@sekolah.sch.id">
                        </div>
                    </div>

                    <div>
                        <label for="master_photo" class="block text-sm font-medium text-gray-700">Foto Master Wajah (Opsional)</label>
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
                            <input type="file" name="master_photo" id="master_photo" accept="image/*" @change="compressFileInput($event, (url) => { createPhotoPreview = url })" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary w-full">
                        </div>
                        <template x-if="createPhotoPreview">
                            <div class="mt-2 flex items-center text-xs text-emerald-600 font-medium">
                                <i class="fa-solid fa-circle-check mr-1"></i> Pratinjau Foto Siap (Telah dikompresi secara otomatis)
                            </div>
                        </template>
                        <p class="mt-1.5 text-xs text-gray-500">Gunakan foto pas dengan wajah terlihat jelas. (Otomatis dikompresi di sisi klien &lt; 200KB).</p>
                    </div>
                </div>

                <!-- TAB 2: ORANG TUA / WALI -->
                <div x-show="activeTab === 'ortu'" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="father_name" class="block text-sm font-medium text-gray-700">Nama Ayah Kandung</label>
                            <input type="text" name="father_name" id="father_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Nama Ayah">
                        </div>
                        <div>
                            <label for="mother_name" class="block text-sm font-medium text-gray-700">Nama Ibu Kandung</label>
                            <input type="text" name="mother_name" id="mother_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Nama Ibu">
                        </div>
                    </div>

                    <div>
                        <label for="parent_phone" class="block text-sm font-medium text-gray-700">No. HP / WhatsApp Orang Tua</label>
                        <input type="text" name="parent_phone" id="parent_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400 flex items-start gap-1.5">
                            <i class="fa-solid fa-circle-info mt-0.5 shrink-0 text-gray-400"></i>
                            Data orang tua di atas disimpan sebagai catatan arsip. Untuk menghubungkan akun orang tua secara digital, gunakan menu <strong class="text-gray-600">Orang Tua</strong> di dasbor operator setelah siswa berhasil ditambahkan.
                        </p>
                    </div>
                </div>

                <!-- TAB 3: ALAMAT & KESEHATAN -->
                <div x-show="activeTab === 'detail'" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="religion" class="block text-sm font-medium text-gray-700">Agama</label>
                            <select name="religion" id="religion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Agama --</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Khonghucu">Khonghucu</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label for="blood_type" class="block text-sm font-medium text-gray-700">Golongan Darah</label>
                            <select name="blood_type" id="blood_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Golongan Darah --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="O">O</option>
                                <option value="-">-</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat Lengkap Rumah</label>
                        <textarea name="address" id="address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"></textarea>
                    </div>

                    <div>
                        <label for="medical_notes" class="block text-sm font-medium text-gray-700">Catatan Medis / Riwayat Kesehatan (Opsional)</label>
                        <textarea name="medical_notes" id="medical_notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Alergi, penyakit bawaan, atau riwayat kesehatan khusus"></textarea>
                    </div>
                </div>

            </div>

            <div class="p-6 pt-4 pb-6 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl shrink-0 flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3">
                <button type="button" @click="showModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                    Batal
                </button>
                <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. MODAL EDIT SISWA (TABBED INTERFACE) -->
<div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true">
    <!-- Backdrop Overlay -->
    <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showEditModal = false"></div>

    <!-- Modal Box -->
    <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 w-full max-w-2xl bg-brand-surface rounded-2xl shadow-xl my-8 border border-brand-border flex flex-col max-h-[90vh] overflow-hidden text-left">
        <div class="p-6 pb-0 sm:p-8 sm:pb-0">
            <div>
                <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title-edit">Lihat / Edit Data Siswa</h3>
                <p class="mt-1 text-sm text-brand-text-muted">Perbarui atau lihat rincian informasi siswa sesuai dengan tab di bawah ini.</p>
            </div>

            <!-- TAB NAVIGATION EDIT -->
            <div class="flex border-b border-gray-200 mt-4 gap-1 overflow-x-auto">
                <button type="button" @click="activeEditTab = 'utama'" :class="activeEditTab === 'utama' ? 'border-brand-primary text-brand-primary font-bold bg-brand-primary/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 rounded-t-lg transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-id-card"></i> 📌 Data Utama
                </button>
                <button type="button" @click="activeEditTab = 'ortu'" :class="activeEditTab === 'ortu' ? 'border-brand-primary text-brand-primary font-bold bg-brand-primary/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 rounded-t-lg transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-user-group"></i> 👨‍👩‍👧 Orang Tua / Wali
                </button>
                <button type="button" @click="activeEditTab = 'detail'" :class="activeEditTab === 'detail' ? 'border-brand-primary text-brand-primary font-bold bg-brand-primary/5' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2.5 text-xs sm:text-sm font-medium border-b-2 rounded-t-lg transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-house-medical"></i> 🏠 Alamat & Kesehatan
                </button>
            </div>
        </div>
        
        <form :action="'{{ url('dashboard/students') }}/' + editForm.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
            @csrf
            @method('PUT')
            <div class="space-y-4 overflow-y-auto flex-1 p-6 sm:p-8">
                
                <!-- TAB 1: DATA UTAMA & AKADEMIK -->
                <div x-show="activeEditTab === 'utama'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: Budi Santoso">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NISN <span class="text-red-500">*</span></label>
                            <input type="text" name="nisn" x-model="editForm.nisn" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 0081234567">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIS (Lokal Sekolah)</label>
                            <input type="text" name="nis" x-model="editForm.nis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 202410012">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                            <input type="text" name="nik" x-model="editForm.nik" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: 3402121508080001">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <select name="gender" x-model="editForm.gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                            <input type="text" name="birth_place" x-model="editForm.birth_place" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: Sleman">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="birth_date" x-model="editForm.birth_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas / Rombel <span class="text-red-500">*</span></label>
                            <select name="class_id" x-model="editForm.class_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->full_name }} ({{ $class->jenjang }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Surel / Username</label>
                            <input type="email" name="email" x-model="editForm.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Contoh: siswa@sekolah.sch.id">
                        </div>
                    </div>

                    <div>
                        <label for="edit_is_active" class="block text-sm font-medium text-gray-700">Status Keaktifan Akun <span class="text-red-500">*</span></label>
                        <select name="is_active" id="edit_is_active" x-model="editForm.is_active" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Foto Master Wajah (Perbarui)</label>
                        <div class="mt-1 flex items-center">
                            <!-- Show new preview if selected -->
                            <img :src="previewUrl" x-show="previewUrl" class="w-12 h-12 flex-shrink-0 rounded-full object-cover border-2 border-emerald-500 shadow-sm" alt="Pratinjau Foto Baru">
                            <!-- Show existing photo if no previewUrl but student has master_photo -->
                            <img :src="selectedStudent?.master_photo ? '/storage/' + selectedStudent.master_photo : ''" x-show="selectedStudent?.master_photo && !previewUrl" class="w-12 h-12 flex-shrink-0 rounded-full object-cover border-2 border-slate-300 shadow-sm" alt="Foto Saat Ini">
                            <!-- Default SVG avatar if no photo at all -->
                            <span x-show="!previewUrl && !selectedStudent?.master_photo" class="inline-block h-12 w-12 flex-shrink-0 rounded-full overflow-hidden bg-gray-100">
                                <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                            <input type="file" name="master_photo" accept="image/*" @change="compressFileInput($event, (url) => { previewUrl = url })" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none w-full">
                        </div>
                        <div x-show="previewUrl" class="mt-2 flex items-center text-xs text-emerald-600 font-medium">
                            <i class="fa-solid fa-circle-check mr-1"></i> Pratinjau Foto Baru Siap (Telah dikompresi secara otomatis)
                        </div>
                        <p x-show="!previewUrl && selectedStudent?.master_photo" class="mt-1 text-xs text-slate-500"><i class="fa-solid fa-circle-info mr-1"></i>Menampilkan foto yang sudah ada. Pilih file baru untuk menggantinya dengan foto yang terkompresi otomatis.</p>
                        <p x-show="!previewUrl && !selectedStudent?.master_photo" class="mt-1 text-xs text-gray-500">Biarkan kosong jika tidak ingin mengunggah foto. Jika mengunggah, gambar akan otomatis dikompresi.</p>
                    </div>
                </div>

                <!-- TAB 2: ORANG TUA / WALI -->
                <div x-show="activeEditTab === 'ortu'" class="space-y-4">
                    <!-- Box Informasi Kontak Ortu Terhubung -->
                    <div x-show="selectedStudent?.parent" class="p-3.5 bg-blue-50/80 border border-blue-200/80 rounded-xl text-xs space-y-2">
                        <div class="font-semibold text-blue-900 flex items-center gap-1.5 border-b border-blue-200/60 pb-1.5">
                            <i class="fa-solid fa-user-group text-blue-600"></i> Ringkasan Akun Ortu Terhubung:
                        </div>
                        <div class="grid grid-cols-1 gap-1.5 text-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-500 w-32 flex-shrink-0">Nama Ortu:</span>
                                <span class="font-semibold text-gray-900" x-text="selectedStudent?.parent?.name"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-500 w-32 flex-shrink-0">Email Ortu:</span>
                                <span class="text-gray-800 font-mono" x-text="selectedStudent?.parent?.email || '-'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-500 w-32 flex-shrink-0">No. HP / WA Ortu:</span>
                                <span class="text-gray-800 font-mono" x-text="selectedStudent?.parent?.phone || selectedStudent?.parent?.phone_number || selectedStudent?.parent?.no_hp || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Ayah Kandung / Wali</label>
                            <input type="text" name="father_name" x-model="editForm.father_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Ibu Kandung</label>
                            <input type="text" name="mother_name" x-model="editForm.mother_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. Telepon / WhatsApp Orang Tua (Untuk Notifikasi)</label>
                        <input type="text" name="parent_phone" x-model="editForm.parent_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                    </div>

                    <div class="pt-2 border-t border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Opsi Relasi Orang Tua / Wali <span class="text-red-500">*</span></label>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full" 
                                  :class="editForm.current_parent_name ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800'"
                                  x-text="'Saat ini: ' + (editForm.current_parent_name ? editForm.current_parent_name : 'Belum terhubung')">
                            </span>
                        </div>
                        
                        <div class="space-y-2 mb-3">
                            <label class="inline-flex items-center w-full">
                                <input type="radio" x-model="editForm.parent_option" name="parent_option" value="unchanged" class="text-brand-primary focus:ring-brand-primary h-4 w-4 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Tidak ada perubahan</span>
                            </label>
                            <label class="inline-flex items-center w-full">
                                <input type="radio" x-model="editForm.parent_option" name="parent_option" value="none" class="text-brand-primary focus:ring-brand-primary h-4 w-4 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Lepas / Tanpa Akun Orang Tua</span>
                            </label>
                            <label class="inline-flex items-center w-full">
                                <input type="radio" x-model="editForm.parent_option" name="parent_option" value="new" class="text-brand-primary focus:ring-brand-primary h-4 w-4 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Buat Akun Orang Tua Baru</span>
                            </label>
                            <label class="inline-flex items-center w-full">
                                <input type="radio" x-model="editForm.parent_option" name="parent_option" value="existing" class="text-brand-primary focus:ring-brand-primary h-4 w-4 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Pilih dari Terdaftar</span>
                            </label>
                        </div>

                        <div x-show="editForm.parent_option === 'new'" x-transition class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Akun Ortu / Wali <span class="text-red-500">*</span></label>
                                <input type="text" name="parent_name" x-model="editForm.parent_name" :required="editForm.parent_option === 'new'" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Surel Ortu (Opsional)</label>
                                <input type="text" name="parent_email" x-model="editForm.parent_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                        </div>
                        
                        <div x-show="editForm.parent_option === 'existing'" x-transition class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Orang Tua / Wali <span class="text-red-500">*</span></label>
                            <select name="parent_id" x-model="editForm.parent_id" :required="editForm.parent_option === 'existing'" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Orang Tua Terdaftar --</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }} {{ $parent->email ? '('.$parent->email.')' : '' }}</option>
                                @endforeach
                            </select>
                            @if($parents->isEmpty())
                                <span class="text-xs text-amber-600 mt-1 block">Belum ada akun orang tua terdaftar. Silakan buat akun baru.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- TAB 3: ALAMAT & KESEHATAN -->
                <div x-show="activeEditTab === 'detail'" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agama</label>
                            <select name="religion" x-model="editForm.religion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Agama --</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Khonghucu">Khonghucu</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Golongan Darah</label>
                            <select name="blood_type" x-model="editForm.blood_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                <option value="">-- Pilih Golongan Darah --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="O">O</option>
                                <option value="-">-</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat Lengkap Rumah</label>
                        <textarea name="address" x-model="editForm.address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan Medis / Riwayat Kesehatan (Opsional)</label>
                        <textarea name="medical_notes" x-model="editForm.medical_notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm"></textarea>
                    </div>
                </div>

            </div>

            <div class="p-6 pt-4 pb-6 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl shrink-0 flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3">
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

<!-- 3. MODAL IMPORT SISWA (CSV/EXCEL) -->
<div x-show="showImportModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" aria-labelledby="modal-title-import" role="dialog" aria-modal="true">
    <!-- Backdrop Overlay -->
    <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showImportModal = false"></div>

    <!-- Modal Box -->
    <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 w-full max-w-xl bg-brand-surface rounded-2xl shadow-xl my-8 border border-brand-border flex flex-col max-h-[90vh] overflow-hidden text-left">
        <div class="p-6 pb-0 sm:p-8 sm:pb-0">
            <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title-import">Import Data Siswa (CSV/Excel)</h3>
            <p class="mt-2 text-sm text-brand-text-muted">Unggah berkas CSV/TXT untuk memasukkan atau memperbarui data siswa secara massal ke dalam kelas.</p>
        </div>
        
        <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
            @csrf
            <div class="space-y-4 overflow-y-auto flex-1 p-6 sm:p-8">
                <div>
                    <label for="import_class_id" class="block text-sm font-medium text-gray-700">Target Kelas / Rombel <span class="text-red-500">*</span></label>
                    <select name="class_id" id="import_class_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                        <option value="">-- Pilih Kelas Target --</option>
                        @foreach($classes as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->full_name }} ({{ $kelas->jenjang }} - Tingkat {{ $kelas->tingkat }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="students_file" class="block text-sm font-medium text-gray-700">Berkas CSV / TXT Siswa <span class="text-red-500">*</span></label>
                    <input type="file" name="students_file" id="students_file" accept=".csv,.txt" required class="mt-1 block w-full text-sm text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-primary focus:border-brand-primary">
                    <p class="mt-1.5 text-xs text-gray-500">
                        <span class="font-medium text-gray-700">Kolom Wajib:</span> <strong>nama_lengkap, nisn / email</strong>.<br>
                        <span class="font-medium text-gray-700">Kolom Opsional:</span> nis, nik, gender (L/P), birth_place, birth_date (YYYY-MM-DD), religion, father_name, mother_name, parent_phone, address, blood_type, medical_notes. Maks 2MB.
                    </p>
                </div>

                <div class="p-3.5 bg-blue-50/70 border border-blue-200/80 rounded-xl text-xs space-y-1.5">
                    <div class="font-semibold text-blue-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-blue-600"></i> Informasi Petunjuk Impor:
                    </div>
                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                        <li><strong>Kolom Wajib:</strong> <code>nama_lengkap</code>, <code>nisn</code> / <code>email</code>.</li>
                        <li><strong>Kolom Opsional:</strong> <code>nis</code>, <code>nik</code>, <code>gender</code> (L/P), <code>parent_phone</code>, <code>birth_place</code>, <code>birth_date</code> (YYYY-MM-DD), <code>religion</code>, <code>father_name</code>, <code>mother_name</code>, <code>address</code>, <code>blood_type</code>, <code>medical_notes</code>.</li>
                        <li>Jika NISN, NIS, atau Email siswa sudah ada di sistem, data profil & kelasnya akan otomatis diperbarui.</li>
                    </ul>
                    <div class="pt-2 border-t border-blue-200/60">
                        <a href="{{ route('students.download-template') }}" class="inline-flex items-center gap-1.5 font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                            <i class="fa-solid fa-download"></i> Unduh Berkas Contoh Lengkap (Template CSV)
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-4 pb-6 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl shrink-0 flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3">
                <button type="button" @click="showImportModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                    Batal
                </button>
                <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                    <i class="fa-solid fa-file-import mr-1.5"></i> Mulai Impor Data
                </button>
            </div>
        </form>
    </div>
</div>
