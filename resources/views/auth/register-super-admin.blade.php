<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Registrasi Super Admin</h2>
        <p class="text-gray-600 mt-2">Institusi: <strong class="text-indigo-600">{{ $tenant->name }}</strong></p>
    </div>

    <form method="POST" action="{{ route('register.super-admin', $invitation->token) }}">
        @csrf

        <!-- Email Address (Readonly) -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100 text-gray-500 cursor-not-allowed" type="email" name="email" :value="$invitation->email" readonly />
            <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah karena terikat pada tautan undangan.</p>
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
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
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="ms-4 w-full justify-center text-lg py-3">
                {{ __('Selesaikan Registrasi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
