<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('Kami telah mengirimkan kode OTP 6-digit ke email Anda. Silakan masukkan kode tersebut di bawah ini untuk memverifikasi akun Anda.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('register.verify-otp') }}" x-data="{
        otp: ['', '', '', '', '', ''],
        focusNext(index) {
            if (this.otp[index].length === 1 && index < 5) {
                this.$refs['otp' + (index + 1)].focus();
            }
        },
        focusPrev(index, event) {
            if (event.key === 'Backspace' && this.otp[index].length === 0 && index > 0) {
                this.$refs['otp' + (index - 1)].focus();
            }
        },
        get fullOtp() {
            return this.otp.join('');
        }
    }">
        @csrf

        <input type="hidden" name="otp" :value="fullOtp">

        <!-- OTP Inputs -->
        <div class="flex justify-between max-w-sm mx-auto mb-6 gap-2">
            <template x-for="(digit, index) in otp" :key="index">
                <input type="text" maxlength="1" x-model="otp[index]"
                    @input="focusNext(index)" @keydown="focusPrev(index, $event)"
                    x-ref="`otp${index}`"
                    class="w-12 h-14 text-center text-2xl font-bold font-mono border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm bg-white"
                    autocomplete="off" required>
            </template>
        </div>
        
        <x-input-error :messages="$errors->get('otp')" class="mb-4 text-center" />

        <div class="flex items-center justify-between mt-4">
            <!-- Countdown Component -->
            <div x-data="{
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
                <button type="button" @click="resendOtp()" :disabled="!canResend"
                        class="text-sm font-semibold transition-colors"
                        :class="canResend ? 'text-indigo-600 hover:text-indigo-900 cursor-pointer' : 'text-gray-400 cursor-not-allowed'">
                    <span x-show="!canResend">Kirim Ulang (<span x-text="seconds"></span>s)</span>
                    <span x-show="canResend" style="display: none;">Kirim Ulang OTP</span>
                </button>
            </div>

            <x-primary-button>
                {{ __('Verifikasi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
