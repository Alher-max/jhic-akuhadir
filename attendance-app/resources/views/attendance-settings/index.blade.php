<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengelolaan Alat & Metode Presensi') }}
            </h2>
            <a href="{{ route('attendance-schedules.index') }}" class="px-4 py-2 bg-brand-primary text-white hover:bg-red-700 text-xs sm:text-sm font-bold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> Atur Jam Operasional Presensi
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ 
        method_rfid: {{ $settings->method_rfid ? 'true' : 'false' }},
        method_qrcode: {{ $settings->method_qrcode ? 'true' : 'false' }},
        method_biometric: {{ $settings->method_biometric ? 'true' : 'false' }},
        method_pwa: {{ $settings->method_pwa ? 'true' : 'false' }},
        method_manual: {{ $settings->method_manual ? 'true' : 'false' }},
        method_wifi: {{ $settings->method_wifi ? 'true' : 'false' }},
        is_liveness_active: {{ ($settings->is_liveness_active ?? true) ? 'true' : 'false' }},
        showDeviceModal: false
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if (session('success'))
                <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- BAGIAN 1: KONFIGURASI 5 METODE PRESENSI -->
            <form action="{{ route('attendance-settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-brand-text-main">Metode Presensi Aktif</h3>
                            <p class="text-sm text-brand-text-muted mt-1">Nyalakan atau matikan metode yang didukung oleh institusi Anda.</p>
                        </div>
                        <button type="submit" class="bg-brand-primary text-white hover:bg-brand-primary/90 font-semibold py-2 px-6 rounded-lg text-sm transition shadow-sm">
                            Simpan Pengaturan
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- 1. RFID -->
                        <div class="border rounded-xl p-5 shadow-sm flex flex-col justify-between cursor-pointer transition-colors" 
                             :class="method_rfid ? 'border-rose-600 bg-rose-50/10' : 'border-gray-200 bg-white'"
                             @click="method_rfid = !method_rfid">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="method_rfid ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'">
                                            <i class="fa-regular fa-id-card text-xl"></i>
                                        </div>
                                        <span class="font-bold text-gray-900">RFID / Tap Card</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                        <input type="checkbox" name="method_rfid" x-model="method_rfid" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed mb-4">Presensi cepat dengan menempelkan kartu ID/RFID ke mesin reader IoT terpasang.</p>
                            </div>
                            
                            <div x-show="method_rfid" @click.stop>
                                <div class="bg-white p-3 rounded-lg border border-gray-100 mt-2">
                                    <div class="text-xs font-semibold text-gray-600 mb-1">API Secret Key (Untuk Mesin)</div>
                                    <div class="flex items-center gap-2">
                                        <input type="text" readonly value="{{ $settings->rfid_secret_key }}" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-500" />
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="generate_new_key" value="1" class="text-rose-600 focus:ring-rose-600 h-4 w-4 border-gray-300 rounded mr-2">
                                            <span class="text-xs text-gray-600 whitespace-nowrap">Reset Key</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. QR Code -->
                        <div class="border rounded-xl p-5 shadow-sm flex flex-col justify-between cursor-pointer transition-colors" 
                             :class="method_qrcode ? 'border-rose-600 bg-rose-50/10' : 'border-gray-200 bg-white'"
                             @click="method_qrcode = !method_qrcode">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="method_qrcode ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500'">
                                            <i class="fa-solid fa-qrcode text-xl"></i>
                                        </div>
                                        <span class="font-bold text-gray-900">QR Code Scanner</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                        <input type="checkbox" name="method_qrcode" x-model="method_qrcode" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">Presensi via pemindaian QR Code di HP atau kios scanner sekolah.</p>
                            </div>
                        </div>

                        <!-- 3. Biometric -->
                        <!-- 3. Physical Biometric -->
                        <div class="border rounded-xl p-5 shadow-sm flex flex-col justify-between cursor-pointer transition-colors" 
                             :class="method_biometric ? 'border-rose-600 bg-rose-50/10' : 'border-gray-200 bg-white'"
                             @click="method_biometric = !method_biometric">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="method_biometric ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500'">
                                            <i class="fa-solid fa-fingerprint text-xl"></i>
                                        </div>
                                        <span class="font-bold text-gray-900">👆 Mesin Biometrik Fisik</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                        <input type="checkbox" name="method_biometric" x-model="method_biometric" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed mb-4">Presensi menggunakan mesin absensi biometrik fisik terintegrasi (seperti mesin sidik jari/wajah IP) yang terpasang di area sekolah.</p>
                            </div>

                            <div x-show="method_biometric" @click.stop>
                                <div class="bg-white p-3 rounded-lg border border-gray-100 mt-2">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">IP Address / Secret Key Integrasi</label>
                                    <input type="text" name="biometric_ip_address" value="{{ $settings->biometric_ip_address }}" placeholder="Cth: 192.168.1.100 atau SecretKey123" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-700 shadow-sm focus:ring-rose-600 focus:border-rose-600" />
                                </div>
                            </div>
                        </div>

                        <!-- 4. Manual -->
                        <div class="border rounded-xl p-5 shadow-sm flex flex-col justify-between cursor-pointer transition-colors" 
                             :class="method_manual ? 'border-rose-600 bg-rose-50/10' : 'border-gray-200 bg-white'"
                             @click="method_manual = !method_manual">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="method_manual ? 'bg-rose-100 text-rose-600' : 'bg-gray-100 text-gray-500'">
                                            <i class="fa-solid fa-clipboard-check text-xl"></i>
                                        </div>
                                        <span class="font-bold text-gray-900">📝 Presensi Manual</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                        <input type="checkbox" name="method_manual" x-model="method_manual" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">Pencatatan kehadiran langsung oleh Guru Kelas / Operator di Dasbor.</p>
                            </div>
                        </div>

                        <!-- 5. PWA (Mobile Clock-In) -->
                        <div class="border rounded-xl p-5 shadow-sm flex flex-col justify-between cursor-pointer transition-colors md:col-span-2" 
                             :class="method_pwa ? 'border-rose-600 bg-rose-50/10' : 'border-gray-200 bg-white'"
                             @click="method_pwa = !method_pwa">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="method_pwa ? 'bg-sky-100 text-sky-600' : 'bg-gray-100 text-gray-500'">
                                            <i class="fa-solid fa-mobile-screen-button text-xl"></i>
                                        </div>
                                        <span class="font-bold text-gray-900">📱 Absen Mandiri via PWA (Mobile Clock-In)</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                        <input type="checkbox" name="method_pwa" x-model="method_pwa" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed mb-4">Mengizinkan siswa dan guru melakukan presensi masuk/pulang secara mandiri langsung dari smartphone mereka melalui aplikasi web PWA.</p>
                            </div>

                            <div x-show="method_pwa" @click.stop>
                                <div class="bg-white p-3 rounded-lg border border-gray-100 mt-2 space-y-3">
                                    
                                    <!-- Opsi 1: Geofencing -->
                                    <div class="border border-gray-100 rounded-md p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-rose-500"></i> Opsi 1: Validasi Geofencing GPS</div>
                                            <button type="button" onclick="navigator.geolocation.getCurrentPosition(function(position) { document.getElementById('latitude_input').value = position.coords.latitude; document.getElementById('longitude_input').value = position.coords.longitude; })" class="text-[10px] bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded hover:bg-gray-50 shadow-sm flex items-center gap-1 cursor-pointer">
                                                <i class="fa-solid fa-location-crosshairs text-amber-500"></i> Dapatkan Lokasi Saat Ini
                                            </button>
                                        </div>

                                        <!-- BOX PETUNJUK (INFO ALERT) PRESISI KOORDINAT GPS -->
                                        <div class="mb-3 bg-amber-50/90 border border-amber-200 rounded-lg p-2.5 text-[11px] text-amber-900 leading-relaxed">
                                            <div class="font-bold flex items-center gap-1.5 text-amber-950 mb-1">
                                                <span>💡 Petunjuk Presisi Koordinat GPS:</span>
                                            </div>
                                            <ul class="list-disc list-inside space-y-1 text-[10.5px] text-amber-900">
                                                <li>Pengambilan lokasi otomatis via Laptop/PC rawan meleset karena mengandalkan jaringan IP/Wi-Fi.</li>
                                                <li>Disarankan menyalin titik lokasi langsung dari Google Maps: <strong>Buka Google Maps &rarr; Klik kanan pada gerbang/gedung sekolah &rarr; Klik angka koordinat untuk menyalin &rarr; Paste angka Latitude dan Longitude ke kolom di bawah.</strong></li>
                                            </ul>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="flex gap-2">
                                                <div class="flex-1">
                                                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Latitude</label>
                                                    <input type="text" id="latitude_input" name="latitude" value="{{ $settings->latitude }}" onpaste="handleGpsPaste(event)" placeholder="Cth: -7.8123456" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-700 shadow-sm focus:ring-rose-600 focus:border-rose-600" />
                                                    <p class="text-[9.5px] text-gray-400 mt-0.5">Koordinat lintang (Auto-split saat paste dari Google Maps)</p>
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Longitude</label>
                                                    <input type="text" id="longitude_input" name="longitude" value="{{ $settings->longitude }}" onpaste="handleGpsPaste(event)" placeholder="Cth: 110.3678901" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-700 shadow-sm focus:ring-rose-600 focus:border-rose-600" />
                                                    <p class="text-[9.5px] text-gray-400 mt-0.5">Koordinat bujur (Auto-split saat paste dari Google Maps)</p>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Radius Perimeter (Meter)</label>
                                                <input type="number" name="radius_meters" value="{{ $settings->radius_meters }}" placeholder="Cth: 100" min="10" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-700 shadow-sm focus:ring-rose-600 focus:border-rose-600" />
                                                <p class="text-[9.5px] text-gray-400 mt-0.5">Batas toleransi jangkauan presensi siswa dari titik lokasi sekolah</p>
                                            </div>
                                            <p class="text-[10px] text-gray-500 leading-tight mt-1">Hanya dapat melakukan clock-in jika berada di dalam radius sekolah.</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Opsi 2: Liveness Detection -->
                                    <div class="border border-gray-100 rounded-md p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex flex-col">
                                                <div class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5 mb-1"><i class="fa-solid fa-face-smile text-emerald-500"></i> Opsi 2: AI Biometric Liveness</div>
                                                <p class="text-[10px] text-gray-500 leading-tight">Mewajibkan pemindaian wajah real-time (Mencegah foto palsu).</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer ml-3" @click.stop>
                                                <input type="checkbox" name="is_liveness_active" x-model="is_liveness_active" value="1" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Opsi 3: Wi-Fi Locking -->
                                    <div class="border border-gray-100 rounded-md p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex flex-col">
                                                <div class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5 mb-1"><i class="fa-solid fa-wifi text-sky-500"></i> Opsi 3: Wi-Fi Network Locking</div>
                                                <p class="text-[10px] text-gray-500 leading-tight">Wajib terhubung ke Wi-Fi resmi sekolah.</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer ml-3">
                                                <input type="checkbox" name="method_wifi" x-model="method_wifi" value="1" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-sky-500"></div>
                                            </label>
                                        </div>
                                        <div x-show="method_wifi" class="space-y-2 mt-2 pt-2 border-t border-gray-100">
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Daftar SSID Wi-Fi Diizinkan</label>
                                                <textarea name="wifi_allowed_ssids" rows="1" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-700 shadow-sm focus:ring-rose-600 focus:border-rose-600" placeholder="Contoh: Wi-Fi_Sekolah_1, Wi-Fi_Perpus">{{ is_array($settings->wifi_allowed_ssids) ? implode(', ', $settings->wifi_allowed_ssids) : '' }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Daftar MAC Address / BSSID</label>
                                                <textarea name="wifi_allowed_macs" rows="1" class="block w-full bg-white border-gray-300 rounded-md text-xs text-gray-700 shadow-sm focus:ring-rose-600 focus:border-rose-600" placeholder="Contoh: 00:1A:2B:3C:4D:5E">{{ is_array($settings->wifi_allowed_macs) ? implode(', ', $settings->wifi_allowed_macs) : '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <!-- BAGIAN 2: DAFTAR PERANGKAT & ROUTER TERHUBUNG -->
            <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border">
                <div class="p-6 border-b border-brand-border flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-bold text-brand-text-main">Perangkat & Router Terdaftar</h3>
                        <p class="text-sm text-brand-text-muted mt-1">Daftar mesin absen dan pemancar akses Wi-Fi yang dikelola sistem.</p>
                    </div>
                    <button @click="showDeviceModal = true" type="button" class="bg-white text-brand-text-main border border-brand-border hover:bg-gray-50 font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i> Tambah Perangkat
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Nama Perangkat</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Tipe</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Lokasi & IP/MAC</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Status</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border bg-white">
                            @forelse($devices as $device)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $device->device_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if($device->device_type === 'rfid')
                                            <span class="inline-flex items-center gap-1.5"><i class="fa-regular fa-id-card text-indigo-500"></i> Mesin RFID</span>
                                        @elseif($device->device_type === 'qrcode')
                                            <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-qrcode text-emerald-500"></i> Kiosk QR Code</span>
                                        @elseif($device->device_type === 'biometric')
                                            <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-fingerprint text-amber-500"></i> Mesin Biometrik</span>
                                        @elseif($device->device_type === 'wifi_router')
                                            <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-wifi text-sky-500"></i> Router Wi-Fi</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium">{{ $device->location ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $device->ip_address ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($device->status === 'online')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span> Online
                                            </span>
                                        @elseif($device->status === 'offline')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Offline</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Maintenance</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <form action="{{ route('attendance-settings.devices.destroy', $device->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus perangkat ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-server text-4xl mb-3 text-gray-300"></i>
                                            <p class="font-medium text-gray-600">Belum ada perangkat terdaftar.</p>
                                            <p class="text-sm mt-1 text-gray-400">Silakan tambah mesin presensi atau router melalui tombol di atas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal Tambah Perangkat -->
        <div x-show="showDeviceModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeviceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDeviceModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeviceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title">Tambah Perangkat Baru</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Daftarkan mesin atau router jaringan ke dalam sistem.</p>
                    </div>
                    
                    <form action="{{ route('attendance-settings.devices.store') }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Perangkat <span class="text-red-500">*</span></label>
                                <input type="text" name="device_name" required placeholder="Contoh: Mesin Gerbang Timur" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipe Perangkat <span class="text-red-500">*</span></label>
                                <select name="device_type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="rfid">Mesin RFID (Tap Card)</option>
                                    <option value="qrcode">Kiosk QR Code Scanner</option>
                                    <option value="biometric">Mesin Biometrik Wajah/Jari</option>
                                    <option value="wifi_router">Router Wi-Fi / Access Point</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lokasi Penempatan</label>
                                <input type="text" name="location" placeholder="Contoh: Lobi Utama" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alamat IP / MAC Address (Opsional)</label>
                                <input type="text" name="ip_address" placeholder="Contoh: 192.168.1.100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status Awal <span class="text-red-500">*</span></label>
                                <select name="status" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                                    <option value="online">Online</option>
                                    <option value="offline" selected>Offline</option>
                                    <option value="maintenance">Maintenance (Perbaikan)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="showDeviceModal = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary transition">
                                Batal
                            </button>
                            <button type="submit" class="bg-brand-primary py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-brand-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary transition">
                                Simpan Perangkat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function handleGpsPaste(e) {
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                if (pastedData && pastedData.includes(',')) {
                    e.preventDefault();
                    const parts = pastedData.split(',');
                    const latInput = document.getElementById('latitude_input');
                    const lngInput = document.getElementById('longitude_input');
                    if (latInput && parts[0]) {
                        latInput.value = parts[0].trim();
                        latInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (lngInput && parts[1]) {
                        lngInput.value = parts[1].trim();
                        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
        </script>
    </div>
</x-app-layout>
