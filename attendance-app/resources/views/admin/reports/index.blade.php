<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Rekapitulasi Laporan Kehadiran') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ selectedMonth: '{{ $selectedMonth }}', selectedLocation: '{{ $locationId ?? '' }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Control Panel -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col md:flex-row items-end gap-4 w-full md:w-auto">
                    <div class="w-full md:w-48">
                        <label for="month" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Bulan & Tahun</label>
                        <input type="month" id="month" name="month" x-model="selectedMonth" 
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm bg-gray-50"
                            @change="$event.target.form.submit()">
                    </div>
                    <div class="w-full md:w-56">
                        <label for="location_id" class="block text-sm font-semibold text-gray-700 mb-1">Lokasi / Cabang</label>
                        <select id="location_id" name="location_id" x-model="selectedLocation" 
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm bg-gray-50"
                            @change="$event.target.form.submit()">
                            <option value="">Semua Cabang / Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <!-- Export Buttons -->
                <div class="flex gap-3 w-full md:w-auto">
                    <a :href="`{{ route('admin.reports.export-excel') }}?month=${selectedMonth}&location_id=${selectedLocation}`" 
                       class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition-colors gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Unduh Excel
                    </a>
                    
                    <a :href="`{{ route('admin.reports.export-pdf') }}?month=${selectedMonth}&location_id=${selectedLocation}`" target="_blank"
                       class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-sm transition-colors gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak PDF
                    </a>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold border-b border-gray-100">Anggota</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100">Lokasi / Cabang</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center">Hadir</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center">Terlambat</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center">Disiplin</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center">Sakit</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center">Izin</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center">Tugas Luar</th>
                                <th class="px-6 py-4 font-bold border-b border-gray-100 text-center text-rose-500">Alpha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reports as $r)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $r['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $r['email'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                                        {{ $r['location_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center bg-emerald-100 text-emerald-700 font-bold w-8 h-8 rounded-full">{{ $r['present'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center bg-orange-100 text-orange-700 font-bold w-8 h-8 rounded-full">{{ $r['late'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <span class="font-bold text-sm {{ $r['discipline_score'] >= 90 ? 'text-emerald-600' : ($r['discipline_score'] >= 75 ? 'text-orange-500' : 'text-rose-600') }}">
                                                {{ $r['discipline_score'] }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1.5">
                                            <div class="h-1.5 rounded-full {{ $r['discipline_score'] >= 90 ? 'bg-emerald-500' : ($r['discipline_score'] >= 75 ? 'bg-orange-400' : 'bg-rose-500') }}" style="width: {{ $r['discipline_score'] }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ $r['sick'] }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ $r['permission'] }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ $r['duty'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($r['alpha'] > 0)
                                            <span class="inline-flex items-center justify-center bg-rose-100 text-rose-700 font-bold w-8 h-8 rounded-full">{{ $r['alpha'] }}</span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-base font-medium text-gray-900">Belum ada data anggota</p>
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
