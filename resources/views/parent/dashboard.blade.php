<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-brand-primary/10 rounded-xl">
                <i class="fa-solid fa-users-rays text-brand-primary text-xl"></i>
            </div>
            <div>
                <h2 class="font-bold text-xl text-brand-text-main leading-tight">
                    {{ __('Portal Orang Tua') }}
                </h2>
                <p class="text-sm text-brand-text-muted">Pantau kehadiran putra-putri Anda secara real-time</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-brand-primary to-[#8A151A] rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Selamat datang, Bapak/Ibu {{ $parent->name }}</h3>
                        <p class="text-white/80">Pantau disiplin dan aktivitas kehadiran siswa dengan mudah dan transparan.</p>
                    </div>
                    <div class="shrink-0 bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-center">
                        <div class="text-3xl font-black">{{ \Carbon\Carbon::now()->format('d M') }}</div>
                        <div class="text-sm font-medium text-white/80 uppercase tracking-widest">{{ \Carbon\Carbon::now()->translatedFormat('l') }}</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mb-2 px-2">
                <i class="fa-solid fa-graduation-cap text-brand-primary"></i>
                <h3 class="font-bold text-lg text-brand-text-main">Status Kehadiran Hari Ini</h3>
            </div>

            <!-- Children Status Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($childrenAttendances as $item)
                    <div class="bg-brand-surface rounded-2xl border border-brand-border p-6 shadow-sm hover:shadow-md transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center font-bold text-lg">
                                    {{ substr($item->child->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-brand-text-main text-lg">{{ $item->child->name }}</h4>
                                    <p class="text-sm text-brand-text-muted">NISN: {{ $item->child->nisn ?? '-' }}</p>
                                </div>
                            </div>
                            
                            @if($item->attendance)
                                @if($item->attendance->status === 'present')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <i class="fa-solid fa-check-circle"></i> Hadir Tepat Waktu
                                    </span>
                                @elseif($item->attendance->status === 'late')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <i class="fa-solid fa-clock"></i> Terlambat
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                        <i class="fa-solid fa-xmark-circle"></i> Tidak Hadir / {{ ucfirst($item->attendance->status) }}
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                    <i class="fa-solid fa-minus-circle"></i> Belum Absen
                                </span>
                            @endif
                        </div>

                        <div class="border-t border-brand-border/50 pt-4 mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-brand-text-muted mb-1 uppercase tracking-wider font-semibold">Jam Masuk</p>
                                <p class="font-medium text-brand-text-main">
                                    @if($item->attendance && $item->attendance->clock_in_time)
                                        {{ \Carbon\Carbon::parse($item->attendance->clock_in_time)->format('H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-brand-text-muted mb-1 uppercase tracking-wider font-semibold">Jam Pulang</p>
                                <p class="font-medium text-brand-text-main">
                                    @if($item->attendance && $item->attendance->clock_out_time)
                                        {{ \Carbon\Carbon::parse($item->attendance->clock_out_time)->format('H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-brand-surface rounded-2xl border border-brand-border p-8 text-center text-brand-text-muted">
                        <i class="fa-solid fa-child-reaching text-4xl mb-4 text-brand-border"></i>
                        <p>Belum ada data siswa yang tertaut ke akun Anda.</p>
                        <p class="text-sm mt-2">Silakan hubungi Admin Sekolah untuk menautkan akun anak Anda.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Quick Actions for Parents -->
            @if(count($childrenAttendances) > 0)
            <div class="mt-8 bg-brand-surface border border-brand-border rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-lg text-brand-text-main mb-4"><i class="fa-solid fa-clipboard-list text-brand-primary mr-2"></i> Pengajuan Orang Tua</h3>
                <p class="text-brand-text-muted mb-4">Ajukan surat izin atau sakit untuk anak Anda dengan mudah tanpa perlu surat fisik.</p>
                <div class="flex gap-4">
                    <button class="bg-brand-bg border border-brand-border text-brand-text-main font-semibold px-4 py-2 rounded-lg hover:bg-brand-primary hover:text-white hover:border-brand-primary transition-colors">
                        <i class="fa-solid fa-file-medical mr-2"></i> Ajukan Izin Sakit
                    </button>
                    <button class="bg-brand-bg border border-brand-border text-brand-text-main font-semibold px-4 py-2 rounded-lg hover:bg-brand-primary hover:text-white hover:border-brand-primary transition-colors">
                        <i class="fa-solid fa-file-signature mr-2"></i> Ajukan Izin Pribadi
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
