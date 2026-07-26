@php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Awal Institusi') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="onboardingWizard()">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-100 h-2">
                    <div class="bg-red-700 h-2 transition-all duration-500 ease-out" :style="'width: ' + (step === 1 ? '50%' : '100%')"></div>
                </div>

                <div class="p-8 sm:p-10">
                    <div class="text-center mb-10">
                        <h2 class="text-2xl font-black text-gray-900" x-text="step === 1 ? 'Pilih Metode Absensi' : 'Konfigurasi Parameter'"></h2>
                        <p class="mt-2 text-gray-500" x-text="step === 1 ? 'Pilih teknologi yang paling sesuai untuk merekam kehadiran di institusi Anda.' : 'Atur detail teknis untuk metode absensi yang telah Anda pilih.'"></p>
                    </div>

                    <form method="POST" action="{{ route('admin.onboarding') }}" id="onboardingForm">
                        @csrf
                        <input type="hidden" name="attendance_method" x-model="method">

                        <!-- STEP 1: PILIH METODE -->
                        <div x-show="step === 1" x-transition.opacity.duration.300ms>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <!-- Hardware -->
                                <div @click="method = 'hardware'" :class="{'border-red-600 ring-2 ring-red-600 bg-red-50': method === 'hardware', 'border-gray-200 hover:border-red-300': method !== 'hardware'}" class="relative cursor-pointer rounded-xl border p-5 transition-all">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2 bg-red-100 rounded-lg">
                                            <svg class="w-6 h-6 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                        </div>
                                        <div class="h-5 w-5 rounded-full border-2 flex items-center justify-center" :class="method === 'hardware' ? 'border-red-700 bg-red-700' : 'border-gray-300'">
                                            <div class="h-2.5 w-2.5 rounded-full bg-white" x-show="method === 'hardware'"></div>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Mesin Absensi</h3>
                                    <p class="text-sm text-gray-500">Gunakan perangkat keras pemindai sidik jari atau kartu pintar.</p>
                                </div>

                                <!-- WiFi -->
                                <div @click="method = 'wifi'" :class="{'border-red-600 ring-2 ring-red-600 bg-red-50': method === 'wifi', 'border-gray-200 hover:border-red-300': method !== 'wifi'}" class="relative cursor-pointer rounded-xl border p-5 transition-all">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                                        </div>
                                        <div class="h-5 w-5 rounded-full border-2 flex items-center justify-center" :class="method === 'wifi' ? 'border-red-700 bg-red-700' : 'border-gray-300'">
                                            <div class="h-2.5 w-2.5 rounded-full bg-white" x-show="method === 'wifi'"></div>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Jaringan WiFi</h3>
                                    <p class="text-sm text-gray-500">Anggota hanya bisa absen saat terhubung ke router spesifik kantor.</p>
                                </div>

                                <!-- GPS -->
                                <div @click="method = 'gps'" :class="{'border-red-600 ring-2 ring-red-600 bg-red-50': method === 'gps', 'border-gray-200 hover:border-red-300': method !== 'gps'}" class="relative cursor-pointer rounded-xl border p-5 transition-all">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2 bg-emerald-100 rounded-lg">
                                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <div class="h-5 w-5 rounded-full border-2 flex items-center justify-center" :class="method === 'gps' ? 'border-red-700 bg-red-700' : 'border-gray-300'">
                                            <div class="h-2.5 w-2.5 rounded-full bg-white" x-show="method === 'gps'"></div>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Geolokasi (GPS)</h3>
                                    <p class="text-sm text-gray-500">Batasi radius jarak pendaftaran absen dari titik koordinat kantor.</p>
                                </div>

                                <!-- Liveness -->
                                <div @click="method = 'liveness'" :class="{'border-red-600 ring-2 ring-red-600 bg-red-50': method === 'liveness', 'border-gray-200 hover:border-red-300': method !== 'liveness'}" class="relative cursor-pointer rounded-xl border p-5 transition-all">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2 bg-purple-100 rounded-lg">
                                            <svg class="w-6 h-6 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="h-5 w-5 rounded-full border-2 flex items-center justify-center" :class="method === 'liveness' ? 'border-red-700 bg-red-700' : 'border-gray-300'">
                                            <div class="h-2.5 w-2.5 rounded-full bg-white" x-show="method === 'liveness'"></div>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Pengenalan Wajah</h3>
                                    <p class="text-sm text-gray-500">Mencegah penitipan absen melalui pindai swafoto anti-kecurangan.</p>
                                </div>
                            </div>
                            
                            <!-- Error message from server -->
                            @error('attendance_method')
                                <p class="text-red-500 text-sm mt-3 font-medium">{{ $message }}</p>
                            @enderror

                            <div class="mt-8 flex justify-end">
                                <button type="button" @click="nextStep()" :disabled="!method" class="px-6 py-3 bg-red-700 text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    Lanjut ke Langkah 2
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2: KONFIGURASI -->
                        <div x-show="step === 2" x-transition.opacity.duration.300ms style="display: none;">
                            
                            <!-- Hardware Info -->
                            <div x-show="method === 'hardware'" class="bg-red-50 border border-red-100 rounded-xl p-6 text-center">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Tidak Ada Konfigurasi Tambahan</h3>
                                <p class="text-gray-600">Sistem akan secara otomatis men-generate <strong>Device Token</strong> unik yang bisa Anda temukan di Dasbor setelah proses ini selesai. Token ini digunakan untuk mengintegrasikan mesin absensi Anda ke sistem kami.</p>
                            </div>

                            <!-- WiFi Input -->
                            <div x-show="method === 'wifi'" class="space-y-4">
                                <div>
                                    <x-input-label for="wifi_bssid" value="Alamat BSSID / MAC Router" />
                                    <x-text-input id="wifi_bssid" class="block mt-1 w-full" type="text" name="wifi_bssid" placeholder="Contoh: 00:1A:2B:3C:4D:5E" />
                                    <p class="text-xs text-gray-500 mt-1">Anggota harus terhubung ke jaringan dengan alamat ini untuk bisa absen.</p>
                                    @error('wifi_bssid')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <!-- GPS Input -->
                            <div x-show="method === 'gps'" class="space-y-6">
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="font-bold text-gray-800 text-sm">Titik Pusat Koordinat</h3>
                                        <button type="button" @click="getLocation()" class="text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 px-3 py-1.5 rounded-lg flex items-center gap-1 transition shadow-sm" :disabled="isLocating">
                                            <svg x-show="!isLocating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <svg x-show="isLocating" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span x-text="isLocating ? 'Mencari...' : 'Gunakan Lokasi Saya Saat Ini'"></span>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="gps_lat" value="Latitude (Garis Lintang)" />
                                            <x-text-input id="gps_lat" x-model="gpsLat" class="block mt-1 w-full bg-white" type="text" name="gps_lat" placeholder="-6.200000" x-bind:required="method === 'gps'" />
                                            @error('gps_lat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <x-input-label for="gps_lng" value="Longitude (Garis Bujur)" />
                                            <x-text-input id="gps_lng" x-model="gpsLng" class="block mt-1 w-full bg-white" type="text" name="gps_lng" placeholder="106.816666" x-bind:required="method === 'gps'" />
                                            @error('gps_lng')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    <p x-show="locationError" class="text-xs text-rose-500 mt-2 font-medium" x-text="locationError"></p>
                                </div>
                                
                                <div>
                                    <x-input-label for="gps_radius" value="Radius Kedekatan (Meter)" />
                                    <x-text-input id="gps_radius" class="block mt-1 w-full" type="number" name="gps_radius" placeholder="Contoh: 50" min="5" value="50" x-bind:required="method === 'gps'" />
                                    <p class="text-xs text-gray-500 mt-1">Jarak maksimal (dalam meter) anggota dari titik di atas agar bisa absen.</p>
                                    @error('gps_radius')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <!-- Liveness Info -->
                            <div x-show="method === 'liveness'" class="bg-purple-50 border border-purple-100 rounded-xl p-6 text-center">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Tidak Ada Konfigurasi Tambahan</h3>
                                <p class="text-gray-600">Sistem AI Pengenalan Wajah akan otomatis diaktifkan. Anda hanya perlu memastikan setiap anggota melakukan <strong>Enrollment Wajah</strong> melalui aplikasi mereka masing-masing.</p>
                            </div>

                            <div class="mt-10 flex justify-between">
                                <button type="button" @click="step = 1" class="px-6 py-3 bg-white text-gray-700 border border-gray-300 font-bold rounded-xl shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Kembali
                                </button>
                                
                                <button type="submit" class="px-6 py-3 bg-red-700 text-white font-bold rounded-xl shadow-md hover:bg-red-800 transition flex items-center gap-2">
                                    Selesaikan Pengaturan
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('onboardingWizard', () => ({
                step: 1,
                method: '',
                gpsLat: '',
                gpsLng: '',
                isLocating: false,
                locationError: '',

                nextStep() {
                    if (this.method) {
                        this.step = 2;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                getLocation() {
                    this.isLocating = true;
                    this.locationError = '';

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.gpsLat = position.coords.latitude;
                                this.gpsLng = position.coords.longitude;
                                this.isLocating = false;
                            },
                            (error) => {
                                this.isLocating = false;
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        this.locationError = "Izin lokasi ditolak oleh browser Anda.";
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        this.locationError = "Informasi lokasi tidak tersedia.";
                                        break;
                                    case error.TIMEOUT:
                                        this.locationError = "Waktu permintaan lokasi habis.";
                                        break;
                                    default:
                                        this.locationError = "Terjadi kesalahan yang tidak diketahui.";
                                        break;
                                }
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    } else {
                        this.isLocating = false;
                        this.locationError = "Geolocation tidak didukung oleh browser Anda.";
                    }
                }
            }));
        });
    </script>
</x-app-layout>

