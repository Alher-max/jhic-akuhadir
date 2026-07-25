<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Laporan Kehadiran & Peringkat Terajin') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- FILTER PERIODE & GLOBAL SUMMARY -->
            <div class="flex flex-col lg:flex-row gap-6">
                
                <!-- Period Toggle -->
                <div class="bg-brand-surface rounded-2xl shadow-sm border border-brand-border p-5 lg:w-1/3 flex flex-col justify-center">
                    <h3 class="font-bold text-gray-800 mb-4 text-center">Pilih Periode Laporan</h3>
                    <form method="GET" action="{{ route('admin.reports.index') }}" id="periodForm" class="flex p-1 bg-gray-100 rounded-xl">
                        @php $periods = ['weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'semesterly' => 'Semesteran']; @endphp
                        @foreach($periods as $val => $label)
                            <label class="flex-1 text-center cursor-pointer relative">
                                <input type="radio" name="period" value="{{ $val }}" class="peer sr-only" onchange="document.getElementById('periodForm').submit()" {{ $period === $val ? 'checked' : '' }}>
                                <div class="px-3 py-2 text-sm font-semibold rounded-lg transition-all peer-checked:bg-brand-primary peer-checked:text-white peer-checked:shadow-sm text-gray-500 hover:text-gray-700">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </form>
                    <div class="mt-4 text-center text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM YYYY') }}
                    </div>
                </div>

                <!-- Global Summary Cards -->
                <div class="lg:w-2/3 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-brand-surface rounded-2xl shadow-sm border border-brand-border p-5 text-center flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 mx-auto bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-3">
                                <i class="fa-solid fa-check-double text-lg"></i>
                            </div>
                            <div class="text-3xl font-black text-gray-800 mb-1">{{ $globalSummary['present'] }}</div>
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hadir</div>
                        </div>
                    </div>
                    
                    <div class="bg-brand-surface rounded-2xl shadow-sm border border-brand-border p-5 text-center flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-blue-50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-3">
                                <i class="fa-solid fa-envelope-open-text text-lg"></i>
                            </div>
                            <div class="text-3xl font-black text-gray-800 mb-1">{{ $globalSummary['permission'] }}</div>
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Izin</div>
                        </div>
                    </div>

                    <div class="bg-brand-surface rounded-2xl shadow-sm border border-brand-border p-5 text-center flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-yellow-50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 mx-auto bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mb-3">
                                <i class="fa-solid fa-thermometer text-lg"></i>
                            </div>
                            <div class="text-3xl font-black text-gray-800 mb-1">{{ $globalSummary['sick'] }}</div>
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sakit</div>
                        </div>
                    </div>

                    <div class="bg-brand-surface rounded-2xl shadow-sm border border-brand-border p-5 text-center flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-rose-50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 mx-auto bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mb-3">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </div>
                            <div class="text-3xl font-black text-gray-800 mb-1">{{ $globalSummary['alpha'] }}</div>
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Alpha</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PERINGKAT KLASEMEN (PODIUMS) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Siswa Terajin -->
                @include('admin.reports.partials.podium_card', ['title' => '👨‍🎓 Siswa Terajin', 'data' => $topStudents, 'type' => 'user'])
                <!-- Kelas Terajin -->
                @include('admin.reports.partials.podium_card', ['title' => '🏫 Kelas Terajin', 'data' => $topClasses, 'type' => 'class'])
                <!-- Guru Terajin -->
                @include('admin.reports.partials.podium_card', ['title' => '👨‍🏫 Guru Terajin', 'data' => $topTeachers, 'type' => 'user'])
                <!-- Staff Terajin -->
                @include('admin.reports.partials.podium_card', ['title' => '👨‍💼 Staff Terajin', 'data' => $topStaffs, 'type' => 'user'])
            </div>
            
            <!-- REKAPITULASI TABEL -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mt-8">
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row md:justify-between md:items-center bg-gray-50/50 rounded-t-2xl gap-4">
                    <h3 class="font-bold text-gray-800 whitespace-nowrap">Detail Rekapitulasi Kehadiran</h3>
                    
                    <div class="flex-1 max-w-md w-full">
                        <form action="{{ route('admin.reports.index') }}" method="GET">
                            <input type="hidden" name="period" value="{{ request('period', 'monthly') }}">
                            <div class="relative flex items-center">
                                <i class="fa-solid fa-search absolute left-3 text-gray-400"></i>
                                <input type="text" name="rekap_search" value="{{ request('rekap_search') }}" placeholder="Cari Nama Anggota..." class="w-full rounded-xl border-gray-300 text-sm focus:ring-brand-primary focus:border-brand-primary pl-9 pr-16 shadow-sm">
                                <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 bg-brand-primary text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-colors">
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('admin.reports.export-excel') }}?month={{ $selectedMonth }}&rekap_search={{ request('rekap_search') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-100">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ route('admin.reports.export-pdf') }}?month={{ $selectedMonth }}&rekap_search={{ request('rekap_search') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-100">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <!-- existing table code modified a bit for UI -->
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-4 font-bold">Anggota</th>
                                <th class="px-6 py-4 font-bold text-center">Hadir</th>
                                <th class="px-6 py-4 font-bold text-center">Terlambat</th>
                                <th class="px-6 py-4 font-bold text-center">Disiplin</th>
                                <th class="px-6 py-4 font-bold text-center">Sakit</th>
                                <th class="px-6 py-4 font-bold text-center">Izin</th>
                                <th class="px-6 py-4 font-bold text-center">Alpha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reports as $r)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-gray-900 text-sm">{{ $r['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $r['email'] }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="inline-flex items-center justify-center bg-emerald-100 text-emerald-700 font-bold w-7 h-7 rounded-full text-xs">{{ $r['present'] }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="inline-flex items-center justify-center bg-orange-100 text-orange-700 font-bold w-7 h-7 rounded-full text-xs">{{ $r['late'] }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <div class="font-bold text-sm {{ $r['discipline_score'] >= 90 ? 'text-emerald-600' : ($r['discipline_score'] >= 75 ? 'text-orange-500' : 'text-rose-600') }}">
                                            {{ $r['discipline_score'] }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-center text-gray-600 font-medium text-sm">{{ $r['sick'] }}</td>
                                    <td class="px-6 py-3 text-center text-gray-600 font-medium text-sm">{{ $r['permission'] }}</td>
                                    <td class="px-6 py-3 text-center">
                                        @if($r['alpha'] > 0)
                                            <span class="inline-flex items-center justify-center bg-rose-100 text-rose-700 font-bold w-7 h-7 rounded-full text-xs">{{ $r['alpha'] }}</span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Belum ada data rekapitulasi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($reports->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 rounded-b-2xl">
                    {{ $reports->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
            
        </div>
    </div>
</x-app-layout>
