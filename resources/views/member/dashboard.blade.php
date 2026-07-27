<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#b91c1c">
    <link rel="manifest" href="/manifest.json">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HadirYuk') }} - Dasbor Member</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='85' font-style='italic' font-weight='900' fill='%23b91c1c' font-family='sans-serif'>H</text></svg>">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Alpine digitalClock data — HARUS di atas @@vite agar alpine:init terlebih dahulu -->
    <script>
        document.addEventListener('alpine:init', function () {
            Alpine.data('digitalClock', function () {
                return {
                    timeString: '00:00:00',
                    dateString: '',
                    endTime: null,
                    remainingTime: '',
                    isTimeToGoHome: false,
                    isGpsRequired: false,
                    gpsError: '',
                    lat: null,
                    lng: null,
                    isLoadingGps: false,
                    showCameraModal: false,
                    stream: null,
                    cameraError: '',
                    cameraStatus: 'Menyiapkan Kamera...',
                    isFallback: 0,
                    activeDay: 1,
                    async openCamera() {
                        console.log('Membuka kamera...');
                        this.showCameraModal = true;
                        this.cameraError = '';
                        this.cameraStatus = 'Menyiapkan Kamera...';
                        await this.$nextTick();
                        try {
                            const s = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                            this.stream = s;
                            if (this.$refs.videoElement) {
                                this.$refs.videoElement.srcObject = s;
                                this.$refs.videoElement.play().catch(() => {});
                            }
                            this.cameraStatus = 'Posisikan Wajah di Dalam Bingkai';
                        } catch (err) {
                            this.closeCamera(false);
                            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                this.cameraError = 'Izin kamera ditolak. Harap aktifkan akses kamera melalui setelan browser Anda.';
                            } else if (err.name === 'NotFoundError') {
                                this.cameraError = 'Perangkat kamera/webcam tidak ditemukan.';
                            } else {
                                this.cameraError = 'Gagal mengakses kamera: ' + err.message;
                            }
                        }
                    },
                    closeCamera(hideModal = true) {
                        if (hideModal) this.showCameraModal = false;
                        if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
                        if (this.$refs.videoElement) this.$refs.videoElement.srcObject = null;
                    },
                    takePhotoAndSubmit() {
                        this.cameraStatus = 'Memproses Absensi...';
                        const video = this.$refs.videoElement;
                        if (video && video.videoWidth) {
                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                            this.$refs.imageDataInput.value = canvas.toDataURL('image/jpeg', 0.75);
                        }
                        this.closeCamera();
                        this.$refs.clockInForm.submit();
                    },
                    handleFallbackUpload(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.$refs.imageDataInput.value = e.target.result;
                                this.isFallback = 1;
                                this.closeCamera();
                                this.$refs.clockInForm.submit();
                            };
                            reader.readAsDataURL(file);
                        }
                    },
                    init() {
                        this.isGpsRequired = this.$el.dataset.gpsRequired === '1';
                        const todayIso = parseInt(this.$el.dataset.todayIso || '1', 10);
                        this.activeDay = (todayIso >= 1 && todayIso <= 7) ? todayIso : 1;
                        this.endTime = this.$el.dataset.endTime ? new Date(this.$el.dataset.endTime) : null;
                        this.updateClock();
                        setInterval(() => this.updateClock(), 1000);
                        if (this.isGpsRequired) {
                            this.isLoadingGps = true;
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    (pos) => { this.lat = pos.coords.latitude; this.lng = pos.coords.longitude; this.isLoadingGps = false; },
                                    () => { this.gpsError = 'Akses lokasi (GPS) wajib diaktifkan.'; this.isLoadingGps = false; },
                                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                                );
                            } else { this.gpsError = 'Perangkat tidak mendukung GPS.'; this.isLoadingGps = false; }
                        }
                    },
                    updateClock() {
                        const now = new Date();
                        this.timeString = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        this.dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        
                        if (this.endTime) {
                            const diff = this.endTime - now;
                            if (diff > 0) {
                                const h = Math.floor(diff / 3600000);
                                const m = Math.floor((diff % 3600000) / 60000);
                                const s = Math.floor((diff % 60000) / 1000);
                                this.remainingTime = `${h}j ${m}m ${s}s`;
                                this.isTimeToGoHome = false;
                            } else {
                                this.remainingTime = 'Waktu Pulang Tiba!';
                                this.isTimeToGoHome = true;
                            }
                        }
                    }
                };
            });
        });
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-brand-bg text-brand-text-main">
    @php
        $lastEndTime = $currentSchedule ? \Carbon\Carbon::parse($currentSchedule->end_time, 'UTC')->setTimezone('Asia/Jakarta')->toIso8601String() : '';
    @endphp
    <div class="min-h-screen flex flex-col md:max-w-md md:mx-auto md:bg-brand-surface md:shadow-xl md:min-h-screen relative border-x border-brand-border"
         x-data="digitalClock()"
         data-gps-required="{{ auth()->user()->tenant->attendance_method === 'gps' ? '1' : '0' }}"
         data-today-iso="{{ $todayDayOfWeek ?? \Carbon\Carbon::now()->dayOfWeekIso }}"
         data-end-time="{{ $lastEndTime }}">
        
        <!-- Minimalist Topbar -->
        <header class="bg-brand-primary text-white p-5 rounded-b-3xl shadow-sm relative z-10">
            <div class="flex justify-between items-start">
                <div class="flex-1 pr-3">
                    <h1 class="text-xl font-bold tracking-tight">{{ Auth::user()->name }}</h1>
                    <p class="text-white/80 text-xs font-medium mt-0.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">domain</span>
                        {{ Auth::user()->tenant->name ?? 'Institusi' }}
                    </p>

                    @php
                        $userClass = Auth::user()->schoolClass;
                        $className = $userClass->nama_kelas ?? 'Belum Ada Kelas';
                        $homeroomTeacherName = ($userClass && $userClass->waliKelas) ? $userClass->waliKelas->name : 'Wali Kelas Belum Diatur';
                    @endphp

                    <div class="flex flex-wrap items-center gap-2 mt-2.5">
                        <span class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-white/25">
                            <span class="material-symbols-outlined text-[13px]">school</span>
                            {{ $className }}
                        </span>
                        <span class="inline-flex items-center gap-1 bg-white/15 backdrop-blur-sm text-white/90 text-[11px] font-medium px-2.5 py-0.5 rounded-full border border-white/20">
                            <span class="material-symbols-outlined text-[13px]">person_tie</span>
                            Wali Kelas: {{ $homeroomTeacherName }}
                        </span>
                    </div>
                </div>
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 text-white rounded-full transition border border-white/20 shrink-0">
                        <span class="material-symbols-outlined">logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-5 pt-8 pb-10 flex flex-col gap-8 -mt-6">
            
            <!-- Clock & Action Card -->
            <div class="bg-brand-surface border border-brand-border rounded-3xl p-8 shadow-sm flex flex-col items-center justify-center relative z-20">
                <p class="text-brand-text-muted font-medium text-sm mb-2" x-text="dateString"></p>
                <div class="text-5xl font-black text-brand-text-main tracking-tighter tabular-nums mb-6" x-text="timeString">
                    00:00:00
                </div>

                <!-- Info Jadwal Card -->
                @if($currentSchedule)
                    <div class="w-full bg-blue-50/50 border border-blue-100 rounded-xl p-4 mb-6 flex flex-col items-center text-center">
                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1">Jadwal Hari Ini</span>
                        <h4 class="text-sm font-semibold text-gray-800">{{ $currentSchedule->name }}</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Jam Belajar: <span class="font-medium">{{ \Carbon\Carbon::parse($currentSchedule->start_time, 'UTC')->setTimezone('Asia/Jakarta')->format('H:i') }} - {{ \Carbon\Carbon::parse($currentSchedule->end_time, 'UTC')->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                        </p>
                        <span class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-md">
                            Toleransi s/d {{ \Carbon\Carbon::parse($currentSchedule->start_time, 'UTC')->addMinutes($currentSchedule->grace_period_minutes)->setTimezone('Asia/Jakarta')->format('H:i') }}
                        </span>
                    </div>
                @else
                    <div class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6 flex flex-col items-center text-center">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Informasi</span>
                        <h4 class="text-sm font-semibold text-gray-600">Tidak Ada Jadwal Belajar Hari Ini</h4>
                    </div>
                @endif

                @if($todayLeave)
                    <!-- KONDISI 0: Izin Disetujui -->
                    <button disabled class="w-full relative rounded-2xl bg-amber-100 p-1 shadow-inner cursor-not-allowed">
                        <div class="relative bg-amber-50 text-amber-600 border border-amber-200 rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[24px]">event_busy</span>
                            Anda Hari Ini: 
                            @if($todayLeave->type === 'sick') Sakit
                            @elseif($todayLeave->type === 'permission') Izin Keperluan Pribadi
                            @elseif($todayLeave->type === 'duty_trip') Tugas Luar Kota / Dinas
                            @else Lainnya @endif
                            (Disetujui)
                        </div>
                    </button>
                @elseif(!$todayAttendance)
                    @if($currentSchedule)
                        <!-- KONDISI 1: Belum Absen -->
                        <form method="POST" action="{{ route('member.clock-in') }}" class="w-full" x-ref="clockInForm">
                            @csrf
                            <input type="hidden" name="latitude" :value="lat">
                            <input type="hidden" name="longitude" :value="lng">
                            <input type="hidden" name="image_data" x-ref="imageDataInput">
                            <input type="hidden" name="is_fallback" :value="isFallback">
                            <input type="file" accept="image/*" capture="user" x-ref="fallbackInput" class="hidden" @change="handleFallbackUpload($event)">
                            
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

                            <button type="button" @click.prevent="openCamera()"
                                    class="w-full bg-brand-primary text-white hover:bg-brand-primary/90 active:scale-95 transition-all shadow-sm rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[24px]">login</span>
                                Presensi Masuk
                            </button>
                        </form>
                    @else
                        <!-- TIDAK ADA JADWAL -->
                        <button disabled class="w-full relative rounded-2xl bg-gray-200 p-1 shadow-inner cursor-not-allowed">
                            <div class="relative bg-gray-100 text-gray-400 border border-gray-200 rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[24px] opacity-50">block</span>
                                LIBUR / OFF
                            </div>
                        </button>
                    @endif
                @elseif(!$todayAttendance?->clock_out && !in_array($todayAttendance?->status, ['sick', 'permission', 'duty_trip']))
                    <!-- KONDISI 2: Sudah Masuk, Belum Pulang -->
                    <div class="w-full flex flex-col gap-3 mb-4">
                        <div class="bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl py-3 px-4 font-bold text-sm tracking-wider flex items-center justify-center gap-2 shadow-inner">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                            STATUS: HADIR
                        </div>
                        
                        <div x-show="endTime !== null" class="w-full bg-brand-surface border border-brand-border rounded-xl p-4 shadow-sm flex flex-col items-center justify-center">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1" x-text="isTimeToGoHome ? 'Status' : 'Sisa Waktu Pulang'"></span>
                            <div class="text-2xl font-black tabular-nums tracking-wider"
                                 :class="isTimeToGoHome ? 'text-emerald-500 animate-pulse' : 'text-brand-primary'"
                                 x-text="remainingTime">
                            </div>
                        </div>
                    </div>

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
                                :class="(isGpsRequired && (!lat || isLoadingGps)) ? 'opacity-60 cursor-not-allowed' : ''"
                                class="w-full bg-rose-600 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-sm rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[24px]">logout</span>
                            PULANG
                        </button>
                    </form>
                @else
                    <!-- KONDISI 3: Sudah Selesai Keduanya atau Absensi di-sync oleh Izin -->
                    <button disabled class="w-full relative rounded-2xl bg-gray-300 p-1 shadow-inner cursor-not-allowed">
                        <div class="relative bg-gray-200 text-gray-500 rounded-xl py-4 font-bold text-lg tracking-wider flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[24px]">check_circle</span>
                            Absensi Hari Ini Selesai
                        </div>
                    </button>
                @endif
                
                <div class="w-full mt-4">
                    <a href="{{ route('member.leaves.create') }}" class="w-full inline-flex justify-center items-center py-3 border rounded-xl text-sm font-semibold bg-brand-surface border-brand-border text-brand-text-muted hover:bg-brand-primary/5 transition-colors">
                        <span class="material-symbols-outlined text-[18px] mr-2">event_note</span>
                        Ajukan Izin / Sakit
                    </a>
                </div>
                
                @if(session('success'))
                    <p class="text-sm font-medium text-emerald-600 mt-3 text-center">{{ session('success') }}</p>
                @endif
                
                @if(session('error'))
                    @if(session('error') === 'Anda tidak memiliki jadwal wajib hadir hari ini.')
                        @if(!$currentSchedule)
                            <p class="text-sm font-medium text-red-600 mt-3 text-center">{{ session('error') }}</p>
                        @endif
                    @else
                        <p class="text-sm font-medium text-red-600 mt-3 text-center">{{ session('error') }}</p>
                    @endif
                @endif
                <p class="text-xs text-brand-text-muted mt-4 text-center">Pastikan Anda berada di lokasi yang sesuai.</p>
            </div>

            <!-- Mata Pelajaran Hari Ini -->
            @if($userType === 'student' && count($todayTimetable) > 0)
            <div>
                <h3 class="text-lg font-bold text-brand-text-main mb-4"><span class="material-symbols-outlined text-red-600 align-bottom mr-1.5">auto_stories</span> Mata Pelajaran Hari Ini</h3>
                <div class="relative border-l-2 border-brand-primary/30 ml-3 pl-4 pb-2">
                    @foreach($todayTimetable as $pelajaran)
                        <div class="relative mb-5">
                            <div class="absolute -left-[23px] top-1 w-3 h-3 bg-brand-primary rounded-full ring-4 ring-brand-bg"></div>
                            <div class="bg-brand-surface border {{ $pelajaran['tipe'] === 'istirahat' ? 'border-amber-200 bg-amber-50' : ($pelajaran['tipe'] === 'upacara' ? 'border-rose-200 bg-rose-50' : 'border-brand-border') }} rounded-2xl p-3 shadow-sm">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">{{ $pelajaran['jam'] }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-800">{{ $pelajaran['waktu'] }}</span>
                                </div>
                                <h4 class="text-sm font-bold text-brand-text-main mt-1">{{ $pelajaran['mapel'] }}</h4>
                                @if($pelajaran['tipe'] === 'pelajaran')
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-brand-text-muted font-medium">
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-gray-500 text-lg align-middle mr-1">person</span>
                                            {{ $pelajaran['guru'] }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-gray-500 text-lg align-middle mr-1">meeting_room</span>
                                            {{ $pelajaran['ruang'] }}
                                        </div>
                                    </div>
                                @elseif($pelajaran['tipe'] === 'upacara')
                                    <div class="flex items-center gap-1 mt-2 text-xs text-brand-text-muted font-medium">
                                        <span class="material-symbols-outlined text-gray-500 text-lg align-middle mr-1">meeting_room</span>
                                        {{ $pelajaran['ruang'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Weekly Schedule Section -->
            @php
                $todayIso = $todayDayOfWeek ?? \Carbon\Carbon::now()->dayOfWeekIso;
                $timetableData = $weeklyTimetable ?? $dummyTimetable ?? [];
                $hasWeeklySchedule = false;
                foreach ($timetableData as $dayItems) {
                    if (!empty($dayItems)) {
                        $hasWeeklySchedule = true;
                        break;
                    }
                }
            @endphp
            <div>
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-brand-text-main">
                        {{ $userType === 'student' ? 'Jadwal Pelajaran Mingguan' : 'Jadwal Tugas & Tanggung Jawab Mingguan' }}
                    </h3>
                </div>

                @if($hasWeeklySchedule)
                    <div class="bg-brand-surface rounded-2xl border border-brand-border overflow-hidden shadow-sm p-1">
                        <div class="flex flex-col gap-1">
                            @php
                                $days = [
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                    7 => 'Minggu',
                                ];
                            @endphp
                            
                            @foreach($days as $num => $dayName)
                                @php
                                    $isToday = ($num === $todayIso);
                                    $dayBreakdown = $timetableData[$num] ?? [];
                                @endphp
                                
                                <div class="rounded-xl border transition-colors"
                                     :class="activeDay === {{ $num }} ? 'border-brand-primary/30 bg-brand-primary/5' : 'border-transparent hover:bg-gray-50'">
                                    <button @click="activeDay = activeDay === {{ $num }} ? null : {{ $num }}" class="w-full text-left p-3 flex items-start justify-between outline-none">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 shrink-0 flex flex-col items-center justify-center rounded-lg"
                                                 :class="activeDay === {{ $num }} ? 'bg-brand-primary text-white' : 'bg-gray-100 text-brand-text-muted'">
                                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ substr($dayName, 0, 3) }}</span>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold" :class="activeDay === {{ $num }} ? 'text-brand-primary' : 'text-brand-text-main'">{{ $dayName }}</h4>
                                                <p class="text-xs text-brand-text-muted mt-0.5">
                                                    @if(count($dayBreakdown) > 0)
                                                        {{ count($dayBreakdown) }} Sesi Kegiatan
                                                    @else
                                                        Libur / Bebas KBM
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($isToday)
                                                <span class="px-2 py-1 bg-brand-primary text-white text-[9px] font-bold uppercase tracking-wider rounded-md shadow-sm shrink-0">Hari Ini</span>
                                            @endif
                                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="activeDay === {{ $num }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </button>
                                    
                                    <div x-show="activeDay === {{ $num }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
                                        <div class="px-3 pb-3 pt-1">
                                            @if(count($dayBreakdown) > 0)
                                                <div class="space-y-2">
                                                    @foreach($dayBreakdown as $b)
                                                        <div class="flex items-start gap-2 bg-white border border-gray-100 rounded-lg p-2 shadow-sm">
                                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800 whitespace-nowrap">{{ $b['jam'] }}</span>
                                                            <div>
                                                                <p class="text-xs font-bold text-gray-800">{{ $b['mapel'] }}</p>
                                                                <p class="text-[10px] text-gray-500 font-medium">{{ $b['waktu'] }} &bull; {{ $b['guru'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-4 bg-gray-50/50 rounded-lg border border-dashed border-gray-200">
                                                    <p class="text-xs text-gray-400 font-medium italic">🎉 Hari Libur / Tidak ada KBM</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-brand-surface border border-brand-border rounded-2xl p-6 text-center text-gray-500 shadow-sm">
                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">calendar_today</span>
                        <p class="text-sm font-medium text-gray-600">Belum ada jadwal pelajaran mingguan yang diatur.</p>
                    </div>
                @endif
            </div>

            <!-- History Section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-brand-text-main">Riwayat Minggu Ini</h3>
                    <a href="#" class="text-sm font-semibold text-brand-primary hover:underline">Lihat Semua</a>
                </div>

                <div class="bg-brand-surface rounded-2xl border border-brand-border overflow-hidden shadow-sm">
                    <div class="divide-y divide-brand-border">
                        @forelse($weeklyAttendances as $att)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Masuk: {{ $att->clock_in ? substr($att->clock_in, 0, 5) : '-' }} &bull; Pulang: {{ $att->clock_out ? substr($att->clock_out, 0, 5) : '-' }}</p>
                                </div>
                                <div>
                                    @if($att->status === 'present')
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-md">Tepat Waktu</span>
                                    @elseif($att->status === 'late')
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md">Terlambat</span>
                                    @elseif($att->status === 'absent')
                                        <span class="px-2 py-1 bg-rose-100 text-rose-700 text-[10px] font-bold rounded-md">Alpa</span>
                                    @elseif($att->status === 'sick')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-md">Sakit</span>
                                    @elseif($att->status === 'permission')
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-md">Izin</span>
                                    @elseif($att->status === 'duty_trip')
                                        <span class="px-2 py-1 bg-purple-100 text-purple-700 text-[10px] font-bold rounded-md">Tugas Luar</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-[10px] font-bold rounded-md">{{ ucfirst($att->status) }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <!-- Empty State -->
                            <div class="p-6 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm">Belum ada catatan kehadiran minggu ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Modal Kamera Liveness Check -->
            <div x-show="showCameraModal" style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-3xl overflow-hidden w-full max-w-sm shadow-2xl relative" @click.outside="closeCamera()">
                    <!-- Header -->
                    <div class="bg-brand-primary p-4 text-center">
                        <h3 class="text-white font-bold text-lg">Verifikasi Wajah (Liveness)</h3>
                        <p class="text-white/80 text-xs mt-1">Pastikan wajah Anda terlihat jelas dalam bingkai</p>
                    </div>
                    
                    <!-- Camera Preview -->
                    <div class="relative w-full bg-black overflow-hidden" style="height: 320px;">

                        {{-- Spinner: kamera sedang dimuat --}}
                        <div x-show="!stream && !cameraError"
                             class="absolute inset-0 flex flex-col items-center justify-center text-white/60 z-10">
                            <svg class="animate-spin h-10 w-10 mb-3 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-xs font-semibold">Membuka kamera...</p>
                        </div>

                        {{-- Error State --}}
                        <div x-show="cameraError"
                             class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center z-20 bg-black">
                            <span class="material-symbols-outlined text-rose-400 text-5xl mb-3">videocam_off</span>
                            <p class="text-rose-400 text-sm font-semibold mb-4" x-text="cameraError"></p>
                            <div class="flex flex-col gap-2 w-full">
                                <button type="button" @click="$refs.fallbackInput.click()"
                                        class="px-4 py-2 w-full bg-brand-primary text-white text-xs font-bold rounded-full">
                                    Gunakan Upload Foto Manual
                                </button>
                                <button type="button" @click="closeCamera()"
                                        class="px-4 py-2 bg-white/10 text-white text-xs font-bold rounded-full w-full border border-white/20">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        {{-- Video Stream --}}
                        <video x-ref="videoElement"
                               autoplay
                               playsinline
                               muted
                               class="absolute inset-0 w-full h-full object-cover"
                               x-show="stream && !cameraError">
                        </video>

                        {{-- Oval Face Guide Overlay --}}
                        <div x-show="stream && !cameraError"
                             class="absolute inset-0 z-10 pointer-events-none flex items-center justify-center">
                            {{-- Dark vignette mask --}}
                            <div class="absolute inset-0 bg-black/40"></div>
                            {{-- Oval cutout using box-shadow technique --}}
                            <div class="relative w-44 h-56 rounded-[50%]"
                                 style="box-shadow: 0 0 0 999px rgba(0,0,0,0.45); border: 2.5px dashed rgba(255,255,255,0.75);">
                            </div>
                            {{-- Instruction label --}}
                            <p class="absolute bottom-5 left-0 right-0 text-center text-white/90 text-[11px] font-bold tracking-widest uppercase drop-shadow">
                                Posisikan Wajah Anda di Dalam Oval
                            </p>
                        </div>

                    </div>

                    
                    <!-- Instruction & Action -->
                    <div class="p-5 flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-primary animate-pulse">face</span>
                            <span x-text="cameraStatus"></span>
                        </p>
                        <div class="flex items-center gap-3 w-full">
                            <button type="button" @click="closeCamera()" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Batal</button>
                            <button type="button" @click="takePhotoAndSubmit()" class="flex-[2] py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 transition-colors">
                                <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                                Ambil & Absen
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <x-footer class="mt-8 bg-transparent border-t-0 text-gray-400" />
        </main>
    </div>

    @include('partials.pwa-prompt')
</body>
</html>
