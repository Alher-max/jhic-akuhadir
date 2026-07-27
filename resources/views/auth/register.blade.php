<x-guest-layout>
    <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-12 bg-brand-bg m-0 p-0"
         x-data="{
            activeTab: '{{ old('role_type', request('role_type', 'kepala_sekolah')) }}',
            tenantCode: '{{ old('tenant_code', request('tenant_code', '')) }}',
            schoolName: '',
            isChecking: false,
            codeValid: false,
            codeError: '',
            studentQuery: '',
            studentResults: [],
            isSearchingStudent: false,
            selectedStudent: null,
            studentSearchAttempted: false,
            checkCode() {
                if (this.activeTab === 'kepala_sekolah' || this.activeTab === 'student') { this.codeValid = true; return; }
                let code = this.tenantCode.trim().toUpperCase();
                if (code.length === 8) {
                    this.isChecking = true; this.codeError = ''; this.schoolName = ''; this.codeValid = false;
                    fetch('/check-school-code/' + code)
                        .then(r => r.json())
                        .then(data => {
                            this.isChecking = false;
                            if (data.success) { this.codeValid = true; this.schoolName = data.school_name; this.codeError = ''; }
                            else { this.codeValid = false; this.codeError = data.message || 'NPSN / Kode Sekolah tidak ditemukan'; }
                        })
                        .catch(() => { this.isChecking = false; this.codeValid = false; this.codeError = 'Gagal memeriksa NPSN / kode sekolah'; });
                } else {
                    this.codeValid = false; this.schoolName = '';
                    this.codeError = code.length > 0 ? 'NPSN / Kode sekolah harus 8 karakter' : '';
                }
            },
            searchStudents() {
                if (this.studentQuery.trim().length < 2 || !this.tenantCode) { this.studentResults = []; this.studentSearchAttempted = false; return; }
                this.isSearchingStudent = true; this.studentSearchAttempted = false;
                fetch('/search-students?school_code=' + encodeURIComponent(this.tenantCode.trim().toUpperCase()) + '&q=' + encodeURIComponent(this.studentQuery.trim()))
                    .then(r => r.json())
                    .then(data => { this.isSearchingStudent = false; this.studentResults = data; this.studentSearchAttempted = true; })
                    .catch(() => { this.isSearchingStudent = false; this.studentResults = []; this.studentSearchAttempted = true; });
            },
            selectStudent(student) { this.selectedStudent = student; this.studentResults = []; this.studentSearchAttempted = false; },
            clearSelectedStudent() { this.selectedStudent = null; this.studentQuery = ''; this.studentResults = []; this.studentSearchAttempted = false; },
            init() { if (this.tenantCode.length === 8 && this.activeTab !== 'kepala_sekolah') { this.checkCode(); } }
         }">

        <!-- ==================== PANEL KIRI: BRANDING ==================== -->
        <div class="lg:col-span-5 hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-brand-primary to-[#8B0000] text-white min-h-screen" style="position: relative; overflow: hidden;">
            <!-- Decorative Blobs -->
            <div style="position:absolute; right:-80px; bottom:-80px; width:320px; height:320px; background:rgba(255,255,255,0.08); border-radius:9999px; filter:blur(48px); pointer-events:none;"></div>
            <div style="position:absolute; left:-80px; top:-80px; width:320px; height:320px; background:rgba(0,0,0,0.15); border-radius:9999px; filter:blur(48px); pointer-events:none;"></div>

            <!-- Logo -->
            <div style="position:relative; z-index:10;">
                <a href="/" style="display:inline-flex; align-items:center; gap:12px; text-decoration:none;">
                    <img src="{{ asset('hadiryuklogo1-4.png') }}" alt="HadirSekolah" style="height:44px; width:auto; object-fit:contain; filter:brightness(0) invert(1); opacity:0.95;">
                    <div>
                        <span style="display:block; font-size:22px; font-weight:800; color:white; letter-spacing:-0.5px;">Hadir<span style="color:#fca5a5;">Sekolah</span></span>
                        <span style="display:block; font-size:10px; color:#fecaca; font-weight:500; letter-spacing:2px; text-transform:uppercase;">Modern Attendance Platform</span>
                    </div>
                </a>
            </div>

            <!-- Value Proposition -->
            <div style="position:relative; z-index:10; max-width:380px; margin-top:auto; margin-bottom:auto;">
                <div style="display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:9999px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.2); font-size:11px; font-weight:600; color:#fecaca; margin-bottom:20px;">
                    <span style="width:8px;height:8px;border-radius:9999px;background:#34d399;display:inline-block;" class="animate-pulse"></span>
                    Sistem Presensi Generasi Baru
                </div>

                <h1 style="font-size:32px; font-weight:800; color:white; line-height:1.2; letter-spacing:-0.5px; margin-bottom:16px;">
                    Presensi Digital Terpadu & Anti-Kecurangan
                </h1>

                <p style="color:rgba(254,202,202,0.9); font-size:14px; line-height:1.7; margin-bottom:28px;">
                    Solusi cerdas kelola sistem presensi sekolah secara terpusat, akurat, dan transparan dalam satu ekosistem terintegrasi.
                </p>

                <!-- Feature Cards -->
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:12px;">
                        <span style="width:28px; height:28px; border-radius:8px; background:rgba(52,211,153,0.2); color:#6ee7b7; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">✓</span>
                        <span style="font-size:13px; color:white; font-weight:500;">🛡️ AI Biometric & Anti-Fake GPS Verification</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:12px;">
                        <span style="width:28px; height:28px; border-radius:8px; background:rgba(52,211,153,0.2); color:#6ee7b7; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">✓</span>
                        <span style="font-size:13px; color:white; font-weight:500;">📱 Notifikasi WhatsApp Real-Time ke Orang Tua</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); border-radius:12px;">
                        <span style="width:28px; height:28px; border-radius:8px; background:rgba(52,211,153,0.2); color:#6ee7b7; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">✓</span>
                        <span style="font-size:13px; color:white; font-weight:500;">📊 Rekapitulasi & Laporan Kehadiran Otomatis</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="position:relative; z-index:10; font-size:11px; color:rgba(254,202,202,0.7); font-weight:500;">
                © 2026 Almas Alfatih, CTO PT Thortech. Hak Cipta Dilindungi.
            </div>
        </div>

        <!-- ==================== PANEL KANAN: FORM ==================== -->
        <div class="lg:col-span-7 flex items-center justify-center p-6 sm:p-12 min-h-screen bg-brand-bg">
            <div class="w-full bg-brand-surface border border-brand-border rounded-2xl p-8 shadow-sm" style="max-width: 480px;">

                <!-- Mobile Logo -->
                <div class="lg:hidden mb-6 text-center">
                    <a href="/" style="display:inline-flex; align-items:center; gap:10px; text-decoration:none; justify-content:center;">
                        <img src="{{ asset('hadiryuklogo1-4.png') }}" alt="HadirSekolah" style="height:36px; width:auto; object-fit:contain;">
                        <span style="font-size:22px; font-weight:900; letter-spacing:-0.5px;">
                            <span style="color:#B81D24;">Hadir</span><span style="color:#1A1516;">Sekolah</span>
                        </span>
                    </a>
                </div>

                <!-- Form Header -->
                <div style="margin-bottom: 24px; text-align: center;">
                    <h2 style="font-size:22px; font-weight:800; color:#1A1516; letter-spacing:-0.3px; margin:0 0 6px 0;">Pendaftaran Akun Baru</h2>
                    <p style="font-size:13px; color:#6B5E60; margin:0;">Pilih peran Anda dan lengkapi data di bawah ini untuk memulai.</p>
                </div>

                <!-- Role Switcher -->
                <div class="grid grid-cols-2 gap-1.5 p-1.5 mb-4 bg-brand-bg rounded-xl border border-brand-border">
                    <button type="button" @click="activeTab = 'kepala_sekolah'; codeValid = true;"
                            :class="{ 'bg-brand-primary text-white font-semibold shadow-sm': activeTab === 'kepala_sekolah', 'text-brand-text-muted hover:bg-brand-primary/5 font-medium': activeTab !== 'kepala_sekolah' }"
                            class="py-2.5 px-2 text-xs text-center rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span class="truncate">Kepala Sekolah</span>
                    </button>
                    <button type="button" @click="activeTab = 'teacher'; checkCode();"
                            :class="{ 'bg-brand-primary text-white font-semibold shadow-sm': activeTab === 'teacher', 'text-brand-text-muted hover:bg-brand-primary/5 font-medium': activeTab !== 'teacher' }"
                            class="py-2.5 px-2 text-xs text-center rounded-lg transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span class="truncate">Tenaga Pendidik / Kependidikan</span>
                    </button>
                </div>

                <!-- Info Box Akun Siswa & Orang Tua -->
                <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs leading-relaxed flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-blue-600 text-base shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-semibold block mb-0.5">Info Akun Siswa & Orang Tua:</strong>
                        Akun Siswa dan Orang Tua dibuatkan langsung oleh sekolah. Silakan hubungi Operator Sekolah atau Wali Kelas Anda untuk mendapatkan hak akses login.
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-xl text-xs font-medium">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="role_type" :value="activeTab">
                    <input type="hidden" name="role" :value="activeTab">

                    <!-- ===== KODE SEKOLAH (Non-Kepala Sekolah & Non-Student) ===== -->
                    <div x-show="activeTab !== 'kepala_sekolah' && activeTab !== 'student'" style="display: none; margin-bottom: 16px;">
                        <label for="tenant_code" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">NPSN / Kode Sekolah (8 Digit) <span x-show="activeTab === 'teacher'" class="text-gray-500 font-normal">(Opsional jika diundang)</span></label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-key" style="font-size:13px;"></i>
                            </span>
                            <input id="tenant_code" type="text" name="tenant_code"
                                   x-model="tenantCode" @input="checkCode()"
                                   placeholder="Contoh: 20101234" maxlength="8"
                                   x-bind:required="activeTab !== 'kepala_sekolah' && activeTab !== 'teacher' && activeTab !== 'student'"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:4px; text-align:center; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <!-- Code Feedback -->
                        <div class="mt-2 text-xs font-medium">
                            <div x-show="isChecking" class="flex items-center justify-center gap-1.5 py-1" style="color:#2563eb;">
                                <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                Memeriksa kode sekolah...
                            </div>
                            <div x-show="!isChecking && codeValid && activeTab !== 'kepala_sekolah' && activeTab !== 'student'" class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl" style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; font-weight:600;">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                ✓ Terhubung dengan: <strong x-text="schoolName"></strong>
                            </div>
                            <div x-show="!isChecking && !codeValid && codeError && activeTab !== 'kepala_sekolah' && activeTab !== 'student'" class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl" style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; font-weight:600;">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span x-text="codeError"></span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('tenant_code')" class="mt-1" />
                    </div>

                    <!-- ===== NPSN (Kepala Sekolah Only) ===== -->
                    <div x-show="activeTab === 'kepala_sekolah'" style="margin-bottom: 16px;">
                        <label for="npsn" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">NPSN <span style="color:#6B5E60; font-weight:400;">(8 Digit)</span></label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-hashtag" style="font-size:13px;"></i>
                            </span>
                            <input id="npsn" type="text" name="npsn"
                                   value="{{ old('npsn') }}"
                                   x-bind:required="activeTab === 'kepala_sekolah'"
                                   maxlength="8"
                                   placeholder="Contoh: 20101234"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; letter-spacing:2px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <x-input-error :messages="$errors->get('npsn')" class="mt-1" />
                    </div>

                    <!-- ===== NAMA SEKOLAH (Kepala Sekolah Only) ===== -->
                    <div x-show="activeTab === 'kepala_sekolah'" style="margin-bottom: 16px;">
                        <label for="tenant_name" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">Nama Sekolah</label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-school" style="font-size:13px;"></i>
                            </span>
                            <input id="tenant_name" type="text" name="tenant_name"
                                   value="{{ old('tenant_name') }}"
                                   x-bind:required="activeTab === 'kepala_sekolah'"
                                   autocomplete="organization" autofocus
                                   placeholder="Contoh: SMA Negeri 1 Jakarta"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <x-input-error :messages="$errors->get('tenant_name')" class="mt-1" />
                    </div>

                    <!-- ===== NISN (Siswa Only) ===== -->
                    <div x-show="activeTab === 'student'" style="display: none; margin-bottom: 16px;">
                        <label for="nisn" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">NISN</label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-id-card" style="font-size:13px;"></i>
                            </span>
                            <input id="nisn" type="text" name="nisn"
                                   value="{{ old('nisn') }}"
                                   placeholder="Contoh: 0012345678"
                                   x-bind:required="activeTab === 'student'"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <x-input-error :messages="$errors->get('nisn')" class="mt-1" />
                    </div>

                    <!-- ===== TANGGAL LAHIR (Siswa Only) ===== -->
                    <div x-show="activeTab === 'student'" style="display: none; margin-bottom: 16px;">
                        <label for="birth_date" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">Tanggal Lahir</label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-calendar-day" style="font-size:13px;"></i>
                            </span>
                            <input id="birth_date" type="date" name="birth_date"
                                   value="{{ old('birth_date') }}"
                                   x-bind:required="activeTab === 'student'"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <x-input-error :messages="$errors->get('birth_date')" class="mt-1" />
                    </div>

                    <!-- ===== CARI SISWA (Orang Tua Only) ===== -->
                    <div x-show="activeTab === 'parent'" style="display: none; margin-bottom: 16px;">
                        <label for="student_search" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">Cari Nama / NISN Siswa (Anak)</label>
                        <input type="hidden" name="student_id" :value="selectedStudent ? selectedStudent.id : ''">

                        <!-- Selected Student Badge -->
                        <div x-show="selectedStudent" class="p-3 rounded-xl flex items-center justify-between" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                            <div style="font-size:12px; color:#166534; font-weight:600; display:flex; align-items:center; gap:6px;">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                ✓ Siswa Terpilih: <strong x-text="selectedStudent ? selectedStudent.name : ''"></strong> (NISN: <span x-text="selectedStudent ? selectedStudent.nisn : ''"></span>)
                            </div>
                            <button type="button" @click="clearSelectedStudent()" style="font-size:11px; color:#e11d48; font-weight:700; text-decoration:underline; margin-left:8px; background:none; border:none; cursor:pointer;">Ganti</button>
                        </div>

                        <!-- Search Input -->
                        <div x-show="!selectedStudent" class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-magnifying-glass" style="font-size:13px;"></i>
                            </span>
                            <input id="student_search" type="text"
                                   x-model="studentQuery"
                                   @input.debounce.300ms="searchStudents()"
                                   placeholder="Masukkan Nama atau NISN Siswa..."
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />

                            <!-- Dropdown -->
                            <div x-show="studentResults.length > 0 && !selectedStudent"
                                 style="position:absolute; top:100%; left:0; right:0; z-index:50; margin-top:4px; background:white; border:1px solid #EAE2E3; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.1); max-height:192px; overflow-y:auto;">
                                <template x-for="item in studentResults" :key="item.id">
                                    <button type="button" @click="selectStudent(item)"
                                            style="width:100%; text-align:left; padding:10px 16px; border-bottom:1px solid #EAE2E3; display:flex; align-items:center; justify-content:space-between; font-size:12px; cursor:pointer; background:none; transition:background 0.15s;"
                                            class="hover:bg-brand-primary/5 last:border-0">
                                        <div>
                                            <span style="font-weight:700; color:#1A1516;" x-text="item.name"></span>
                                            <span style="color:#6B5E60; margin-left:6px;" x-text="' - NISN: ' + item.nisn"></span>
                                        </div>
                                        <span x-show="item.class_name" style="background:#f3f4f6; color:#6B5E60; font-weight:500; padding:2px 8px; border-radius:4px; font-size:10px;" x-text="item.class_name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Searching -->
                        <div x-show="isSearchingStudent" class="mt-2 flex items-center gap-1" style="font-size:12px; color:#2563eb; font-weight:500;">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            Mencari data siswa...
                        </div>
                        <!-- Empty -->
                        <div x-show="!selectedStudent && !isSearchingStudent && studentSearchAttempted && studentResults.length === 0 && studentQuery.length >= 2"
                             class="mt-2 p-3 rounded-xl flex items-start gap-2" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:12px; font-weight:500; line-height:1.5;">
                            <span style="font-size:14px; line-height:1;">⚠️</span>
                            <span>Nama atau NISN siswa tidak ditemukan. Silakan minta anak/siswa mendaftar terlebih dahulu.</span>
                        </div>
                        <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                    </div>

                    <!-- ===== NAMA LENGKAP ===== -->
                    <div x-show="activeTab !== 'student'" style="margin-bottom: 16px;">
                        <label for="name" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;"
                               x-text="activeTab === 'kepala_sekolah' ? 'Nama Lengkap Kepala Sekolah' : (activeTab === 'parent' ? 'Nama Lengkap Orang Tua / Wali' : 'Nama Lengkap')"></label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-user" style="font-size:13px;"></i>
                            </span>
                            <input id="name" type="text" name="name"
                                   value="{{ old('name') }}"
                                   x-bind:required="activeTab !== 'student'"
                                   autocomplete="name"
                                   placeholder="Nama Lengkap Anda"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <!-- ===== EMAIL ===== -->
                    <div x-show="activeTab !== 'student'" style="margin-bottom: 16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <label for="email" style="display:block; font-size:11px; font-weight:600; color:#1A1516;">Email</label>
                            <span x-show="activeTab === 'student'" style="font-size:10px; color:#6B5E60;">(Opsional untuk Siswa)</span>
                        </div>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-envelope" style="font-size:13px;"></i>
                            </span>
                            <input id="email" type="email" name="email"
                                   value="{{ old('email') }}"
                                   x-bind:required="activeTab !== 'student'"
                                   autocomplete="username"
                                   placeholder="nama@email.com"
                                   style="padding-left: 2.9rem !important; padding-right: 1rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <!-- ===== PASSWORD ===== -->
                    <div x-data="{ showPass: false }" style="margin-bottom: 16px;">
                        <label for="password" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">Password</label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-lock" style="font-size:13px;"></i>
                            </span>
                            <input id="password"
                                   :type="showPass ? 'text' : 'password'"
                                   name="password" required autocomplete="new-password"
                                   placeholder="••••••••"
                                   style="padding-left: 2.9rem !important; padding-right: 2.8rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                            <button type="button" @click="showPass = !showPass"
                                    style="position:absolute; right:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; display:flex; align-items:center; justify-content:center; width:20px; height:20px; background:none; border:none; cursor:pointer;">
                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'" style="font-size:13px;"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <!-- ===== KONFIRMASI PASSWORD ===== -->
                    <div x-data="{ showConfirm: false }" style="margin-bottom: 16px;">
                        <label for="password_confirmation" style="display:block; font-size:11px; font-weight:600; color:#1A1516; margin-bottom:6px;">Konfirmasi Password</label>
                        <div class="relative w-full flex items-center">
                            <span style="position:absolute; left:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; pointer-events:none; display:flex; align-items:center; justify-content:center; width:20px; height:20px;">
                                <i class="fa-solid fa-lock" style="font-size:13px;"></i>
                            </span>
                            <input id="password_confirmation"
                                   :type="showConfirm ? 'text' : 'password'"
                                   name="password_confirmation" required autocomplete="new-password"
                                   placeholder="••••••••"
                                   style="padding-left: 2.9rem !important; padding-right: 2.8rem !important; width:100%; background:#FFFFFF; border:1px solid #EAE2E3; color:#1A1516; border-radius:12px; padding-top:12px; padding-bottom:12px; font-size:13px; outline:none; transition:border-color 0.2s;"
                                   class="placeholder:text-brand-text-muted focus:border-brand-primary" />
                            <button type="button" @click="showConfirm = !showConfirm"
                                    style="position:absolute; right:1.15rem; top:50%; transform:translateY(-50%); z-index:10; color:#6B5E60; display:flex; align-items:center; justify-content:center; width:20px; height:20px; background:none; border:none; cursor:pointer;">
                                <i class="fa-solid" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'" style="font-size:13px;"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>

                    <!-- ===== SUBMIT ===== -->
                    <div style="padding-top: 8px;">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-brand-primary hover:bg-brand-primary/90 active:scale-95 transition-all text-white font-semibold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                style="padding:14px; font-size:15px;"
                                x-bind:disabled="activeTab === 'student' ? false : (activeTab === 'kepala_sekolah' ? false : (activeTab === 'teacher' ? (tenantCode.length > 0 && !codeValid) : !codeValid))">
                            <span>Register</span>
                            <i class="fa-solid fa-arrow-right" style="font-size:13px;"></i>
                        </button>

                        <div style="text-align:center; padding-top:16px;">
                            <a href="{{ route('login') }}" style="font-size:12px; color:#6B5E60; text-decoration:none; font-weight:500; transition:color 0.2s;"
                               class="hover:text-brand-primary">
                                {{ __('Already registered?') }} <span style="font-weight:700; color:#B81D24; text-decoration:underline;">Masuk di sini</span>
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-guest-layout>
