<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HadirYuk') }} - Member Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $category = Auth::user()->tenant->business_category ?? 'education';
    $theme = [
        'education' => [
            'header_bg' => 'bg-indigo-600',
            'header_text' => 'text-indigo-200',
            'btn_logout' => 'bg-indigo-700/50 hover:bg-indigo-700',
            'icon_logout' => 'text-indigo-100',
            'link_color' => 'text-indigo-600 hover:text-indigo-700',
            'btn_secondary' => 'border-indigo-200 text-indigo-600 bg-indigo-50 hover:bg-indigo-100',
            'leave_text' => 'Ajukan Izin / Sakit',
        ],
        'corporate' => [
            'header_bg' => 'bg-slate-800',
            'header_text' => 'text-slate-300',
            'btn_logout' => 'bg-slate-700/50 hover:bg-slate-700',
            'icon_logout' => 'text-slate-200',
            'link_color' => 'text-slate-700 hover:text-slate-800',
            'btn_secondary' => 'border-slate-300 text-slate-700 bg-slate-50 hover:bg-slate-200',
            'leave_text' => 'Pengajuan Cuti / Tugas Luar',
        ],
        'umkm' => [
            'header_bg' => 'bg-amber-600',
            'header_text' => 'text-amber-100',
            'btn_logout' => 'bg-amber-700/50 hover:bg-amber-700',
            'icon_logout' => 'text-amber-50',
            'link_color' => 'text-amber-600 hover:text-amber-700',
            'btn_secondary' => 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100',
            'leave_text' => 'Izin Tidak Masuk / Shift',
        ]
    ];
    $activeTheme = $theme[$category] ?? $theme['education'];
