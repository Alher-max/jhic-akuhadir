<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#b91c1c">
    <link rel="manifest" href="/manifest.json">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">

    <title>{{ config('app.name', 'HadirYuk') }} - Portal Orang Tua</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='85' font-style='italic' font-weight='900' fill='%23b91c1c' font-family='sans-serif'>H</text></svg>">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-brand-bg text-brand-text-main">
    <div class="min-h-screen flex flex-col md:max-w-md md:mx-auto md:bg-brand-surface md:shadow-xl md:min-h-screen relative border-x border-brand-border"
        x-data="{ activeChild: 0 }">

        @include('partials.pwa-prompt')

        <!-- Minimalist Topbar -->
        <header class="bg-brand-primary text-white p-5 rounded-b-3xl shadow-sm relative z-10">
            <div class="flex justify-between items-start">
                <div class="flex-1 pr-3">
                    <h1 class="text-xl font-bold tracking-tight">{{ Auth::user()->name }}</h1>
                    <p class="text-white/80 text-xs font-medium mt-0.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">family_restroom</span>
                        Portal Orang Tua
                    </p>

                    <div class="flex flex-wrap items-center gap-2 mt-2.5">
                        <span
                            class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-white/25">
                            <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}
                        </span>
                    </div>
                </div>
                <!-- Header Action Buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="$dispatch('open-change-password-modal')" title="Ganti Password"
                        class="w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 text-white rounded-full transition border border-white/20">
                        <span class="material-symbols-outlined text-[20px]">key</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar"
                            class="w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 text-white rounded-full transition border border-white/20">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mb-6 flex-1 px-5 pt-8 pb-36 md:pb-16 flex flex-col gap-6 -mt-6">

            <!-- PWA Push Notification Prompt -->
            <div x-data="{
                    showPrompt: false,
                    isSupported: window.PushManager && window.PushManager.isSupported(),
                    async init() {
                        // Delay check to avoid blocking main thread
                        setTimeout(() => {
                            if (this.isSupported && Notification.permission === 'default') {
                                this.showPrompt = true;
                            }
                        }, 2000);
                    },
                    async enableNotifications() {
                        try {
                            const permission = await Notification.requestPermission();
                            if (permission === 'granted') {
                                await window.PushManager.subscribe();
                                this.showPrompt = false;
                                alert('Notifikasi berhasil diaktifkan!');
                            }
                        } catch (error) {
                            console.error(error);
                            alert('Gagal mengaktifkan notifikasi.');
                        }
                    }
                }" x-show="showPrompt" x-cloak
                class="bg-brand-primary/5 border border-brand-primary/20 rounded-3xl p-5 shadow-sm mb-2 flex flex-col gap-4">
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-3xl">notifications_active</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Aktifkan Notifikasi</h4>
                        <p class="text-xs text-gray-600 mt-1">
                            Terima pengumuman penting wali kelas putra-putri Anda langsung di HP secara real-time.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="enableNotifications()"
                        class="flex-1 bg-brand-primary text-white text-xs font-bold py-3 rounded-2xl shadow-sm hover:bg-brand-primary/90 transition">
                        Ya, Aktifkan
                    </button>
                    <button @click="showPrompt = false"
                        class="px-5 py-3 text-gray-500 text-xs font-bold rounded-2xl hover:bg-gray-100 transition">
                        Nanti
                    </button>
                </div>
            </div>

            <!-- Welcome Widget -->
            <div
                class="w-full bg-brand-surface border border-brand-border rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">waving_hand</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-900">Selamat Datang, {{ Auth::user()->greeting_name }}
                        </h4>
                        <p class="text-[11px] text-gray-600">Pantau kehadiran putra-putri Anda hari ini.</p>
                    </div>
                </div>
            </div>

            <!-- Children Status Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-brand-text-main uppercase tracking-wider">Status Kehadiran Siswa
                    </h3>
                    <span
                        class="text-[10px] font-bold text-brand-primary bg-brand-primary/10 px-2 py-0.5 rounded-full">{{ count($childrenAttendances) }}
                        Anak</span>
                </div>

                @forelse($childrenAttendances as $index => $item)
                    <div
                        class="bg-brand-surface border border-brand-border rounded-3xl overflow-hidden shadow-sm transition-all">
                        <!-- Child Header -->
                        <div class="p-4 bg-gray-50/50 border-b border-brand-border flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ substr($item->child->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-brand-text-main">{{ $item->child->name }}</h4>
                                    <p class="text-[10px] text-brand-text-muted font-medium">NISN:
                                        {{ $item->child->nisn ?? '-' }} •
                                        {{ $item->child->schoolClass->nama_kelas ?? 'Tanpa Kelas' }}
                                    </p>
                                </div>
                            </div>

                            @if($item->attendance)
                                @if($item->attendance->status === 'present')
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <span class="material-symbols-outlined text-sm">check_circle</span> HADIR
                                    </span>
                                @elseif($item->attendance->status === 'late')
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        <span class="material-symbols-outlined text-sm">schedule</span> TERLAMBAT
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        <span class="material-symbols-outlined text-sm">cancel</span>
                                        {{ strtoupper($item->attendance->status) }}
                                    </span>
                                @endif
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                    <span class="material-symbols-outlined text-sm">pending</span> BELUM ABSEN
                                </span>
                            @endif
                        </div>

                        <!-- Attendance Details -->
                        <div class="p-4 grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-2xl p-3 border border-gray-100">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Jam
                                    Masuk</span>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-emerald-500 text-lg">login</span>
                                    <span class="text-sm font-black text-gray-700">
                                        {{ $item->attendance && $item->attendance->clock_in ? \Carbon\Carbon::parse($item->attendance->clock_in)->format('H:i') . ' WIB' : '--:--' }}
                                    </span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-3 border border-gray-100">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Jam
                                    Pulang</span>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-rose-500 text-lg">logout</span>
                                    <span class="text-sm font-black text-gray-700">
                                        {{ $item->attendance && $item->attendance->clock_out ? \Carbon\Carbon::parse($item->attendance->clock_out)->format('H:i') . ' WIB' : '--:--' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-brand-border px-4 py-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold text-gray-700">Riwayat Minggu Ini</span>
                                <span class="text-[10px] text-gray-400">{{ $item->weeklyAttendances->count() }} catatan</span>
                            </div>
                            @forelse($item->weeklyAttendances as $weeklyAttendance)
                                <div class="flex items-center justify-between py-1 text-[10px]">
                                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($weeklyAttendance->date)->translatedFormat('D, d M') }}</span>
                                    <span class="font-semibold text-gray-700">{{ strtoupper($weeklyAttendance->status) }}</span>
                                </div>
                            @empty
                                <p class="text-[10px] text-gray-400">Belum ada catatan kehadiran minggu ini.</p>
                            @endforelse
                        </div>

                        <!-- Pengumuman & Agenda -->
                        <div class="border-t border-brand-border">
                            <!-- Announcements Section -->
                            <div x-data="{ open: true }">
                                <button @click="open = !open"
                                    class="w-full px-4 py-3 flex items-center justify-between text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-lg text-brand-primary">campaign</span>
                                        📢 Pengumuman Kelas ({{ isset($item->announcements) ? $item->announcements->count() : 0 }})
                                    </div>
                                    <span class="material-symbols-outlined transition-transform"
                                        :class="open ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                <div x-show="open" x-collapse class="px-4 pb-4">
                                    @if(isset($item->announcements) && $item->announcements->count() > 0)
                                        <div class="space-y-2 pt-1">
                                            @foreach($item->announcements as $announcement)
                                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-2.5">
                                                    <h5 class="font-bold text-xs text-blue-700">{{ $announcement->title }}</h5>
                                                    <p class="text-[10px] text-blue-600/80 mt-0.5">{{ $announcement->description }}</p>
                                                    <span
                                                        class="text-[9px] text-blue-400 mt-1 block">{{ $announcement->created_at?->diffForHumans() ?? '-' }}</span>
                                                    @if($announcement->attachment_path)
                                                        <div class="mt-1">
                                                            <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank"
                                                                class="inline-flex items-center gap-1 text-[10px] text-blue-700 font-semibold hover:underline">
                                                                <span class="material-symbols-outlined text-xs">attachment</span>
                                                                <span>Lihat Lampiran</span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                            <p class="text-[10px] text-gray-400 font-medium italic">Tidak ada pengumuman hari ini.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Agenda Section -->
                            <div x-data="{ open: true }">
                                <button @click="open = !open"
                                    class="w-full px-4 py-3 flex items-center justify-between text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors border-t border-brand-border">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-lg text-brand-primary">auto_stories</span>
                                        📅 Agenda & Jadwal Hari Ini ({{ count($item->todayAgenda) }})
                                    </div>
                                    <span class="material-symbols-outlined transition-transform"
                                        :class="open ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                <div x-show="open" x-collapse class="px-4 pb-4">
                                    @forelse($item->todayAgenda as $ag)
                                        <div
                                            class="flex items-center justify-between p-2.5 bg-white border border-gray-100 rounded-xl shadow-xs mt-2">
                                            <div class="flex items-center gap-2.5">
                                                <span
                                                    class="px-2 py-0.5 rounded-md font-extrabold text-[9px] border {{ $ag->badge_class }}">
                                                    {{ $ag->badge }}
                                                </span>
                                                <div>
                                                    <span class="font-bold text-gray-800 text-xs block">{{ $ag->title }}</span>
                                                    <span class="text-[10px] text-gray-500 font-medium">{{ $ag->subtitle }}</span>
                                                </div>
                                            </div>
                                            <span
                                                class="font-bold text-[10px] text-brand-primary bg-brand-primary/5 px-2 py-1 rounded-lg border border-brand-primary/10 shrink-0">
                                                {{ $ag->time_str }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 bg-gray-50 rounded-xl border border-dashed border-gray-200 mt-2">
                                            <p class="text-[10px] text-gray-400 font-medium italic">Tidak ada jadwal KBM atau
                                                kegiatan hari ini.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-brand-surface border border-brand-border rounded-3xl p-8 text-center shadow-sm">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">child_care</span>
                        <p class="text-sm font-bold text-gray-600">Belum Ada Data Siswa</p>
                        <p class="text-xs text-gray-400 mt-1">Silakan hubungi admin sekolah untuk menautkan akun anak Anda.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Quick Actions -->
            @if(count($childrenAttendances) > 0)
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-brand-text-main uppercase tracking-wider">Aksi Cepat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('member.leaves.create') }}"
                            class="flex flex-col items-center justify-center p-4 bg-white border border-brand-border rounded-2xl shadow-xs hover:bg-brand-primary/5 transition-colors group">
                            <div
                                class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined">event_busy</span>
                            </div>
                            <span class="text-[11px] font-bold text-gray-700">Ajukan Izin</span>
                        </a>
                        <a href="#"
                            class="flex flex-col items-center justify-center p-4 bg-white border border-brand-border rounded-2xl shadow-xs hover:bg-brand-primary/5 transition-colors group">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined">history</span>
                            </div>
                            <span class="text-[11px] font-bold text-gray-700">Riwayat</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Info Widget -->
            <div
                class="w-full bg-blue-50/80 border border-blue-200/80 rounded-2xl p-4 shadow-sm flex items-center gap-3 text-blue-900">
                <div
                    class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">info</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">Informasi Real-time</h4>
                    <p class="text-[11px] text-gray-600">Data kehadiran diperbarui secara otomatis saat siswa melakukan
                        absensi di sekolah.</p>
                </div>
            </div>

            <!-- Modal Ganti Password (Reuse from student) -->
            <div x-data="{ showChangePasswordModal: false, showCurrent: false, showNew: false, showConfirm: false }"
                @open-change-password-modal.window="showChangePasswordModal = true" x-show="showChangePasswordModal"
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div class="bg-white rounded-3xl overflow-hidden w-full max-w-sm shadow-2xl relative p-6 text-left"
                    @click.outside="showChangePasswordModal = false">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">lock_reset</span>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Ganti Password Akun</h3>
                        </div>
                        <button type="button" @click="showChangePasswordModal = false"
                            class="text-gray-400 hover:text-gray-600 p-1">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('put')

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Password Saat Ini</label>
                            <div class="relative flex items-center">
                                <input :type="showCurrent ? 'text' : 'password'" name="current_password" required
                                    placeholder="Masukkan password lama..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-brand-primary focus:border-brand-primary">
                                <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute right-3 text-gray-400 hover:text-gray-600">
                                    <span class="material-symbols-outlined text-base"
                                        x-text="showCurrent ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Password Baru</label>
                            <div class="relative flex items-center">
                                <input :type="showNew ? 'text' : 'password'" name="password" required
                                    placeholder="Minimal 8 karakter..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-brand-primary focus:border-brand-primary">
                                <button type="button" @click="showNew = !showNew"
                                    class="absolute right-3 text-gray-400 hover:text-gray-600">
                                    <span class="material-symbols-outlined text-base"
                                        x-text="showNew ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password
                                Baru</label>
                            <div class="relative flex items-center">
                                <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                    placeholder="Ulangi password baru..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:ring-brand-primary focus:border-brand-primary">
                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 text-gray-400 hover:text-gray-600">
                                    <span class="material-symbols-outlined text-base"
                                        x-text="showConfirm ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="showChangePasswordModal = false"
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                Simpan Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <x-footer class="mt-8 bg-transparent border-t-0 text-gray-400" />
        </main>
    </div>

@include('partials.demo-role-switcher')
</body>

</html>