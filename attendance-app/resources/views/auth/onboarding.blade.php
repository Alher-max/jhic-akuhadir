@php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag; @endphp
<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-xl rounded-2xl shadow-xl border border-slate-100 bg-white p-8">
            
            <!-- Logo / Badge -->
            <div class="flex justify-center mb-6">
                <a href="/" class="inline-flex items-center gap-2 text-decoration-none" style="text-decoration: none;">
                    <img src="{{ asset('hadiryuklogo1-4.png') }}" alt="HadirSekolah" class="h-8 w-auto object-contain">
                    <span class="text-xl font-extrabold tracking-tight">
                        <span class="text-red-700">Hadir</span><span class="text-slate-900">Sekolah</span>
                    </span>
                </a>
            </div>

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-semibold mb-4 border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Langkah Terakhir
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Pendaftaran Institusi Berhasil! 🎉</h2>
                <p class="mt-2 text-sm text-slate-500">
                    Tentukan bagaimana Anda ingin mengelola konfigurasi teknis absensi sekolah.
                </p>
            </div>

            <form method="POST" action="{{ route('onboarding') }}" x-data="{ option: 'self' }">
                @csrf
                <div class="space-y-4">
                    <!-- Opsi 1 -->
                    <label class="relative block cursor-pointer rounded-xl p-5 transition-all"
                           :class="{ 'border-2 border-red-600 bg-red-50/40 shadow-sm': option === 'self', 'border border-slate-200 bg-white hover:border-slate-300': option !== 'self' }"
                           @click="option = 'self'">
                        <input type="radio" name="onboarding_option" value="self" class="sr-only" x-model="option">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-slate-900 font-bold">Kelola Mandiri</p>
                                    <div class="shrink-0 text-red-600" x-show="option === 'self'" style="display: none;">
                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 leading-relaxed">Saya ingin mengaktifkan jadwal, lokasi GPS, dan data awal sekolah sendiri.</p>
                            </div>
                        </div>
                    </label>

                    <!-- Opsi 2 -->
                    <label class="relative block cursor-pointer rounded-xl p-5 transition-all"
                           :class="{ 'border-2 border-red-600 bg-red-50/40 shadow-sm': option === 'delegate', 'border border-slate-200 bg-white hover:border-slate-300': option !== 'delegate' }"
                           @click="option = 'delegate'">
                        <input type="radio" name="onboarding_option" value="delegate" class="sr-only" x-model="option">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-slate-900 font-bold">Delegasi ke Staf Operasional</p>
                                    <div class="shrink-0 text-red-600" x-show="option === 'delegate'" style="display: none;">
                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 leading-relaxed">Kirimkan akses/undangan ke Tim IT atau Sekretaris Sekolah untuk melengkapi data.</p>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Input Dinamis untuk Delegasi -->
                <div x-show="option === 'delegate'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="mt-6 p-6 bg-slate-50 rounded-xl border border-slate-200 space-y-4"
                     style="display: none;">
                    
                    <div>
                        <label for="delegate_name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Staf</label>
                        <input id="delegate_name" type="text" name="delegate_name" value="{{ old('delegate_name') }}" placeholder="Contoh: Budi Santoso"
                               class="w-full border-slate-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm text-sm p-2.5">
                        @error('delegate_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="delegate_email" class="block text-sm font-semibold text-slate-700 mb-1">Email Staf</label>
                        <input id="delegate_email" type="email" name="delegate_email" value="{{ old('delegate_email') }}" placeholder="nama.staf@contoh.com"
                               class="w-full border-slate-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm text-sm p-2.5">
                        <p class="text-xs text-slate-500 mt-1.5">Kami akan mengirimkan link konfigurasi ke email ini.</p>
                        @error('delegate_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                @error('onboarding_option')
                    <p class="text-red-500 text-sm mt-4 text-center font-medium">{{ $message }}</p>
                @enderror

                <div class="mt-8">
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white font-semibold py-3.5 px-6 rounded-xl w-full shadow-lg shadow-red-700/20 transition-all flex items-center justify-center space-x-2 text-base">
                        <span>Lanjutkan ke Dashboard &rarr;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