@endphp
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col md:max-w-md md:mx-auto md:bg-white md:shadow-xl md:min-h-screen relative">
        
        <!-- Minimalist Topbar -->
        <header class="{{ $activeTheme['header_bg'] }} text-white p-5 rounded-b-3xl shadow-lg relative z-10 transition-colors duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold tracking-tight">{{ Auth::user()->name }}</h1>
                    <p class="{{ $activeTheme['header_text'] }} text-sm font-medium mt-0.5 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        {{ Auth::user()->tenant->name ?? 'Institusi' }}
                    </p>
                </div>
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 {{ $activeTheme['btn_logout'] }} rounded-full transition">
                        <svg class="w-5 h-5 {{ $activeTheme['icon_logout'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-5 pt-8 pb-10 flex flex-col gap-8 -mt-6">
            
            <!-- Clock & Action Card -->
            <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-gray-100 flex flex-col items-center justify-center relative z-20" x-data="digitalClock()">
                <p class="text-gray-500 font-medium text-sm mb-2" x-text="dateString"></p>
                <div class="text-5xl font-black text-gray-800 tracking-tighter tabular-nums mb-8" x-text="timeString">
                    00:00:00
                </div>

                @if($todayLeave)
                    <!-- KONDISI 0: Izin Disetujui -->
                    <button disabled class="w-full relative rounded-2xl bg-amber-100 p-1 shadow-inner cursor-not-allowed">
                        <div class="relative bg-amber-50 text-amber-600 border border-amber-200 rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Anda Hari Ini: 
                            @if($todayLeave->type === 'sick') Sakit
                            @elseif($todayLeave->type === 'permission') Izin
                            @else Tugas Luar @endif
                            (Disetujui)
                        </div>
                    </button>
                    <!-- KONDISI 1: Belum Absen -->
                    <form method="POST" action="{{ route('member.clock-in') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="latitude" :value="lat">
                        <input type="hidden" name="longitude" :value="lng">
                        
                        <template x-if="isGpsRequired && gpsError">
                            <div class="mb-3 w-full bg-rose-50 text-rose-600 border border-rose-200 rounded-xl py-2 px-3 font-semibold text-xs text-center">
                                <span x-text="gpsError"></span>
                            </div>
                        </template>
                        <template x-if="isGpsRequired && isLoadingGps">
                            <div class="mb-3 w-full bg-blue-50 text-blue-600 border border-blue-200 rounded-xl py-2 px-3 font-semibold text-xs text-center flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mendapatkan Lokasi GPS...
                            </div>
                        </template>

                        <button type="submit" :disabled="isGpsRequired && (!lat || isLoadingGps)"
                                :class="(isGpsRequired && (!lat || isLoadingGps)) ? 'opacity-60 cursor-not-allowed' : 'hover:scale-[1.02] active:scale-95 group'" 
                                class="w-full relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 p-1 shadow-lg shadow-emerald-500/30 transition-all">
                            <div class="absolute inset-0 bg-white/20" :class="(isGpsRequired && (!lat || isLoadingGps)) ? '' : 'group-hover:bg-transparent transition-colors'"></div>
                            <div class="relative bg-emerald-50 text-white rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2 transition-colors" :class="(isGpsRequired && (!lat || isLoadingGps)) ? '' : 'group-hover:bg-transparent'" style="background-color: transparent;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                MASUK
                            </div>
                        </button>
                    </form>
                @elseif(!$todayAttendance->clock_out && !in_array($todayAttendance->status, ['sick', 'permission', 'duty_trip']))
                    <!-- KONDISI 2: Sudah Masuk, Belum Pulang -->
                    <form method="POST" action="{{ route('member.clock-out') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="latitude" :value="lat">
                        <input type="hidden" name="longitude" :value="lng">
                        
                        <template x-if="isGpsRequired && gpsError">
                            <div class="mb-3 w-full bg-rose-50 text-rose-600 border border-rose-200 rounded-xl py-2 px-3 font-semibold text-xs text-center">
                                <span x-text="gpsError"></span>
                            </div>
                        </template>
                        <template x-if="isGpsRequired && isLoadingGps">
                            <div class="mb-3 w-full bg-blue-50 text-blue-600 border border-blue-200 rounded-xl py-2 px-3 font-semibold text-xs text-center flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mendapatkan Lokasi GPS...
                            </div>
                        </template>

                        <button type="submit" :disabled="isGpsRequired && (!lat || isLoadingGps)"
                                :class="(isGpsRequired && (!lat || isLoadingGps)) ? 'opacity-60 cursor-not-allowed' : 'hover:scale-[1.02] active:scale-95 group'"
                                class="w-full relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-red-500 p-1 shadow-lg shadow-red-500/30 transition-all">
                            <div class="absolute inset-0 bg-white/20" :class="(isGpsRequired && (!lat || isLoadingGps)) ? '' : 'group-hover:bg-transparent transition-colors'"></div>
                            <div class="relative bg-red-50 text-white rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2 transition-colors" :class="(isGpsRequired && (!lat || isLoadingGps)) ? '' : 'group-hover:bg-transparent'" style="background-color: transparent;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                PULANG
                            </div>
                        </button>
                    </form>
                @else
                    <!-- KONDISI 3: Sudah Selesai Keduanya atau Absensi di-sync oleh Izin -->
                    <button disabled class="w-full relative rounded-2xl bg-gray-300 p-1 shadow-inner cursor-not-allowed">
                        <div class="relative bg-gray-200 text-gray-500 rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Absensi Hari Ini Selesai
                        </div>
                    </button>
                @endif
                
                <div class="w-full mt-4">
                    <a href="{{ route('member.leaves.create') }}" class="w-full inline-flex justify-center items-center py-2 px-4 border rounded-xl text-sm font-semibold {{ $activeTheme['btn_secondary'] }} transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        {{ $activeTheme['leave_text'] }}
                    </a>
                </div>
                
                @if(session('success'))
                    <p class="text-sm font-medium text-emerald-600 mt-3 text-center">{{ session('success') }}</p>
                @endif
                @if(session('error'))
                    <p class="text-sm font-medium text-red-600 mt-3 text-center">{{ session('error') }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-4 text-center">Pastikan Anda berada di lokasi yang sesuai.</p>
            </div>

            <!-- History Section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Riwayat Minggu Ini</h3>
                    <a href="#" class="text-sm font-semibold {{ $activeTheme['link_color'] }}">Lihat Semua</a>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                    <div class="divide-y divide-gray-100">
                        
                        <!-- Empty State for Demo -->
                        <div class="p-6 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm">Belum ada catatan kehadiran minggu ini.</p>
                        </div>
                        
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Alpine.js Digital Clock & GPS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('digitalClock', () => ({
                timeString: '00:00:00',
                dateString: '',
                isGpsRequired: {{ auth()->user()->tenant->attendance_method === 'gps' ? 'true' : 'false' }},
                gpsError: '',
                lat: null,
                lng: null,
                isLoadingGps: false,
                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    if (this.isGpsRequired) {
                        this.isLoadingGps = true;
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.lat = position.coords.latitude;
                                    this.lng = position.coords.longitude;
                                    this.isLoadingGps = false;
                                },
                                (error) => {
                                    this.gpsError = 'Akses lokasi (GPS) wajib diaktifkan untuk melakukan absensi.';
                                    this.isLoadingGps = false;
                                },
                                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                            );
                        } else {
                            this.gpsError = 'Perangkat Anda tidak mendukung fitur GPS.';
                            this.isLoadingGps = false;
                        }
                    }
                },
                updateClock() {
                    const now = new Date();
                    this.timeString = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute:'2-digit', second:'2-digit' });
                    this.dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                }
            }));
        });
    </script>
</body>
</html>
