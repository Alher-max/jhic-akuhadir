<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center p-6 bg-brand-bg">
        <div class="mb-6 text-center">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="text-3xl font-black text-brand-primary tracking-tight">Hadir<span class="text-brand-text-main">Sekolah</span></span>
            </a>
            <p class="text-xs text-brand-text-muted mt-1 font-medium">Aktivasi Akun Siswa Pertama Kali</p>
        </div>

        <div class="w-full sm:max-w-md bg-brand-surface border border-brand-border rounded-2xl shadow-sm p-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('student.activate.store') }}">
                @csrf

                <!-- NISN ATAU EMAIL -->
                <div>
                    <x-input-label for="nisn_or_email" :value="__('NISN atau Email Siswa')" />
                    <x-text-input id="nisn_or_email" class="block mt-1 w-full border-brand-border focus:border-brand-primary" type="text" name="nisn_or_email" :value="old('nisn_or_email')" placeholder="Masukkan NISN atau Email terdaftar" required autofocus />
                    <x-input-error :messages="$errors->get('nisn_or_email')" class="mt-2" />
                </div>

                <!-- Password Baru -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password Baru')" />
                    <x-text-input id="password" class="block mt-1 w-full border-brand-border focus:border-brand-primary" type="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Konfirmasi Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full border-brand-border focus:border-brand-primary" type="password" name="password_confirmation" placeholder="Ketik ulang password baru" required />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a class="underline text-xs text-brand-text-muted hover:text-brand-primary rounded-md" href="{{ route('login') }}">
                        {{ __('Kembali ke Login') }}
                    </a>

                    <x-primary-button class="bg-brand-primary hover:bg-brand-primary/90 text-white font-bold py-2.5 px-6 rounded-xl transition">
                        {{ __('Aktifkan Akun') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
