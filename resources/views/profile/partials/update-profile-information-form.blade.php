<section>
    <header>
        <h2 class="text-lg font-bold text-gray-900 border-b border-slate-200 pb-2">
            {{ __('Informasi Profil Kepala Sekolah / Pengguna') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 mb-6">
            {{ __("Lengkapi data identitas, kepegawaian, dan riwayat penugasan Anda.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-8" enctype="multipart/form-data">
        @csrf
        @method('patch')

        @php
            $profile = $user->profile ?? new \App\Models\UserProfile();
        @endphp

        <!-- I. DATA IDENTITAS PRIBADI -->
        <div>
            <h3 class="text-md font-semibold text-red-700 mb-4 border-l-4 border-red-700 pl-2">I. Data Identitas Pribadi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                
                <!-- FOTO PROFIL -->
                @php
                    $isStudent = $user->role === 'student';
                    $hasPhoto = !empty($user->avatar) || !empty($user->master_photo);
                @endphp

                @if($isStudent && $hasPhoto)
                    <!-- UI Foto Siswa Terkunci (Lock 1x) -->
                    <div class="md:col-span-2 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ Storage::url($user->avatar ?: $user->master_photo) }}" alt="{{ $user->name }}" class="rounded-full h-16 w-16 object-cover shadow-sm border-2 border-amber-300">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded font-bold text-xs bg-amber-200 text-amber-900">
                                        <i class="fa-solid fa-lock text-2xs"></i> Foto Profil Terkunci & Terverifikasi
                                    </span>
                                </div>
                                <p class="text-xs text-amber-800 font-medium">
                                    Foto profil telah dikunci & terverifikasi. Hubungi Wali Kelas/Operator jika perlu mengubah foto.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Form Upload Foto (Pertama kali / Non-Student / Setelah Reset) -->
                    <div class="md:col-span-2 space-y-3 pb-4 border-b border-slate-200">
                        @if($isStudent && !$hasPhoto)
                            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 text-xs text-rose-800 font-medium flex items-start gap-2.5">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm mt-0.5 flex-shrink-0"></i>
                                <span>⚠️ Perhatian: Foto profil ini digunakan untuk verifikasi kehadiran. Anda hanya dapat mengunggah foto 1 KALI. Gunakan pasfoto resmi berpakaian rapi.</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-6" x-data="{ photoName: null, photoPreview: null }">
                            <input type="file" id="avatar" name="avatar" class="hidden"
                                   x-ref="avatar"
                                   accept="image/*"
                                   x-on:change="
                                        compressFileInput($event, (url, compressedFile) => {
                                            if (compressedFile) {
                                                photoName = compressedFile.name;
                                                photoPreview = url;
                                            }
                                        });
                                   " />

                            <!-- Foto Saat Ini -->
                            <div x-show="! photoPreview">
                                @if($user->avatar || $user->master_photo)
                                    <img src="{{ Storage::url($user->avatar ?: $user->master_photo) }}" alt="{{ $user->name }}" class="rounded-full h-20 w-20 object-cover shadow-sm border border-slate-200">
                                @else
                                    <div class="rounded-full h-20 w-20 bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-3xl shadow-sm border border-slate-300">
                                        {{ substr(preg_replace('/[^a-zA-Z]/', '', $user->name), 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Preview Foto Baru -->
                            <div x-show="photoPreview" style="display: none;">
                                <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center shadow-sm border border-slate-200"
                                      x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                                </span>
                            </div>

                            <div>
                                <button type="button" x-on:click.prevent="$refs.avatar.click()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                                    Pilih Foto
                                </button>
                                <p class="mt-2 text-xs text-slate-500">Format: JPG, PNG, WEBP. (Otomatis dikompresi di sisi klien &lt; 200KB).</p>
                                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="md:col-span-2">
                    <x-input-label for="name" :value="__('Nama Lengkap (beserta gelar)')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-white" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                
                <div>
                    <x-input-label for="email" :value="__('Alamat Email Aktif')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 cursor-not-allowed text-gray-500" :value="old('email', $user->email)" readonly />
                    <p class="text-xs text-gray-500 mt-1">Alamat email tidak dapat diubah (terkait kredensial login utama).</p>
                </div>

                <div>
                    <x-input-label for="phone_number" :value="__('Nomor HP / WhatsApp')" />
                    <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full bg-white" :value="old('phone_number', $profile->phone_number)" />
                </div>

                <div>
                    <x-input-label for="birth_place" :value="__('Tempat Lahir')" />
                    <x-text-input id="birth_place" name="birth_place" type="text" class="mt-1 block w-full bg-white" :value="old('birth_place', $profile->birth_place)" />
                </div>

                <div>
                    <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                    <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full bg-white" :value="old('birth_date', $profile->birth_date)" />
                </div>

                <div>
                    <x-input-label for="gender" :value="__('Jenis Kelamin')" />
                    <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" {{ old('gender', $profile->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender', $profile->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="religion" :value="__('Agama')" />
                    <select id="religion" name="religion" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Islam" {{ old('religion', $profile->religion) == 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen (Protestan)" {{ old('religion', $profile->religion) == 'Kristen (Protestan)' ? 'selected' : '' }}>Kristen (Protestan)</option>
                        <option value="Katolik" {{ old('religion', $profile->religion) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('religion', $profile->religion) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('religion', $profile->religion) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Khonghucu" {{ old('religion', $profile->religion) == 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="address" :value="__('Alamat Rumah Tinggal')" />
                    <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">{{ old('address', $profile->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- II. DATA KEPEGAWAIAN & STATUS PEGAWAI -->
        <div>
            <h3 class="text-md font-semibold text-red-700 mb-4 border-l-4 border-red-700 pl-2">II. Data Kepegawaian & Status Pegawai</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                
                <div>
                    <x-input-label for="employee_id" :value="__('NIP / NIK / No. Pegawai')" />
                    <x-text-input id="employee_id" name="employee_id" type="text" class="mt-1 block w-full bg-white" :value="old('employee_id', $profile->employee_id)" />
                </div>

                <div>
                    <x-input-label for="nuptk" :value="__('NUPTK')" />
                    <x-text-input id="nuptk" name="nuptk" type="text" class="mt-1 block w-full bg-white" :value="old('nuptk', $profile->nuptk)" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="employment_status" :value="__('Status Kepegawaian')" />
                    <select id="employment_status" name="employment_status" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="PNS (Pegawai Negeri Sipil)" {{ old('employment_status', $profile->employment_status) == 'PNS (Pegawai Negeri Sipil)' ? 'selected' : '' }}>PNS (Pegawai Negeri Sipil)</option>
                        <option value="PPPK (Pegawai Pemerintah dengan Perjanjian Kerja)" {{ old('employment_status', $profile->employment_status) == 'PPPK (Pegawai Pemerintah dengan Perjanjian Kerja)' ? 'selected' : '' }}>PPPK (Pegawai Pemerintah dengan Perjanjian Kerja)</option>
                        <option value="GTY (Guru Tetap Yayasan)" {{ old('employment_status', $profile->employment_status) == 'GTY (Guru Tetap Yayasan)' ? 'selected' : '' }}>GTY (Guru Tetap Yayasan)</option>
                        <option value="Guru Honorer Sekolah" {{ old('employment_status', $profile->employment_status) == 'Guru Honorer Sekolah' ? 'selected' : '' }}>Guru Honorer Sekolah</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="rank_group" :value="__('Pangkat / Golongan Ruang Terakhir (Khusus ASN)')" />
                    <select id="rank_group" name="rank_group" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach(['Penata Muda / III/a', 'Penata Muda Tk. I / III/b', 'Penata / III/c', 'Penata Tk. I / III/d', 'Pembina / IV/a', 'Pembina Tk. I / IV/b', 'Pembina Utama Muda / IV/c', 'Pembina Utama Madya / IV/d', 'Pembina Utama / IV/e', 'Bukan ASN / Tanpa Golongan Ruang'] as $opt)
                            <option value="{{ $opt }}" {{ old('rank_group', $profile->rank_group) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="functional_position" :value="__('Jabatan Fungsional Guru Terakhir')" />
                    <select id="functional_position" name="functional_position" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach(['Guru Ahli Pertama', 'Guru Ahli Muda', 'Guru Ahli Madya', 'Guru Ahli Utama', 'Non-Fungsional (Guru Non-ASN)'] as $opt)
                            <option value="{{ $opt }}" {{ old('functional_position', $profile->functional_position) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- III. DATA PENUGASAN SEBAGAI KEPALA SEKOLAH -->
        <div>
            <h3 class="text-md font-semibold text-red-700 mb-1 border-l-4 border-red-700 pl-2">III. Data Penugasan Sebagai Kepala Sekolah</h3>
            <p class="text-xs text-gray-500 mb-4 ml-3">*) Dapat dikosongkan terlebih dahulu, dapat diisi oleh Operator Sekolah.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                
                <div class="md:col-span-2">
                    <x-input-label for="school_name" :value="__('Nama Sekolah Tempat Tugas')" />
                    <x-text-input id="school_name" name="school_name" type="text" class="mt-1 block w-full bg-white" :value="old('school_name', $profile->school_name)" />
                </div>

                <div>
                    <x-input-label for="school_npsn" :value="__('NPSN Sekolah')" />
                    <x-text-input id="school_npsn" name="school_npsn" type="text" class="mt-1 block w-full bg-white" :value="old('school_npsn', $profile->school_npsn)" />
                </div>

                <div>
                    <x-input-label for="school_ownership" :value="__('Status Kepemilikan Sekolah')" />
                    <select id="school_ownership" name="school_ownership" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Negeri" {{ old('school_ownership', $profile->school_ownership) == 'Negeri' ? 'selected' : '' }}>Negeri</option>
                        <option value="Swasta (Yayasan)" {{ old('school_ownership', $profile->school_ownership) == 'Swasta (Yayasan)' ? 'selected' : '' }}>Swasta (Yayasan)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="school_level" :value="__('Jenjang Sekolah Tempat Tugas')" />
                    <select id="school_level" name="school_level" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach(['TK / PAUD', 'SD / SDLB', 'SMP / SMPLB', 'SMA / SMAN / SMALB', 'SMK / SMKK'] as $opt)
                            <option value="{{ $opt }}" {{ old('school_level', $profile->school_level) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="sk_appointment" :value="__('Nomor & Tanggal SK Pengangkatan')" />
                    <x-text-input id="sk_appointment" name="sk_appointment" type="text" class="mt-1 block w-full bg-white" :value="old('sk_appointment', $profile->sk_appointment)" placeholder="Contoh: 123/SK/2026, 01-Jan-2026" />
                </div>

                <div>
                    <x-input-label for="tmt_position" :value="__('TMT (Terhitung Mulai Tanggal) Jabatan')" />
                    <x-text-input id="tmt_position" name="tmt_position" type="date" class="mt-1 block w-full bg-white" :value="old('tmt_position', $profile->tmt_position)" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="tenure_period" :value="__('Masa Jabatan Periode Ke-')" />
                    <select id="tenure_period" name="tenure_period" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Periode 1 (Tahun Ke-1 s.d Ke-4)" {{ old('tenure_period', $profile->tenure_period) == 'Periode 1 (Tahun Ke-1 s.d Ke-4)' ? 'selected' : '' }}>Periode 1 (Tahun Ke-1 s.d Ke-4)</option>
                        <option value="Periode 2 (Tahun Ke-5 s.d Ke-8 / Perpanjangan)" {{ old('tenure_period', $profile->tenure_period) == 'Periode 2 (Tahun Ke-5 s.d Ke-8 / Perpanjangan)' ? 'selected' : '' }}>Periode 2 (Tahun Ke-5 s.d Ke-8 / Perpanjangan)</option>
                        <option value="Periode Khusus (Aturan Yayasan Swasta)" {{ old('tenure_period', $profile->tenure_period) == 'Periode Khusus (Aturan Yayasan Swasta)' ? 'selected' : '' }}>Periode Khusus (Aturan Yayasan Swasta)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- IV. RIWAYAT PENDIDIKAN TERTINGGI -->
        <div>
            <h3 class="text-md font-semibold text-red-700 mb-4 border-l-4 border-red-700 pl-2">IV. Riwayat Pendidikan Tertinggi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                
                <div>
                    <x-input-label for="highest_education" :value="__('Kualifikasi Pendidikan Formal Tertinggi')" />
                    <select id="highest_education" name="highest_education" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="S-3 (Doktor)" {{ old('highest_education', $profile->highest_education) == 'S-3 (Doktor)' ? 'selected' : '' }}>S-3 (Doktor)</option>
                        <option value="S-2 (Magister)" {{ old('highest_education', $profile->highest_education) == 'S-2 (Magister)' ? 'selected' : '' }}>S-2 (Magister)</option>
                        <option value="S-1 / D-IV (Sarjana)" {{ old('highest_education', $profile->highest_education) == 'S-1 / D-IV (Sarjana)' ? 'selected' : '' }}>S-1 / D-IV (Sarjana)</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="university_major" :value="__('Nama Perguruan Tinggi & Jurusan')" />
                    <x-text-input id="university_major" name="university_major" type="text" class="mt-1 block w-full bg-white" :value="old('university_major', $profile->university_major)" />
                </div>
            </div>
        </div>

        <!-- V. SERTIFIKASI & LEGALITAS KEPEMIMPINAN -->
        <div>
            <h3 class="text-md font-semibold text-red-700 mb-4 border-l-4 border-red-700 pl-2">V. Sertifikasi & Legalitas Kepemimpinan</h3>
            <div class="grid grid-cols-1 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                
                <div>
                    <x-input-label for="has_educator_certificate" :value="__('Kepemilikan Sertifikat Pendidik (Serdik)')" />
                    <select id="has_educator_certificate" name="has_educator_certificate" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Sudah Memiliki" {{ old('has_educator_certificate', $profile->has_educator_certificate) == 'Sudah Memiliki' ? 'selected' : '' }}>Sudah Memiliki</option>
                        <option value="Belum Memiliki" {{ old('has_educator_certificate', $profile->has_educator_certificate) == 'Belum Memiliki' ? 'selected' : '' }}>Belum Memiliki</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="leadership_training_certificate" :value="__('Jenis Sertifikat Diklat Kepemimpinan yang Dimiliki')" />
                    <select id="leadership_training_certificate" name="leadership_training_certificate" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach(['Sertifikat Guru Penggerak', 'Sertifikat Diklat Calon Kepala Sekolah (CKS)', 'Memiliki Keduanya (Guru Penggerak & CKS)', 'Belum Memiliki Keduanya'] as $opt)
                            <option value="{{ $opt }}" {{ old('leadership_training_certificate', $profile->leadership_training_certificate) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- VI. RIWAYAT PENGALAMAN MANAJERIAL SEBELUMNYA -->
        <div>
            <h3 class="text-md font-semibold text-red-700 mb-4 border-l-4 border-red-700 pl-2">VI. Riwayat Pengalaman Manajerial Sebelumnya</h3>
            <div class="grid grid-cols-1 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                
                <div>
                    <x-input-label for="managerial_experience" :value="__('Pernah Memegang Jabatan Manajerial Minimal 2 Tahun?')" />
                    <select id="managerial_experience" name="managerial_experience" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach([
                            'Pernah, sebagai Wakil Kepala Sekolah',
                            'Pernah, sebagai Ketua Jurusan / Program Keahlian (Khusus SMK)',
                            'Pernah, sebagai Kepala Laboratorium / Kepala Perpustakaan',
                            'Pernah, jabatan kepemimpinan lain (Koordinator/Ketua Yayasan/dll.)',
                            'Belum Pernah'
                        ] as $opt)
                            <option value="{{ $opt }}" {{ old('managerial_experience', $profile->managerial_experience) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white font-medium px-6 py-2.5 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                {{ __('SIMPAN PROFIL') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 font-medium"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
