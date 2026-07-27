<x-guest-layout>
    <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-12 bg-brand-bg m-0 p-0">

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
                    <p class="text-xs text-brand-text-muted mt-1 font-medium">Presensi Digital Terpadu & Anti-Kecurangan</p>
                </div>

                <!-- Form Header -->
                <div style="margin-bottom: 24px; text-align: center;">
                    <h2 style="font-size:22px; font-weight:800; color:#1A1516; letter-spacing:-0.3px; margin:0 0 6px 0;">Masuk ke Sistem</h2>
                    <p style="font-size:13px; color:#6B5E60; margin:0;">Silakan masukkan kredensial akun Anda di bawah ini.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Kode Sekolah / NPSN -->
                    <div class="mb-4">
                        <x-input-label for="school_code" :value="__('Kode Sekolah / NPSN')" />
                        <x-text-input id="school_code" class="block mt-1 w-full border-brand-border focus:border-brand-primary" type="text" name="school_code" :value="old('school_code')" required autofocus placeholder="Contoh: 20102026" />
                        <x-input-error :messages="$errors->get('school_code')" class="mt-2" />
                    </div>

                    <!-- Login ID (Email / NISN / NIP) -->
                    <div>
                        <x-input-label for="login_id" :value="__('Email / ID Pengguna')" />
                        <x-text-input id="login_id" class="block mt-1 w-full border-brand-border focus:border-brand-primary" type="text" name="login_id" :value="old('login_id')" required autocomplete="username" placeholder="Contoh: email@sekolah.sch.id, NISN, NIP, atau NUPTK" />
                        <p class="mt-1 text-xs text-brand-text-muted">Gunakan Email (Kepala Sekolah/Guru), NISN (Siswa), NIP/NUPTK, atau Username.</p>
                        <x-input-error :messages="$errors->get('login_id')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4" x-data="{ showPassword: false }">
                        <x-input-label for="password" :value="__('Password')" />

                        <div class="relative mt-1">
                            <x-text-input id="password" class="block w-full border-brand-border focus:border-brand-primary pr-10"
                                            type="password"
                                            x-bind:type="showPassword ? 'text' : 'password'"
                                            name="password"
                                            required autocomplete="current-password" />

                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-text-muted hover:text-brand-primary focus:outline-none transition-colors"
                                    aria-label="Toggle Password Visibility">
                                <!-- Eye Open Icon (Shown when password is hidden) -->
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Eye Off / Coret Icon (Shown when password is visible) -->
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary" name="remember">
                            <span class="ms-2 text-sm text-brand-text-muted">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        @if (Route::has('password.request'))
                            <a class="underline text-xs text-brand-text-muted hover:text-brand-primary rounded-md" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif

                        <x-primary-button class="bg-brand-primary hover:bg-brand-primary/90 focus:bg-brand-primary/90 text-white font-bold py-2.5 px-6 rounded-xl transition">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>

                <div class="mt-6 text-center text-sm text-brand-text-muted">
                    Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-brand-primary hover:underline">Daftar di sini</a>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>
