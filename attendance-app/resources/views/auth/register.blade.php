<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Institution Type -->
        <div>
            <x-input-label for="institution_type" :value="__('Tipe Institusi')" />
            <select id="institution_type" name="institution_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required autofocus>
                <option value="" disabled selected>Pilih Tipe Institusi</option>
                <option value="Sekolah / Madrasah" {{ old('institution_type') == 'Sekolah / Madrasah' ? 'selected' : '' }}>Sekolah / Madrasah</option>
                <option value="Perusahaan / Bisnis" {{ old('institution_type') == 'Perusahaan / Bisnis' ? 'selected' : '' }}>Perusahaan / Bisnis</option>
                <option value="Bimbel / Pusat Kursus" {{ old('institution_type') == 'Bimbel / Pusat Kursus' ? 'selected' : '' }}>Bimbel / Pusat Kursus</option>
            </select>
            <x-input-error :messages="$errors->get('institution_type')" class="mt-2" />
        </div>

        <!-- Tenant Name -->
        <div class="mt-4">
            <x-input-label for="tenant_name" :value="__('Nama Sekolah / Perusahaan / Bimbel')" />
            <x-text-input id="tenant_name" class="block mt-1 w-full" type="text" name="tenant_name" :value="old('tenant_name')" required autocomplete="organization" />
            <x-input-error :messages="$errors->get('tenant_name')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Nama Lengkap Pimpinan (Kepala Sekolah / Direktur / Owner)')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
