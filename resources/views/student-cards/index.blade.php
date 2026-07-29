<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-id-card text-brand-primary"></i>
            {{ __('Pengaturan Alat & Lainnya') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-brand-bg min-h-screen" x-data="{
        template: '{{ $defaultSettings['template'] }}',
        accentColor: '{{ $defaultSettings['accent_color'] }}',
        schoolName: '{{ addslashes($defaultSettings['school_name']) }}',
        academicYear: '{{ addslashes($defaultSettings['academic_year']) }}',
        cardTitle: '{{ addslashes($defaultSettings['card_title']) }}',
        footerText: '{{ addslashes($defaultSettings['footer_text']) }}',
        logoUrl: '{{ $defaultSettings['logo_url'] ?? '' }}',
        selectedStudents: [],
        selectAll: false,
        previewLogo(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file logo maksimal 2 MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        removeLogoPreview() {
            this.logoUrl = '';
            if (document.getElementById('school_logo_input')) {
                document.getElementById('school_logo_input').value = '';
            }
        },
        toggleAll() {
            if (this.selectAll) {
                this.selectedStudents = Array.from(document.querySelectorAll('.student-checkbox')).map(cb => cb.value);
            } else {
                this.selectedStudents = [];
            }
        }
    }">
        <!-- Hidden Standalone Forms for Logo Upload & Removal -->
        <form action="{{ route('student-cards.upload-logo') }}" method="POST" enctype="multipart/form-data" id="uploadLogoForm" class="hidden">
            @csrf
            <input type="file" name="school_logo" id="school_logo_input" accept="image/png,image/jpeg,image/jpg,image/svg+xml" @change="previewLogo($event); document.getElementById('uploadLogoForm').submit();">
        </form>

        <form action="{{ route('student-cards.remove-logo') }}" method="POST" id="removeLogoForm" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Bar Menu Tab Navigasi Pengaturan Presensi -->
            <div class="flex items-center gap-2 p-1.5 bg-brand-surface rounded-2xl border border-brand-border shadow-xs w-full sm:w-auto self-start">
                <a href="{{ route('attendance-settings.index') }}"
                   class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 {{ request()->routeIs('attendance-settings.*') ? 'bg-brand-primary text-white font-medium shadow-sm' : 'bg-brand-surface border border-brand-border text-brand-text-muted hover:bg-brand-primary/5' }}">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>Alat</span>
                </a>
                <a href="{{ route('attendance-schedules.index') }}"
                   class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 {{ request()->routeIs('attendance-schedules.*') ? 'bg-brand-primary text-white font-medium shadow-sm' : 'bg-brand-surface border border-brand-border text-brand-text-muted hover:bg-brand-primary/5' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Keterlambatan</span>
                </a>
                <a href="{{ route('student-cards.index') }}"
                   class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 {{ request()->routeIs('student-cards.*') ? 'bg-brand-primary text-white font-medium shadow-sm' : 'bg-brand-surface border border-brand-border text-brand-text-muted hover:bg-brand-primary/5' }}">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Kartu</span>
                </a>
            </div>

            <!-- Form Cetak Kartu Pelajar -->
            <form action="{{ route('student-cards.print') }}" method="POST" target="_blank" id="printForm" class="space-y-6">
                @csrf

                <!-- SECTION 1: KUSTOMISASI DESAIN & PRATINJAU LANGSUNG -->
                <div class="bg-brand-surface p-6 rounded-2xl border border-brand-border shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div>
                            <h4 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                                <i class="fa-solid fa-palette text-brand-primary"></i> Desain & Template Kartu Pelajar
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Kustomisasi tampilan visual, atribut teks, serta warna utama kartu ID fisik siswa.
                            </p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-qrcode text-indigo-600"></i> Standard CR80 + QR Code
                        </span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <!-- Controls (Left - 7 cols) -->
                        <div class="lg:col-span-7 space-y-4">
                            <!-- Pemilihan Template -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Pilih Template Desain
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <label class="border-2 rounded-xl p-3 cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5"
                                        :class="template === 'modern' ? 'border-brand-primary bg-red-50/20 text-brand-primary font-bold shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-600'">
                                        <input type="radio" name="template" value="modern" x-model="template" class="sr-only">
                                        <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
                                        <span class="text-xs">Modern</span>
                                    </label>

                                    <label class="border-2 rounded-xl p-3 cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5"
                                        :class="template === 'classic' ? 'border-indigo-600 bg-indigo-50/20 text-indigo-700 font-bold shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-600'">
                                        <input type="radio" name="template" value="classic" x-model="template" class="sr-only">
                                        <i class="fa-solid fa-landmark text-lg"></i>
                                        <span class="text-xs">Klasik</span>
                                    </label>

                                    <label class="border-2 rounded-xl p-3 cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5"
                                        :class="template === 'minimalist' ? 'border-emerald-600 bg-emerald-50/20 text-emerald-700 font-bold shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-600'">
                                        <input type="radio" name="template" value="minimalist" x-model="template" class="sr-only">
                                        <i class="fa-solid fa-minus text-lg"></i>
                                        <span class="text-xs">Minimalis</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Pemilihan Warna Aksen -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                    Warna Aksen Kartu
                                </label>
                                <div class="flex items-center gap-3">
                                    <label class="cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="accent_color" value="red" x-model="accentColor" class="sr-only">
                                        <span class="w-7 h-7 rounded-full bg-red-600 inline-block border-2 transition-transform" :class="accentColor === 'red' ? 'border-gray-900 scale-110 shadow-sm' : 'border-transparent opacity-80'"></span>
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="accent_color" value="indigo" x-model="accentColor" class="sr-only">
                                        <span class="w-7 h-7 rounded-full bg-indigo-600 inline-block border-2 transition-transform" :class="accentColor === 'indigo' ? 'border-gray-900 scale-110 shadow-sm' : 'border-transparent opacity-80'"></span>
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="accent_color" value="emerald" x-model="accentColor" class="sr-only">
                                        <span class="w-7 h-7 rounded-full bg-emerald-600 inline-block border-2 transition-transform" :class="accentColor === 'emerald' ? 'border-gray-900 scale-110 shadow-sm' : 'border-transparent opacity-80'"></span>
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="accent_color" value="amber" x-model="accentColor" class="sr-only">
                                        <span class="w-7 h-7 rounded-full bg-amber-500 inline-block border-2 transition-transform" :class="accentColor === 'amber' ? 'border-gray-900 scale-110 shadow-sm' : 'border-transparent opacity-80'"></span>
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-1.5">
                                        <input type="radio" name="accent_color" value="slate" x-model="accentColor" class="sr-only">
                                        <span class="w-7 h-7 rounded-full bg-slate-800 inline-block border-2 transition-transform" :class="accentColor === 'slate' ? 'border-gray-900 scale-110 shadow-sm' : 'border-transparent opacity-80'"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Wording Inputs -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Sekolah / Lembaga</label>
                                    <input type="text" name="school_name" x-model="schoolName" required class="w-full border-gray-300 rounded-xl shadow-xs text-xs font-semibold focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Ajaran / Berlaku</label>
                                    <input type="text" name="academic_year" x-model="academicYear" required class="w-full border-gray-300 rounded-xl shadow-xs text-xs font-semibold focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Judul Kartu</label>
                                    <input type="text" name="card_title" x-model="cardTitle" required class="w-full border-gray-300 rounded-xl shadow-xs text-xs font-semibold focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Teks Catatan Footer</label>
                                    <input type="text" name="footer_text" x-model="footerText" class="w-full border-gray-300 rounded-xl shadow-xs text-xs focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                                </div>
                            </div>

                            <!-- Logo Sekolah Upload Controls -->
                            <div class="border-t border-gray-100 pt-3">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                    <span>Logo Sekolah</span>
                                    <span class="text-[10px] text-gray-400 font-normal">PNG, JPG, JPEG, SVG (Maks. 2 MB)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="document.getElementById('school_logo_input').click()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 transition flex items-center gap-2 cursor-pointer shadow-xs">
                                        <i class="fa-solid fa-cloud-arrow-up text-brand-primary"></i> Pilih / Unggah Logo
                                    </button>
                                    <button type="button" x-show="logoUrl" @click="removeLogoPreview(); document.getElementById('removeLogoForm').submit();" class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl border border-red-200 transition flex items-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-trash-can"></i> Hapus / Reset Logo
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1.5">Latar belakang transparan (PNG/SVG) direkomendasikan untuk hasil cetak optimal.</p>
                            </div>
                        </div>

                        <!-- Live Interactive Card Preview (Right - 5 cols) -->
                        <div class="lg:col-span-5 flex flex-col items-center justify-center p-4 bg-gray-50/80 border border-gray-200 rounded-2xl space-y-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-eye text-brand-primary"></i> Live Card Preview
                            </span>

                            <!-- Card Mockup Container (CR80 Aspect Ratio) -->
                            <div class="w-full max-w-[340px] h-[210px] rounded-2xl shadow-lg border relative overflow-hidden transition-all duration-300 flex flex-col justify-between p-3.5 select-none"
                                :class="{
                                    'bg-gradient-to-br from-slate-900 via-slate-800 to-red-950 text-white border-red-500/30': template === 'modern' && accentColor === 'red',
                                    'bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white border-indigo-500/30': template === 'modern' && accentColor === 'indigo',
                                    'bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 text-white border-emerald-500/30': template === 'modern' && accentColor === 'emerald',
                                    'bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 text-white border-amber-500/30': template === 'modern' && accentColor === 'amber',
                                    'bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white border-slate-700': template === 'modern' && accentColor === 'slate',
                                    
                                    'bg-white text-gray-900 border-2 border-red-600': template === 'classic' && accentColor === 'red',
                                    'bg-white text-gray-900 border-2 border-indigo-600': template === 'classic' && accentColor === 'indigo',
                                    'bg-white text-gray-900 border-2 border-emerald-600': template === 'classic' && accentColor === 'emerald',
                                    'bg-white text-gray-900 border-2 border-amber-500': template === 'classic' && accentColor === 'amber',
                                    'bg-white text-gray-900 border-2 border-slate-800': template === 'classic' && accentColor === 'slate',

                                    'bg-slate-50 text-gray-800 border border-gray-300': template === 'minimalist'
                                }">

                                <!-- Header Bar Mockup -->
                                <div class="flex items-center justify-between border-b pb-2"
                                    :class="template === 'modern' ? 'border-white/10' : 'border-gray-200'">
                                    <div class="flex items-center gap-2">
                                        <template x-if="logoUrl">
                                            <img :src="logoUrl" alt="Logo Sekolah" class="w-6 h-6 object-contain rounded shrink-0">
                                        </template>
                                        <template x-if="!logoUrl">
                                            <div class="w-6 h-6 rounded-md bg-white/20 flex items-center justify-center text-xs font-black shrink-0"
                                                 :class="accentColor === 'red' ? 'text-red-500' : (accentColor === 'indigo' ? 'text-indigo-400' : 'text-emerald-400')">
                                                H
                                            </div>
                                        </template>
                                        <div>
                                            <h5 class="text-[10px] font-black uppercase tracking-wider leading-none" x-text="schoolName || 'NAMA SEKOLAH'"></h5>
                                            <span class="text-[8px] opacity-75 leading-none" x-text="cardTitle || 'KARTU PELAJAR'"></span>
                                        </div>
                                    </div>
                                    <span class="text-[8px] font-bold px-1.5 py-0.5 rounded bg-white/15" x-text="academicYear"></span>
                                </div>

                                <!-- Card Body: Avatar & Info & QR -->
                                <div class="flex items-center justify-between gap-2 my-auto">
                                    <!-- Photo Avatar -->
                                    <div class="w-14 h-16 rounded-lg bg-gray-200 border border-white/20 overflow-hidden shrink-0 flex flex-col items-center justify-center text-gray-400">
                                        <i class="fa-solid fa-user text-2xl"></i>
                                    </div>

                                    <!-- Student Info -->
                                    <div class="flex-1 space-y-0.5 text-left pl-1">
                                        <h4 class="text-xs font-bold truncate">Ahmad Fikri Prasetyo</h4>
                                        <div class="text-[9px] opacity-80 leading-tight">
                                            <p><span class="font-semibold">NISN:</span> 0081234567</p>
                                            <p><span class="font-semibold">NIS:</span> 2026-1042</p>
                                            <p><span class="font-semibold">Kelas:</span> X IPA 1</p>
                                        </div>
                                    </div>

                                    <!-- Live QR Code Mockup -->
                                    <div class="w-14 h-14 bg-white p-1 rounded-md shadow-xs shrink-0 flex flex-col items-center justify-center">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=0081234567" 
                                             alt="QR Code" 
                                             class="w-full h-full object-contain"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="hidden text-center text-gray-900 text-[7px] font-bold">
                                            [QR SCAN]
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Bar -->
                                <div class="text-[7.5px] text-center pt-1 border-t truncate opacity-70"
                                     :class="template === 'modern' ? 'border-white/10' : 'border-gray-200'"
                                     x-text="footerText || 'Hak cipta terdaftar institusi sekolah.'">
                                </div>
                            </div>
                            <p class="text-[10.5px] text-gray-400 text-center leading-tight">
                                Fitur QR Code pada kartu terhubung penuh dengan scanner absensi sekolah.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PILIH DATA SISWA UNTUK DICETAK -->
                <div class="bg-brand-surface p-6 rounded-2xl border border-brand-border shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                        <div>
                            <h4 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                                <i class="fa-solid fa-users text-indigo-600"></i> Data Siswa Terdaftar ({{ count($students) }} Siswa)
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Centang siswa secara massal atau per individu untuk mencetak Kartu Pelajar.
                            </p>
                        </div>

                        <!-- Filter Controls -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Kelas -->
                            <select name="class_id_filter" onchange="window.location.href='{{ route('student-cards.index') }}?class_id=' + this.value" class="border-gray-300 rounded-xl text-xs font-semibold focus:ring-brand-primary focus:border-brand-primary py-2 px-3">
                                <option value="">Semua Kelas</option>
                                @foreach($classes as $cls)
                                    <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                                        {{ $cls->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Student List Table -->
                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="p-3 w-10 text-center">
                                        <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary h-4 w-4">
                                    </th>
                                    <th class="p-3">Siswa</th>
                                    <th class="p-3">NISN / NIS</th>
                                    <th class="p-3">Kelas</th>
                                    <th class="p-3">Token Presensi (QR)</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                                @forelse($students as $student)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="p-3 text-center">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" x-model="selectedStudents" class="student-checkbox rounded border-gray-300 text-brand-primary focus:ring-brand-primary h-4 w-4">
                                        </td>
                                        <td class="p-3 flex items-center gap-2.5">
                                            @if($student->avatar || $student->master_photo)
                                                <img src="{{ asset('storage/' . ($student->avatar ?? $student->master_photo)) }}" alt="{{ $student->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-brand-primary/10 text-brand-primary font-bold flex items-center justify-center text-xs">
                                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="font-bold text-gray-900 block">{{ $student->name }}</span>
                                                <span class="text-[10px] text-gray-400">{{ $student->email }}</span>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-semibold text-gray-800">{{ $student->nisn ?? '-' }}</span>
                                            <span class="text-gray-400 text-[10px] block">NIS: {{ $student->nis ?? '-' }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold text-[11px]">
                                                {{ $student->schoolClass->full_name ?? 'Tanpa Kelas' }}
                                            </span>
                                        </td>
                                        <td class="p-3">
                                            <code class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-[10px] font-mono">
                                                {{ $student->nisn ?? ($student->nis ?? 'STD-'.$student->id) }}
                                            </code>
                                        </td>
                                        <td class="p-3 text-right">
                                            <button type="submit" name="student_ids[]" value="{{ $student->id }}" class="px-3 py-1.5 bg-gray-100 hover:bg-brand-primary hover:text-white text-gray-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                                <i class="fa-solid fa-print"></i> Cetak Single
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-gray-400 font-medium">
                                            <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                            Belum ada data siswa ditemukan untuk kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Floating Bottom Submit Bar -->
                <div class="bg-brand-surface p-4 rounded-2xl border border-brand-border shadow-md flex items-center justify-between mt-6 sticky bottom-4 z-10">
                    <p class="text-xs text-gray-600 font-medium hidden sm:block">
                        <i class="fa-solid fa-check-circle text-emerald-500 me-1"></i>
                        <span x-text="selectedStudents.length"></span> siswa terpilih dari total {{ count($students) }} siswa.
                    </p>
                    <button type="submit" class="px-6 py-2.5 bg-brand-primary hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow-md transition inline-flex items-center gap-2 ml-auto">
                        <i class="fa-solid fa-print"></i> Pratinjau & Cetak Kartu Pelajar
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
