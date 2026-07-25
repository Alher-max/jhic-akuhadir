<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 p-8 space-y-6">
            
            <!-- LOGO & BRANDING -->
            <div class="text-center space-y-3">
                <div class="flex justify-center mb-2">
                    <a href="/" class="inline-flex items-center gap-2 text-decoration-none">
                        <img src="{{ asset('hadiryuklogo1-4.png') }}" alt="HadirSekolah" class="h-9 w-auto object-contain">
                        <span class="text-2xl font-extrabold tracking-tight">
                            <span class="text-red-700">Hadir</span><span class="text-slate-900">Sekolah</span>
                        </span>
                    </a>
                </div>

                <!-- BADGE NAMA SEKOLAH -->
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100 shadow-sm">
                    <i class="fa-solid fa-school text-red-600"></i>
                    <span>{{ $invitation->tenant->name ?? $tenant->name ?? 'SMA Negeri 2 Yogyakarta' }}</span>
                </div>

                <!-- TITLE & SUBTITLE -->
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Registrasi Staf Operasional</h2>
                <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                    Lengkapi data Anda untuk mengaktifkan akun operasional sekolah secara resmi.
                </p>
            </div>

            <!-- FORM STAF -->
            <form method="POST" action="{{ route('register.staff', ['token' => $invitation->token]) }}" class="space-y-4">
                @csrf

                <!-- EMAIL (READONLY) -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Terdaftar</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ $invitation->email }}" readonly disabled
                               class="w-full pl-10 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 cursor-not-allowed">
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-slate-400"></i> Email ini ditetapkan oleh pimpinan dan tidak dapat diubah.
                    </p>
                </div>

                <!-- NAMA LENGKAP -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Contoh: Eko Prasetyo, S.Kom."
                               class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all">
                    </div>
                    @error('name')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PASSWORD -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi <span class="text-red-500">*</span></label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-key text-sm"></i>
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter"
                               class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all">
                    </div>
                    @error('password')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CONFIRM PASSWORD -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-key text-sm"></i>
                        </div>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi Anda"
                               class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all">
                    </div>
                    @error('password_confirmation')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="pt-2">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-red-500/20 transition-all flex items-center justify-center gap-2 text-base cursor-pointer">
                        <span>Daftar Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
