<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('homeroom.reports.index') }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Daftar Rapor
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Kelas {{ $class->nama_kelas }}</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Modul Vokasi SMK: Kelas {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Praktik Kerja Lapangan (PKL), Presensi Geofence Industri Mitra, & Uji Kompetensi Keahlian (UKK)
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vocational.internships.create', $class) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <span class="material-symbols-outlined text-[18px]">add_business</span>
                    <span>Daftarkan Siswa PKL</span>
                </a>
                @if($activeYear)
                    <div class="px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        TA: {{ $activeYear->formatted_period }}
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Sub-Navigation Menu Tabs -->
            <div class="flex items-center gap-2 border-b border-brand-border pb-3 overflow-x-auto text-sm font-bold">
                <a href="{{ route('vocational.internships.index', $class) }}"
                    class="px-4 py-2 rounded-xl bg-brand-primary text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">business_center</span>
                    <span>Praktik Kerja Lapangan (PKL)</span>
                </a>
                <a href="{{ route('vocational.ukk.index', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                    <span>Uji Kompetensi Keahlian (UKK)</span>
                </a>
            </div>

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Tabel Penempatan PKL -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-5 border-b border-brand-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-base text-brand-text-main">Daftar Penempatan PKL Siswa</h3>
                        <p class="text-xs text-brand-text-muted mt-0.5">
                            Data industri mitra, pembimbing lapangan, integrasi geofence presensi, dan status nilai PKL
                        </p>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-brand-border text-gray-600">
                        {{ $placements->count() }} Siswa Terdaftar PKL
                    </span>
                </div>

                @if($placements->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-3xl">domain_disabled</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Penempatan PKL</h3>
                        <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                            Daftarkan siswa kelas {{ $class->nama_kelas }} ke perusahaan/industri mitra untuk memulai pemantauan presensi dan penilaian PKL.
                        </p>
                        <a href="{{ route('vocational.internships.create', $class) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                            <span class="material-symbols-outlined text-[18px]">add_business</span>
                            <span>Daftarkan Siswa Sekarang</span>
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 text-gray-700 font-extrabold uppercase border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-center w-10">No</th>
                                    <th class="px-4 py-3">Nama Siswa</th>
                                    <th class="px-4 py-3">Industri Mitra (DUDI)</th>
                                    <th class="px-4 py-3">Pembimbing Industri & Sekolah</th>
                                    <th class="px-4 py-3">Periode Magang</th>
                                    <th class="px-4 py-3 text-center">Geofence Lokasi</th>
                                    <th class="px-4 py-3 text-center">Nilai Akhir</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($placements as $idx => $placement)
                                    @php
                                        $assess = $placement->assessment;
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-4 py-3 text-center text-gray-500 font-semibold">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-900">
                                            <div class="text-sm text-brand-text-main">{{ $placement->student?->name }}</div>
                                            <div class="text-[11px] font-mono text-gray-400">NISN: {{ $placement->student?->nisn ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-extrabold text-gray-900">{{ $placement->company_name }}</div>
                                            <div class="text-[11px] text-gray-500 truncate max-w-xs">{{ $placement->company_address ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">
                                            <div class="font-semibold">DUDI: {{ $placement->mentor_name ?? '-' }}</div>
                                            <div class="text-[11px] text-gray-500">Guru: {{ $placement->teacherSupervisor?->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                            <div>{{ $placement->start_date->format('d/m/Y') }} - {{ $placement->end_date->format('d/m/Y') }}</div>
                                            <div class="text-[11px] text-gray-400">{{ $placement->start_date->diffInMonths($placement->end_date) }} Bulan</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($placement->industryLocation)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[11px]">
                                                    <span class="material-symbols-outlined text-[13px]">location_on</span>
                                                    <span>{{ $placement->industryLocation->name }}</span>
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic">Standar Sekolah</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if($assess)
                                                <div class="font-black text-sm text-gray-900">{{ number_format($assess->final_score, 1) }}</div>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ $assess->final_score >= 85 ? 'bg-emerald-100 text-emerald-800' : ($assess->final_score >= 75 ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                                    {{ $assess->predicate }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">
                                                    Belum Dinilai
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('vocational.internships.assessment.show', $placement) }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                                                    <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                                    <span>{{ $assess ? 'Ubah Nilai' : 'Input Nilai' }}</span>
                                                </a>

                                                @if($assess)
                                                    <a href="{{ route('vocational.internships.print', $placement) }}" target="_blank"
                                                        title="Cetak Lembar Nilai PKL A4"
                                                        class="p-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                                    </a>
                                                @endif

                                                <form method="POST" action="{{ route('vocational.internships.destroy', $placement) }}"
                                                    onsubmit="return confirm('Hapus penempatan PKL untuk {{ $placement->student?->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition" title="Hapus Penempatan">
                                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
