<x-guest-layout>
    <div class="min-h-screen w-full flex items-center justify-center bg-brand-bg p-4 sm:p-6">
        <div class="w-full max-w-md bg-brand-surface border border-brand-border rounded-2xl p-6 sm:p-8 shadow-sm text-center">
            
            <!-- LOGO HEADER -->
            <div class="flex justify-center mb-4">
                <img src="{{ asset('hadiryuklogo1-4.png') }}" alt="HadirSekolah Logo" class="h-12 object-contain">
            </div>

            <!-- JUDUL & INSTRUKSI -->
            <h2 class="text-xl font-bold text-brand-text-main mb-2">Verifikasi Kode OTP</h2>
            <p class="text-xs text-brand-text-muted mb-6 leading-relaxed">
                Kode OTP telah dikirimkan ke email Anda dan berlaku selama <span class="font-bold text-brand-primary">5 menit</span>. Silakan masukkan kode tersebut di bawah ini untuk memverifikasi akun Anda.
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if(app()->environment('local') && isset($localOtp))
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl text-center">
                    <div class="font-bold mb-1"><i class="fa-solid fa-code text-amber-500"></i> Mode Lokal / Dev Aktif</div>
                    Kode OTP Asli: <strong class="font-mono text-base tracking-widest text-amber-600">{{ $localOtp }}</strong><br>
                    <span class="text-amber-700/80 mt-1 block">Atau gunakan kode bypass: <strong class="font-mono">123456</strong></span>
                </div>
            @endif

            <!-- FORM OTP -->
            <form method="POST" action="{{ route('register.verify-otp') }}" x-data="{
                otp: ['', '', '', '', '', ''],
                handlePaste(e) {
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const digits = paste.replace(/\D/g, '').substring(0, 6);
                    if (digits.length > 0) {
                        for (let i = 0; i < digits.length; i++) {
                            this.otp[i] = digits[i];
                        }
                        if (digits.length === 6) {
                            setTimeout(() => { this.$el.submit(); }, 100);
                        } else {
                            setTimeout(() => {
                                const inputs = this.$el.querySelectorAll('input[type=text]');
                                if(inputs[digits.length]) inputs[digits.length].focus();
                            }, 50);
                        }
                    }
                },
                handleInput(index, e) {
                    if (e.target.value) {
                        if (index < 5) {
                            e.target.nextElementSibling?.focus();
                        } else {
                            if (this.otp.every(d => d !== '')) {
                                setTimeout(() => { this.$el.submit(); }, 100);
                            }
                        }
                    }
                }
            }" @submit="document.getElementById('otp_final').value = otp.join('')" class="space-y-6">
                @csrf
                
                <input type="hidden" name="otp" id="otp_final">

                <!-- 6 KOTAK INPUT OTP (GRID / FLEX) -->
                <div class="flex justify-center gap-2 sm:gap-3 my-4">
                    <template x-for="(digit, index) in otp" :key="index">
                        <input type="text" inputmode="numeric" maxlength="1" x-model="otp[index]"
                            @paste.prevent="handlePaste($event)"
                            @input="handleInput(index, $event)" 
                            @keydown.backspace="if (!$el.value) $el.previousElementSibling?.focus()"
                            class="w-11 h-13 text-center text-xl font-bold font-mono text-brand-text-main bg-brand-bg border border-brand-border rounded-xl focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all"
                            autocomplete="off" required>
                    </template>
                </div>
                
                <x-input-error :messages="$errors->get('otp')" class="mb-4 text-center" />

                <!-- TIMER / KIRIM ULANG -->
                <div class="text-xs text-brand-text-muted flex justify-center items-center gap-1" x-data="{
                    seconds: 60,
                    canResend: false,
                    init() {
                        this.startCountdown();
                    },
                    startCountdown() {
                        this.seconds = 60;
                        this.canResend = false;
                        let interval = setInterval(() => {
                            this.seconds--;
                            if (this.seconds <= 0) {
                                clearInterval(interval);
                                this.canResend = true;
                            }
                        }, 1000);
                    },
                    resendOtp() {
                        if(!this.canResend) return;
                        fetch('{{ route('register.resend-otp') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(res => {
                            this.startCountdown();
                        });
                    }
                }">
                    <span x-show="!canResend">Tidak menerima kode?</span>
                    <button type="button" @click="resendOtp()" :disabled="!canResend"
                            class="text-brand-primary font-semibold hover:underline"
                            :class="canResend ? 'cursor-pointer' : 'cursor-not-allowed'">
                        <span x-show="!canResend">Kirim Ulang (<span x-text="seconds"></span>s)</span>
                        <span x-show="canResend" style="display: none;">Kirim Ulang OTP</span>
                    </button>
                </div>

                <!-- TOMBOL VERIFIKASI -->
                <button 
                    type="submit" 
                    class="w-full bg-brand-primary hover:bg-[#8B0000] text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition-all text-sm uppercase tracking-wider"
                >
                    Verifikasi Sekarang
                </button>
            </form>

        </div>
    </div>
</x-guest-layout>
