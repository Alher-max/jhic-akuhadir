<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Jadwal') }}
            </h2>
            <a href="{{ route('schedules.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Buat Jadwal Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ viewMode: localStorage.getItem('scheduleViewMode') || 'list' }" x-init="$watch('viewMode', val => localStorage.setItem('scheduleViewMode', val))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- FILTER BAR & VIEW SWITCHER -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <form method="GET" action="{{ route('schedules.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto" id="filterForm">
                    <div class="w-full sm:w-48">
                        <select name="class_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-48">
                        <select name="teacher_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Guru</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-48">
                        <select name="day" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Hari</option>
                            <option value="1" {{ request('day') == '1' ? 'selected' : '' }}>Senin</option>
                            <option value="2" {{ request('day') == '2' ? 'selected' : '' }}>Selasa</option>
                            <option value="3" {{ request('day') == '3' ? 'selected' : '' }}>Rabu</option>
                            <option value="4" {{ request('day') == '4' ? 'selected' : '' }}>Kamis</option>
                            <option value="5" {{ request('day') == '5' ? 'selected' : '' }}>Jumat</option>
                            <option value="6" {{ request('day') == '6' ? 'selected' : '' }}>Sabtu</option>
                        </select>
                    </div>
                    
                    @if(request('class_id') || request('teacher_id') || request('day'))
                        <a href="{{ route('schedules.index') }}" class="text-sm text-gray-500 hover:text-rose-600 font-medium px-2 flex-shrink-0 transition-colors w-full sm:w-auto text-center sm:text-left mt-2 sm:mt-0">
                            Reset Filter
                        </a>
                    @endif
                </form>

                <!-- Toggle Switcher -->
                <div class="bg-gray-100 p-1.5 rounded-lg flex inline-flex shadow-inner self-start lg:self-auto w-full sm:w-auto">
                    <button @click="viewMode = 'list'" :class="{'bg-white shadow text-gray-900 font-bold': viewMode === 'list', 'text-gray-500 hover:text-gray-700': viewMode !== 'list'}" class="flex-1 sm:flex-none px-4 py-2 rounded-md text-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-list"></i> 📋 Daftar
                    </button>
                    <button @click="viewMode = 'grid'" :class="{'bg-white shadow text-gray-900 font-bold': viewMode === 'grid', 'text-gray-500 hover:text-gray-700': viewMode !== 'grid'}" class="flex-1 sm:flex-none px-4 py-2 rounded-md text-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-regular fa-calendar-days"></i> 🗓️ Matriks Mingguan
                    </button>
                </div>
            </div>

            <!-- MODE 1: DAFTAR (LIST) -->
            <div x-show="viewMode === 'list'" x-cloak class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 transition-opacity">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-medium border-b border-gray-100">Nama Jadwal</th>
                                <th class="px-6 py-4 font-medium border-b border-gray-100">Kelas & Guru</th>
                                <th class="px-6 py-4 font-medium border-b border-gray-100">Tipe / Waktu</th>
                                <th class="px-6 py-4 font-medium border-b border-gray-100">Jam (Masuk - Pulang)</th>
                                <th class="px-6 py-4 font-medium border-b border-gray-100">Toleransi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($schedules as $schedule)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $schedule->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $schedule->users()->count() }} peserta terdaftar</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            @if($schedule->schoolClass)
                                                <span class="inline-flex items-center w-max px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    <i class="fa-solid fa-graduation-cap mr-1.5 opacity-70"></i> {{ $schedule->schoolClass->nama_kelas }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Kelas: -</span>
                                            @endif
                                            
                                            @if($schedule->teacher)
                                                <span class="inline-flex items-center w-max px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    <i class="fa-solid fa-chalkboard-user mr-1.5 opacity-70"></i> {{ $schedule->teacher->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Guru: -</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($schedule->type === 'routine')
                                            @php
                                                $days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
                                                $dName = $days[$schedule->day_of_week] ?? 'Hari '.$schedule->day_of_week;
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                Rutin ({{ $dName }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                                Non-Rutin ({{ \Carbon\Carbon::parse($schedule->specific_date)->format('d M Y') }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-clock text-gray-400"></i>
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $schedule->grace_period_minutes }} Menit
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-regular fa-calendar-xmark text-4xl mb-3 text-gray-300"></i>
                                            <p class="text-base font-medium text-gray-700">Tidak ada jadwal ditemukan.</p>
                                            <p class="text-sm mt-1">Coba sesuaikan filter atau buat jadwal baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODE 2: MATRIKS MINGGUAN (GRID) -->
            <div x-show="viewMode === 'grid'" x-cloak class="transition-opacity">
                <div class="overflow-x-auto pb-4">
                    <div class="grid grid-cols-5 gap-4 min-w-[900px]">
                    @php
                        $daysMap = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat'
                        ];
                    @endphp
                    
                    @foreach($daysMap as $dayNum => $dayName)
                        <!-- Jika ada filter day dan bukan hari ini, bisa disembunyikan. Tapi UI lebih bagus kalau tampil semua namun kosong jika filter aktif. -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col {{ (request('day') && request('day') != $dayNum) ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 text-center relative">
                                @if(date('N') == $dayNum)
                                    <div class="absolute top-0 left-0 w-full h-1 bg-indigo-500"></div>
                                @endif
                                <h3 class="font-bold text-gray-800">{{ $dayName }}</h3>
                            </div>
                            <div class="p-3 flex-1 flex flex-col gap-3 bg-gray-50/30 min-h-[300px]">
                                @php
                                    $daySchedules = $schedules->filter(function($s) use ($dayNum) {
                                        return $s->type === 'routine' && $s->day_of_week == $dayNum;
                                    })->sortBy('start_time');
                                @endphp

                                @forelse($daySchedules as $sched)
                                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all relative overflow-hidden group">
                                        <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500 group-hover:bg-indigo-600 transition-colors"></div>
                                        <div class="pl-2">
                                            <div class="text-xs font-bold text-indigo-600 mb-1.5 flex items-center gap-1.5">
                                                <i class="fa-regular fa-clock"></i> 
                                                {{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}
                                            </div>
                                            <div class="font-bold text-gray-900 text-sm leading-snug mb-3">{{ $sched->name }}</div>
                                            
                                            <div class="flex flex-col gap-1.5 mt-auto">
                                                @if($sched->schoolClass)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-100 truncate w-fit max-w-full">
                                                        <i class="fa-solid fa-graduation-cap mr-1.5 opacity-70"></i> {{ $sched->schoolClass->nama_kelas }}
                                                    </span>
                                                @endif
                                                @if($sched->teacher)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100 truncate w-fit max-w-full">
                                                        <i class="fa-solid fa-chalkboard-user mr-1.5 opacity-70"></i> {{ $sched->teacher->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex-1 flex flex-col items-center justify-center text-gray-400 py-6">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-2">
                                            <i class="fa-regular fa-folder-open text-gray-300"></i>
                                        </div>
                                        <p class="text-xs font-medium">Kosong</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
                
                <!-- Extra Schedules (Non-Routine) -->
                @php
                    $nonRoutine = $schedules->filter(function($s) { return $s->type === 'non_routine'; });
                @endphp
                @if($nonRoutine->count() > 0)
                    <div class="mt-8 bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                <i class="fa-regular fa-calendar-star"></i>
                            </div>
                            Jadwal Ekstra / Ujian Khusus (Non-Rutin)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($nonRoutine as $sched)
                                <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100 shadow-sm relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500"></div>
                                    <div class="pl-2">
                                        <div class="text-xs font-bold text-purple-700 mb-1 flex items-center gap-1">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($sched->specific_date)->format('d M Y') }}
                                        </div>
                                        <div class="text-[10px] font-semibold text-gray-500 mb-2 flex items-center gap-1">
                                            <i class="fa-regular fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}
                                        </div>
                                        <div class="font-bold text-gray-900 text-sm leading-tight mb-2">{{ $sched->name }}</div>
                                        
                                        <div class="flex flex-col gap-1 mt-3">
                                            @if($sched->schoolClass)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-white text-gray-700 border border-gray-200">
                                                    {{ $sched->schoolClass->nama_kelas }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
