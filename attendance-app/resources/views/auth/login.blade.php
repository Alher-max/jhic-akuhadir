<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center p-6 bg-brand-bg">
        <div class="mb-6 text-center">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="text-3xl font-black text-brand-primary tracking-tight">Hadir<span class="text-brand-text-main">Sekolah</span></span>
            </a>
            <p class="text-xs text-brand-text-muted mt-1 font-medium">Presensi Digital Terpadu & Anti-Kecurangan</p>
        </div>

        <div class="w-full sm:max-w-md bg-brand-surface border border-brand-border rounded-2xl shadow-sm p-8">
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
</x-guest-layout>
