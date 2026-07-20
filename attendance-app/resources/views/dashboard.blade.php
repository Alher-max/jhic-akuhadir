<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->tenant)
            <div class="mb-8 rounded-xl shadow-lg bg-gradient-to-r from-indigo-600 to-purple-600 p-6 md:p-8 text-white relative overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10"></div>
                <div class="absolute bottom-0 right-1/4 -mb-12 w-32 h-32 rounded-full bg-white opacity-10"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-3xl font-extrabold tracking-tight mb-2">
                            Selamat datang, <br class="hidden md:block" />{{ auth()->user()->tenant->name }}!
                        </h2>
                        <p class="text-indigo-100 max-w-xl text-lg">
                            Kelola pendaftaran karyawan atau siswa Anda. Bagikan kode unik institusi di bawah agar mereka dapat bergabung dengan mudah.
                        </p>
                    </div>
                    
                    <div class="flex flex-col gap-3">
                        <div class="bg-white/20 backdrop-blur-md rounded-lg p-5 text-center min-w-[200px] border border-white/30 shadow-inner">
                            <p class="text-indigo-100 text-sm font-semibold uppercase tracking-wider mb-1">Kode Institusi</p>
                            <p class="text-4xl font-black tracking-widest select-all">
                                {{ auth()->user()->tenant->code }}
                            </p>
                        </div>
                        <a href="{{ route('schedules.index') }}" class="block w-full py-3 px-4 bg-white text-indigo-700 text-center rounded-lg font-bold shadow hover:bg-indigo-50 transition-colors">
                            Kelola Jadwal
                        </a>
                        
                        @if(in_array(auth()->user()->role, ['owner', 'manager_teacher']))
                        <a href="{{ route('admin.leaves.index') }}" class="block w-full py-3 px-4 bg-white text-indigo-700 text-center rounded-lg font-bold shadow hover:bg-indigo-50 transition-colors relative">
                            Kelola Izin & Sakit
                            @if($pendingLeavesCount > 0)
                                <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-md animate-bounce">
                                    {{ $pendingLeavesCount }} Perlu Diproses
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="block w-full py-3 px-4 bg-white text-indigo-700 text-center rounded-lg font-bold shadow hover:bg-indigo-50 transition-colors">
                            Laporan Kehadiran
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Anggota -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Anggota</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalMembers }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>

                <!-- Sudah Hadir -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Sudah Hadir Hari Ini</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $presentToday }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-lg">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Belum Absen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Belum Absen</p>
                        <p class="text-3xl font-bold text-rose-500 mt-1">{{ $absentToday }}</p>
                    </div>
                    <div class="p-3 bg-rose-50 rounded-lg">
                        <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900">Log Kehadiran Hari Ini</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-medium">Nama Anggota</th>
                                <th class="px-6 py-4 font-medium">Email</th>
                                <th class="px-6 py-4 font-medium">Jam Masuk</th>
                                <th class="px-6 py-4 font-medium">Jam Pulang</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($attendances as $attendance)
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $attendance->user->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $attendance->user->email ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if($attendance->clock_in)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($attendance->clock_out)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize bg-gray-100 text-gray-800">
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <p class="text-base font-medium text-gray-900">Belum ada riwayat absensi</p>
                                        <p class="text-sm mt-1">Belum ada anggota yang absen untuk hari ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
