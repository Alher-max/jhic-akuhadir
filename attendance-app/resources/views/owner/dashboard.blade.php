<x-app-layout>
    <x-slot:title>Dasbor Kepala Sekolah — HadirSekolah</x-slot:title>
    
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Dasbor Kepala Sekolah') }}
            </h2>
            
            <!-- FILTER PERIODE -->
            <div class="flex items-center gap-2">
                <label for="period" class="text-sm font-medium text-gray-600">Periode:</label>
                <select name="period" id="period" x-on:change="window.location.href = '{{ route('kepsek.dashboard') }}?period=' + $event.target.value" class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm text-sm">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="this_week" {{ $period == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="this_semester" {{ $period == 'this_semester' ? 'selected' : '' }}>Semester Ini</option>
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- SECTION 1: METRIK STATISTIK UTAMA (CARDS) -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Total Siswa -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Total Siswa</div>
                    <div class="text-3xl font-extrabold text-slate-800">{{ number_format($totalSiswa) }}</div>
                </div>
                
                <!-- Total Guru -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Pendidik (Guru)</div>
                    <div class="text-3xl font-extrabold text-slate-800">{{ number_format($totalGuru) }}</div>
                </div>

                <!-- Total Staf -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Kependidikan (Staf)</div>
                    <div class="text-3xl font-extrabold text-slate-800">{{ number_format($totalStaf) }}</div>
                </div>

                <!-- Kedisiplinan Siswa -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-2 h-full {{ $studentDisciplineRate >= 80 ? 'bg-emerald-500' : ($studentDisciplineRate >= 60 ? 'bg-amber-400' : 'bg-red-500') }}"></div>
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">% Kedisiplinan Siswa</div>
                    <div class="flex items-end gap-2">
                        <div class="text-3xl font-extrabold text-slate-800">{{ $studentDisciplineRate }}%</div>
                    </div>
                </div>

                <!-- Kedisiplinan Guru -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-2 h-full {{ $staffDisciplineRate >= 80 ? 'bg-emerald-500' : ($staffDisciplineRate >= 60 ? 'bg-amber-400' : 'bg-red-500') }}"></div>
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">% Kedisiplinan Guru</div>
                    <div class="flex items-end gap-2">
                        <div class="text-3xl font-extrabold text-slate-800">{{ $staffDisciplineRate }}%</div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: LIVE SNAPSHOT & GRAFIK TREN -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI: LIVE SNAPSHOT HARI INI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></div> 
                        Live Snapshot Hari Ini
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Siswa -->
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <h4 class="font-semibold text-slate-700">Siswa ({{ $totalSiswa > 0 ? $siswaSnapshot['hadir'] + $siswaSnapshot['terlambat'] + $siswaSnapshot['izin'] + $siswaSnapshot['alpa'] : 0 }} Record)</h4>
                            </div>
                            <!-- Progress Bar -->
                            @php
                                $totalS = max(1, $siswaSnapshot['hadir'] + $siswaSnapshot['terlambat'] + $siswaSnapshot['izin'] + $siswaSnapshot['alpa']);
                                $pHadir = ($siswaSnapshot['hadir'] / $totalS) * 100;
                                $pTelat = ($siswaSnapshot['terlambat'] / $totalS) * 100;
                                $pIzin = ($siswaSnapshot['izin'] / $totalS) * 100;
                                $pAlpa = ($siswaSnapshot['alpa'] / $totalS) * 100;
                            @endphp
                            <div class="w-full h-3 flex rounded-full overflow-hidden mb-3 bg-slate-100">
                                <div style="width: {{ $pHadir }}%" class="bg-emerald-500 h-full transition-all"></div>
                                <div style="width: {{ $pTelat }}%" class="bg-amber-400 h-full transition-all"></div>
                                <div style="width: {{ $pIzin }}%" class="bg-blue-400 h-full transition-all"></div>
                                <div style="width: {{ $pAlpa }}%" class="bg-red-500 h-full transition-all"></div>
                            </div>
                            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                                <div class="bg-slate-50 rounded p-1 border border-slate-100">
                                    <div class="font-bold text-emerald-600">{{ $siswaSnapshot['hadir'] }}</div>
                                    <div class="text-slate-500">Hadir</div>
                                </div>
                                <div class="bg-slate-50 rounded p-1 border border-slate-100">
                                    <div class="font-bold text-amber-500">{{ $siswaSnapshot['terlambat'] }}</div>
                                    <div class="text-slate-500">Terlambat</div>
                                </div>
                                <div class="bg-slate-50 rounded p-1 border border-slate-100">
                                    <div class="font-bold text-blue-500">{{ $siswaSnapshot['izin'] }}</div>
                                    <div class="text-slate-500">Izin/Sakit</div>
                                </div>
                                <div class="bg-slate-50 rounded p-1 border border-slate-100">
                                    <div class="font-bold text-red-600">{{ $siswaSnapshot['alpa'] }}</div>
                                    <div class="text-slate-500">Alpa</div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Guru -->
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <h4 class="font-semibold text-slate-700">Status Kehadiran Guru (Total: {{ $totalGuru }})</h4>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-emerald-700">{{ $guruSnapshot['hadir'] }}</div>
                                    <div class="text-xs text-emerald-600 font-medium">Hadir & Mengajar</div>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-red-700">{{ $guruSnapshot['absen'] }}</div>
                                    <div class="text-xs text-red-600 font-medium">Izin / Alpa</div>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-slate-700">{{ $guruSnapshot['belum_absen'] }}</div>
                                    <div class="text-xs text-slate-500 font-medium">Belum Ada Data</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: GRAFIK TREN (SVG Bar Chart) -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col">
                    <h3 class="text-lg font-bold text-slate-800 mb-6">Tren Kedisiplinan 7 Hari Terakhir</h3>
                    
                    <!-- SVG Chart Container -->
                    <div class="flex-1 flex items-end justify-between gap-2 h-48 relative pt-6 pb-6 ml-8">
                        <!-- Background Grid Lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-6 pt-6">
                            <div class="w-full border-t border-slate-100 h-0 flex items-center relative"><span class="absolute -left-8 text-xs text-slate-400 w-6 text-right">100</span></div>
                            <div class="w-full border-t border-slate-100 h-0 flex items-center relative"><span class="absolute -left-8 text-xs text-slate-400 w-6 text-right">75</span></div>
                            <div class="w-full border-t border-slate-100 h-0 flex items-center relative"><span class="absolute -left-8 text-xs text-slate-400 w-6 text-right">50</span></div>
                            <div class="w-full border-t border-slate-100 h-0 flex items-center relative"><span class="absolute -left-8 text-xs text-slate-400 w-6 text-right">25</span></div>
                            <div class="w-full border-t border-slate-300 h-0 flex items-center relative"><span class="absolute -left-8 text-xs text-slate-400 w-6 text-right">0</span></div>
                        </div>

                        <!-- Bars -->
                        @foreach($trendData as $data)
                        <div class="relative flex flex-col items-center flex-1 h-full z-10 group justify-end">
                            <!-- Tooltip -->
                            <div class="absolute -top-8 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-20">
                                {{ $data['rate'] }}% Tepat Waktu
                            </div>
                            
                            <!-- Bar -->
                            <div class="w-full max-w-[40px] bg-red-600 rounded-t-sm transition-all duration-500 hover:bg-red-700 cursor-pointer shadow-sm" style="height: {{ $data['rate'] }}%;"></div>
                            
                            <!-- Label -->
                            <div class="absolute -bottom-6 text-xs font-medium text-slate-500">{{ $data['day'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- SECTION 3: QUICK LOOKUP & JADWAL -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI: QUICK LOOKUP -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" x-data="{
                    query: '',
                    searchType: 'student',
                    results: [],
                    isLoading: false,
                    async search() {
                        if (this.query.trim().length < 2) {
                            this.results = [];
                            return;
                        }
                        this.isLoading = true;
                        try {
                            const url = this.searchType === 'student' 
                                ? '{{ route('kepsek.api.search-student') }}' 
                                : '{{ route('kepsek.api.search-teacher') }}';
                            const response = await fetch(`${url}?q=${encodeURIComponent(this.query)}`);
                            if(response.ok) {
                                this.results = await response.json();
                            } else {
                                this.results = [];
                            }
                        } catch (error) {
                            this.results = [];
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Pencarian Cepat
                    </h3>
                    
                    <div class="flex gap-2 mb-4">
                        <select x-model="searchType" @change="search" class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm text-sm w-1/3">
                            <option value="student">Siswa (Nama/NISN)</option>
                            <option value="teacher">Guru/Staf (Nama/Email)</option>
                        </select>
                        <input type="text" x-model="query" @input.debounce.300ms="search" placeholder="Ketik kata kunci..." class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm text-sm w-2/3">
                    </div>

                    <div class="min-h-[200px] border border-slate-100 rounded-lg bg-slate-50 p-2 overflow-y-auto max-h-[300px]">
                        <template x-if="isLoading">
                            <div class="flex justify-center p-4">
                                <svg class="animate-spin h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </template>
                        
                        <template x-if="!isLoading && results.length === 0 && query.length > 0">
                            <div class="text-center text-sm text-slate-500 p-4">Tidak ada hasil ditemukan.</div>
                        </template>

                        <template x-if="!isLoading && results.length === 0 && query.length === 0">
                            <div class="text-center text-sm text-slate-400 p-4 italic">Ketik minimal 2 huruf untuk memulai pencarian...</div>
                        </template>

                        <ul class="space-y-1">
                            <template x-for="item in results" :key="item.id">
                                <li class="bg-white p-3 rounded-md shadow-sm border border-slate-100 flex justify-between items-center hover:border-red-200 transition-colors cursor-pointer">
                                    <div>
                                        <div class="font-bold text-sm text-slate-800" x-text="item.name"></div>
                                        <div class="text-xs text-slate-500" x-text="searchType === 'student' ? (item.nisn ? 'NISN: '+item.nisn : '') : item.email"></div>
                                    </div>
                                    <div class="text-xs font-semibold px-2 py-1 bg-slate-100 text-slate-600 rounded" x-text="item.school_class ? item.school_class.name : item.role"></div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- KOLOM KANAN: JADWAL MENGAJAR -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col max-h-[400px]">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Jadwal Mengajar Hari Ini
                    </h3>
                    
                    <div class="overflow-y-auto flex-1 pr-2">
                        @if($jadwalGuru->isEmpty())
                            <div class="text-center p-6 bg-slate-50 rounded-lg border border-slate-100 border-dashed">
                                <p class="text-slate-500 text-sm">Tidak ada jadwal mengajar yang tercatat hari ini.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($jadwalGuru as $jadwal)
                                <div class="p-3 border-l-4 {{ \Carbon\Carbon::now()->between($jadwal->start_time, $jadwal->end_time) ? 'border-emerald-500 bg-emerald-50' : 'border-red-700 bg-slate-50' }} rounded-r-lg border-y border-r border-slate-100 flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-sm text-slate-800">{{ $jadwal->name }}</div>
                                        <div class="text-xs text-slate-600 mt-0.5">
                                            Guru: <span class="font-semibold">{{ $jadwal->teacher->name ?? 'Belum Ditentukan' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($jadwal->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->end_time)->format('H:i') }}</div>
                                        <div class="text-[10px] uppercase font-bold tracking-wider mt-1 px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-500 inline-block">
                                            {{ $jadwal->schoolClass->name ?? 'Semua Kelas' }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- SECTION 4: PERHATIAN KHUSUS (SISWA INDISIPLINER) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-rose-100">
                <div class="px-6 py-4 border-b border-rose-100 bg-rose-50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-rose-200 rounded text-rose-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-rose-900">Perhatian Khusus <span class="text-sm font-normal text-rose-700">(Top 5 Alpa Semester Ini)</span></h3>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold border-b border-slate-100">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold border-b border-slate-100">NISN</th>
                                <th class="px-6 py-4 font-bold border-b border-slate-100 text-center">Jumlah Alpa</th>
                                <th class="px-6 py-4 font-bold border-b border-slate-100 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($problematicStudents as $student)
                                @if($student->absent_count > 0)
                                <tr class="hover:bg-rose-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800">{{ $student->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $student->nisn ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            {{ $student->absent_count }} Hari
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" class="text-sm font-semibold text-red-700 hover:text-red-900 bg-white border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
                                            Kontak Ortu
                                        </button>
                                    </td>
                                </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-emerald-600">
                                            <svg class="w-10 h-10 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <p class="font-bold">Luar Biasa!</p>
                                            <p class="text-sm text-emerald-700 mt-1">Tidak ada catatan alpa siswa di semester ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Removed Alpine.data quickLookup from push scripts -->
</x-app-layout>
