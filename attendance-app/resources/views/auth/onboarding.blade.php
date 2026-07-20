<x-guest-layout>
    <div x-data="{ option: 'self' }">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Pendaftaran Institusi Berhasil!</h2>
            <p class="mt-2 text-sm text-gray-600">
                Langkah terakhir, tentukan bagaimana Anda ingin mengelola sistem absensi ini.
            </p>
        </div>

        <form method="POST" action="{{ route('onboarding') }}">
            @csrf

            <div class="space-y-4">
                <!-- Opsi A -->
                <label class="relative block cursor-pointer rounded-lg border bg-white p-4 shadow-sm hover:border-gray-300 sm:p-6"
                       :class="{ 'border-indigo-500 ring-1 ring-indigo-500': option === 'self', 'border-gray-200': option !== 'self' }"
                       @click="option = 'self'">
                    <input type="radio" name="onboarding_option" value="self" class="sr-only" x-model="option">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">Kelola Mandiri</p>
                                <p class="mt-1 text-sm text-gray-500">Saya ingin mengelola konfigurasi teknisnya sendiri.</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-indigo-600" x-show="option === 'self'" style="display: none;">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </label>

                <!-- Opsi B -->
                <label class="relative block cursor-pointer rounded-lg border bg-white p-4 shadow-sm hover:border-gray-300 sm:p-6"
                       :class="{ 'border-indigo-500 ring-1 ring-indigo-500': option === 'delegate', 'border-gray-200': option !== 'delegate' }"
                       @click="option = 'delegate'">
                    <input type="radio" name="onboarding_option" value="delegate" class="sr-only" x-model="option">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">Delegasi ke Staf</p>
                                <p class="mt-1 text-sm text-gray-500">Saya ingin mendelegasikan ke staf operasional (Tim IT / Sekretaris).</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-indigo-600" x-show="option === 'delegate'" style="display: none;">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Input Dinamis untuk Email Staf -->
            <div x-show="option === 'delegate'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="mt-6 p-5 bg-gray-50 rounded-lg border border-gray-200"
                 style="display: none;">
                
                <x-input-label for="delegate_email" :value="__('Email Staf yang Didelegasikan')" />
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M3 4a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2H3zm14 2v.511l-7 4.2-7-4.2V6h14zM3 14v-5.289l7 4.2 7-4.2V14H3z" />
                        </svg>
                    </div>
                    <x-text-input id="delegate_email" class="block w-full pl-10" type="email" name="delegate_email" :value="old('delegate_email')" placeholder="nama.staf@contoh.com" />
                </div>
                <x-input-error :messages="$errors->get('delegate_email')" class="mt-2" />
            </div>

            <x-input-error :messages="$errors->get('onboarding_option')" class="mt-4" />

            <div class="mt-8 flex justify-end">
                <x-primary-button>
                    {{ __('Lanjutkan ke Dasbor') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
