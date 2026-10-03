<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('vocational.internships.index', $class) }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Daftar Penempatan PKL
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Daftarkan Siswa</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Pendaftaran Penempatan PKL (Praktik Kerja Lapangan)
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Kelas {{ $class->nama_kelas }} &bull; TA {{ $activeYear->formatted_period }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <span class="material-symbols-outlined text-red-600 text-base">error</span>
                        <span>Terdapat kesalahan formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('vocational.internships.store', $class) }}" class="space-y-6">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class->id }}">

                <!-- Section 1: Siswa & Guru Pembimbing -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-brand-text-main border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-primary">person</span>
                        <span>1. Data Siswa & Pembimbing Sekolah</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Siswa Magang <span class="text-red-500">*</span>
                            </label>
                            <select name="student_id" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} (NISN: {{ $student->nisn ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Guru Pembimbing PKL (Sekolah) <span class="text-red-500">*</span>
                            </label>
                            <select name="teacher_supervisor_id" required
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                                <option value="">-- Pilih Guru Pembimbing --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ (old('teacher_supervisor_id', auth()->id()) == $teacher->id) ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Industri Mitra & Geofence -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-brand-text-main border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-primary">corporate_fare</span>
                        <span>2. Industri Mitra (DUDI) & Presensi Geofence</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Nama Perusahaan / Instansi Mitra <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="company_name" required value="{{ old('company_name') }}"
                                placeholder="Contoh: PT Telkom Akses Regional 2"
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tautkan Lokasi Geofence HadirYuk (Opsional)
                            </label>
                            <select name="industry_location_id"
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                                <option value="">-- Tidak Ditautkan (Gunakan Lokasi Umum) --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('industry_location_id') == $loc->id ? 'selected' : '' }}>
                                        📍 {{ $loc->name }} (Radius {{ $loc->radius ?? 100 }}m)
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">
                                Jika ditautkan, presensi selfie siswa selama magang akan dihitung otomatis saat berada di titik geofence kantor mitra.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap Perusahaan</label>
                        <textarea name="company_address" rows="2"
                            placeholder="Alamat kantor / workshop tempat siswa ditempatkan..."
                            class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">{{ old('company_address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Instruktur / Pembimbing Lapangan DUDI</label>
                            <input type="text" name="mentor_name" value="{{ old('mentor_name') }}"
                                placeholder="Contoh: Hendra Kusuma, S.T."
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jabatan Pembimbing di Industri</label>
                            <input type="text" name="mentor_position" value="{{ old('mentor_position') }}"
                                placeholder="Contoh: Head of Network Engineer / Supervisor HRD"
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Periode Pelaksanaan PKL -->
                <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-brand-text-main border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-primary">calendar_month</span>
                        <span>3. Periode Waktu Pelaksanaan PKL</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Mulai PKL <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" required value="{{ old('start_date') }}"
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Selesai PKL <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="end_date" required value="{{ old('end_date') }}"
                                class="w-full text-sm rounded-xl border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('vocational.internships.index', $class) }}"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold shadow-md transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Daftarkan Penempatan</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
