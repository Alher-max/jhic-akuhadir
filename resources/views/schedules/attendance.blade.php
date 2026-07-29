<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-brand-primary"></i>
            {{ __('Pengaturan Alat & Lainnya') }}
        </h2>
    </x-slot>

    @php
        $rawMode = $tenant->attendance_mode ?? 'daily_arrival';
        $initMode = ($rawMode === 'non_formal_session' || $rawMode === 'session_based') ? 'session_based' : 'daily_arrival';
    @endphp

    <div class="py-10 bg-brand-bg min-h-screen" x-data="{
        attendanceMode: '{{ $initMode }}',
        sessionTolerance: {{ $tenant->session_late_tolerance_minutes ?? 10 }}
    }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Bar Menu Tab Navigasi Pengaturan Presensi -->
            <div class="flex items-center gap-2 p-1.5 bg-brand-surface rounded-2xl border border-brand-border shadow-xs w-full sm:w-auto self-start">
                <a href="{{ route('attendance-settings.index') }}"
                   class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 {{ request()->routeIs('attendance-settings.*') ? 'bg-brand-primary text-white font-medium shadow-sm' : 'bg-brand-surface border border-brand-border text-brand-text-muted hover:bg-brand-primary/5' }}">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>Alat</span>
                </a>
                <a href="{{ route('attendance-schedules.index') }}"
                   class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 {{ request()->routeIs('attendance-schedules.*') ? 'bg-brand-primary text-white font-medium shadow-sm' : 'bg-brand-surface border border-brand-border text-brand-text-muted hover:bg-brand-primary/5' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Keterlambatan</span>
                </a>
                <a href="{{ route('student-cards.index') }}"
                   class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 {{ request()->routeIs('student-cards.*') ? 'bg-brand-primary text-white font-medium shadow-sm' : 'bg-brand-surface border border-brand-border text-brand-text-muted hover:bg-brand-primary/5' }}">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Kartu</span>
                </a>
            </div>

            <!-- Form Jam & Mode Operasional -->
            <form action="{{ route('attendance-schedules.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- SECTION 1: SELEKSI MODE PRESENSI -->
                <div class="bg-brand-surface p-6 rounded-2xl border border-brand-border shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4">
                        <div>
                            <h4 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-indigo-600"></i> Mode Presensi Lembaga
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Konfigurasi mode presensi sekolah, aturan batas keterlambatan, dan jam kerja operasional harian (Khusus Operator Sekolah).
                            </p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5 self-start sm:self-auto shrink-0">
                            <i class="fa-solid fa-user-shield"></i> Pengaturan Operator Sekolah
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Option 1: Mode Kedatangan Harian -->
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                            :class="attendanceMode === 'daily_arrival' ? 'border-brand-primary bg-red-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="attendance_mode" value="daily_arrival" x-model="attendanceMode" class="text-brand-primary focus:ring-brand-primary h-4 w-4">
                                    <div>
                                        <h5 class="font-bold text-sm text-gray-900">Mode Kedatangan Harian</h5>
                                        <p class="text-xs text-gray-500">Presensi Kedatangan Sekolah (Jam Masuk Terpusat)</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800" x-show="attendanceMode === 'daily_arrival'">
                                    Aktif
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-3 pt-3 border-t border-gray-100">
                                Siswa presensi masuk sekolah berdasarkan jam operasional harian terpusat (misal 07:00).
                            </p>
                        </label>

                        <!-- Option 2: Mode Per Sesi / Jam Pembelajaran -->
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all"
                            :class="attendanceMode === 'session_based' ? 'border-indigo-600 bg-indigo-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="attendance_mode" value="session_based" x-model="attendanceMode" class="text-indigo-600 focus:ring-indigo-600 h-4 w-4">
                                    <div>
                                        <h5 class="font-bold text-sm text-gray-900">Mode Per Sesi / Jam Pembelajaran</h5>
                                        <p class="text-xs text-gray-500">Presensi Berbasis Sesi Kelas / Mata Pelajaran</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-indigo-100 text-indigo-800" x-show="attendanceMode === 'session_based'">
                                    Aktif
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-3 pt-3 border-t border-gray-100">
                                Siswa presensi sekolah & kelas berdasarkan jam mulai sesi/jam pembelajaran KBM.
                            </p>
                        </label>
                    </div>

                    <!-- Input Khusus Toleransi Sesi Pembelajaran (Jika Mode Per Sesi Aktif) -->
                    <div x-show="attendanceMode === 'session_based'" x-transition class="p-4 bg-indigo-50/60 border border-indigo-200 rounded-xl space-y-3">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-indigo-600 text-lg shrink-0 mt-0.5"></i>
                            <div>
                                <h6 class="font-bold text-xs text-indigo-950 uppercase tracking-wider">Mode Per Sesi Aktif</h6>
                                <p class="text-xs text-indigo-900 mt-0.5 leading-relaxed">
                                    Aturan keterlambatan akan dihitung secara otomatis berdasarkan Jam Mulai Sesi KBM pada Jadwal Pelajaran. Pengaturan jam harian 7 hari di bawah ini dinonaktifkan dan hanya digunakan saat Mode Kedatangan Harian aktif.
                                </p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-indigo-100 max-w-sm">
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Toleransi Keterlambatan Sesi (Menit) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <input type="number" name="session_late_tolerance_minutes" x-model="sessionTolerance" min="0" max="180" class="w-full border-gray-300 rounded-xl shadow-xs text-sm font-bold text-gray-800 focus:ring-indigo-600 focus:border-indigo-600 pl-3 pr-16 py-2">
                                <span class="absolute right-3 text-xs font-semibold text-gray-400 pointer-events-none">
                                    Menit
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: 7 KARTU JAM HARIAN -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-brand-primary"></i> Jam Operasional Harian (Senin – Minggu)
                        </h4>
                        <span class="text-xs text-gray-500 font-medium" x-show="attendanceMode === 'session_based'">
                            (Status: Mode Per Sesi Aktif)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 transition-all duration-300"
                        :class="attendanceMode === 'session_based' ? 'opacity-40 pointer-events-none grayscale-[50%]' : ''">
                        @foreach($schedules as $index => $schedule)
                            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm p-5 flex flex-col justify-between space-y-4 transition hover:border-gray-300 {{ $loop->last ? 'md:col-span-2 lg:col-span-2' : '' }}">
                                <input type="hidden" name="schedules[{{ $index }}][id]" value="{{ $schedule->id }}">
                                
                                <!-- Card Header: Day Name & Toggle -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full {{ $schedule->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                        <h4 class="font-bold text-base text-gray-800">{{ $schedule->day_name }}</h4>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="schedules[{{ $index }}][is_active]" value="0">
                                        <input type="checkbox" name="schedules[{{ $index }}][is_active]" value="1" {{ $schedule->is_active ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 relative"></div>
                                        <span class="ms-2 text-xs font-semibold text-gray-600">
                                            {{ $schedule->is_active ? 'Aktif' : 'Libur' }}
                                        </span>
                                    </label>
                                </div>

                                <!-- Card Inputs -->
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                                <i class="fa-regular fa-clock text-emerald-600 me-1"></i> Jam Masuk
                                            </label>
                                            <input type="time" name="schedules[{{ $index }}][time_in]" value="{{ \Carbon\Carbon::parse($schedule->time_in)->format('H:i') }}" required class="w-full border-gray-300 rounded-xl shadow-xs text-sm font-semibold text-gray-800 focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                                <i class="fa-regular fa-clock text-indigo-600 me-1"></i> Jam Pulang
                                            </label>
                                            <input type="time" name="schedules[{{ $index }}][time_out]" value="{{ \Carbon\Carbon::parse($schedule->time_out)->format('H:i') }}" required class="w-full border-gray-300 rounded-xl shadow-xs text-sm font-semibold text-gray-800 focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                            <i class="fa-solid fa-stopwatch text-amber-500 me-1"></i> Toleransi Keterlambatan
                                        </label>
                                        <div class="relative flex items-center">
                                            <input type="number" name="schedules[{{ $index }}][late_tolerance_minutes]" value="{{ $schedule->late_tolerance_minutes }}" min="0" max="180" required class="w-full border-gray-300 rounded-xl shadow-xs text-sm font-semibold text-gray-800 focus:ring-brand-primary focus:border-brand-primary pl-3 pr-16 py-2">
                                            <span class="absolute right-3 text-xs font-semibold text-gray-400 pointer-events-none">
                                                Menit
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Floating Bottom Submit Bar -->
                <div class="bg-brand-surface p-4 rounded-2xl border border-brand-border shadow-md flex items-center justify-between mt-6">
                    <p class="text-xs text-gray-500 font-medium hidden sm:block">
                        <i class="fa-solid fa-circle-info text-blue-500 me-1"></i> Pengaturan disimpan langsung oleh Operator Sekolah.
                    </p>
                    <button type="submit" class="px-6 py-2.5 bg-brand-primary hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow-md transition inline-flex items-center gap-2 ml-auto">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Jam & Mode Operasional
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
