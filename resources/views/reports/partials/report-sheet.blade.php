{{-- Lembar Rapor Resmi A4 Siswa --}}
<div class="page-sheet bg-white text-gray-900 mx-auto p-8 rounded-xl shadow-lg border border-gray-200 max-w-[210mm] text-[11px] leading-normal font-sans">
    
    <!-- Kop Surat Resmi Sekolah -->
    <div class="border-b-2 border-double border-gray-900 pb-3 mb-4 text-center">
        <h1 class="text-base font-extrabold uppercase tracking-wide text-gray-900">
            {{ $tenant->name ?? 'PEMERINTAH DAERAH / YAYASAN PENDIDIKAN' }}
        </h1>
        <h2 class="text-lg font-black uppercase text-gray-950 mt-0.5">
            {{ $tenant->name ?? 'SEKOLAH HADIRYUK' }}
        </h2>
        <p class="text-[10px] text-gray-600 mt-1">
            @if($tenant?->npsn) <span>NPSN: {{ $tenant->npsn }}</span> • @endif
            @if($tenant?->address) <span>{{ $tenant->address }}</span> • @endif
            <span>Platform Presensi & Rapor Digital Terpadu HadirYuk</span>
        </p>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center my-3">
        <h3 class="text-sm font-black uppercase tracking-wider underline">
            LAPORAN HASIL BELAJAR (RAPOR)
        </h3>
        <p class="text-[10px] text-gray-600 mt-0.5">
            Kurikulum {{ ucfirst($schoolClass->curriculum_type ?? 'Merdeka') }}
        </p>
    </div>

    <!-- Identitas Siswa & Kelas (Grid 2 Kolom) -->
    <div class="grid grid-cols-2 gap-4 mb-4 text-[11px] border border-gray-300 p-2.5 rounded bg-gray-50/50">
        <table class="w-full">
            <tr>
                <td class="font-bold py-0.5 w-28">Nama Peserta Didik</td>
                <td class="w-2">:</td>
                <td class="font-semibold">{{ $student->name }}</td>
            </tr>
            <tr>
                <td class="font-bold py-0.5">NISN / NIS</td>
                <td>:</td>
                <td>{{ $student->nisn ?? $student->nis ?? '-' }}</td>
            </tr>
            <tr>
                <td class="font-bold py-0.5">Sekolah</td>
                <td>:</td>
                <td>{{ $tenant->name ?? 'HadirYuk' }}</td>
            </tr>
        </table>
        <table class="w-full">
            <tr>
                <td class="font-bold py-0.5 w-24">Kelas / Rombel</td>
                <td class="w-2">:</td>
                <td class="font-semibold">{{ $schoolClass->nama_kelas }}</td>
            </tr>
            <tr>
                <td class="font-bold py-0.5">Fase</td>
                <td>:</td>
                <td>{{ $schoolClass->fase ?? 'E' }}</td>
            </tr>
            <tr>
                <td class="font-bold py-0.5">Semester</td>
                <td>:</td>
                <td>{{ $academicYear->semester === '1' ? '1 (Ganjil)' : '2 (Genap)' }}</td>
            </tr>
            <tr>
                <td class="font-bold py-0.5">Tahun Ajaran</td>
                <td>:</td>
                <td>{{ $academicYear->name }}</td>
            </tr>
        </table>
    </div>

    <!-- A. NILAI AKADEMIK / CAPAIAN PEMBELAJARAN INTRAKURIKULER -->
    <div class="mb-4">
        <h4 class="font-bold text-[11px] uppercase mb-1.5 flex items-center gap-1">
            <span>A. Capaian Hasil Belajar (Intrakurikuler)</span>
        </h4>
        <table class="w-full border-collapse border border-gray-400 text-[10px]">
            <thead>
                <tr class="bg-gray-100 text-gray-900">
                    <th class="border border-gray-400 px-2 py-1.5 w-8 text-center">No</th>
                    <th class="border border-gray-400 px-2 py-1.5 w-44 text-left">Mata Pelajaran</th>
                    <th class="border border-gray-400 px-2 py-1.5 w-16 text-center">Nilai Akhir</th>
                    <th class="border border-gray-400 px-2 py-1.5 text-left">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $index => $grade)
                    <tr class="align-top">
                        <td class="border border-gray-400 px-2 py-1.5 text-center font-medium">{{ $index + 1 }}</td>
                        <td class="border border-gray-400 px-2 py-1.5 font-bold text-gray-900">
                            {{ $grade->subject->name ?? 'Mapel' }}
                        </td>
                        <td class="border border-gray-400 px-2 py-1.5 text-center font-black text-gray-900 bg-gray-50/50">
                            {{ number_format((float) $grade->score, 0) }}
                        </td>
                        <td class="border border-gray-400 px-2 py-1.5 text-[9.5px] leading-relaxed space-y-1">
                            @if($grade->highest_achievement)
                                <div>
                                    <strong class="text-emerald-700">Tercapai optimal:</strong>
                                    <span>{{ $grade->highest_achievement }}</span>
                                </div>
                            @endif
                            @if($grade->lowest_achievement)
                                <div>
                                    <strong class="text-amber-700">Perlu peningkatan:</strong>
                                    <span>{{ $grade->lowest_achievement }}</span>
                                </div>
                            @endif
                            @if(!$grade->highest_achievement && !$grade->lowest_achievement)
                                <span class="text-gray-400 italic">Capaian kompetensi tuntas.</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="border border-gray-400 px-2 py-3 text-center text-gray-400 italic">
                            Belum ada nilai mata pelajaran yang diinput pada semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($grades->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="2" class="border border-gray-400 px-2 py-1 text-right">Rata-rata Nilai Akhir:</td>
                        <td class="border border-gray-400 px-2 py-1 text-center font-black">{{ number_format($averageScore, 2) }}</td>
                        <td class="border border-gray-400 px-2 py-1 text-gray-600 text-[9px]">Total Nilai: {{ number_format($totalScore, 0) }} ({{ $grades->count() }} Mapel)</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- B. EKSTRAKURIKULER & C. PRESENSI (Grid 2 Kolom) -->
    <div class="grid grid-cols-3 gap-4 mb-4">
        <!-- B. Ekstrakurikuler (2 Kolom) -->
        <div class="col-span-2">
            <h4 class="font-bold text-[11px] uppercase mb-1.5">
                B. Ekstrakurikuler
            </h4>
            <table class="w-full border-collapse border border-gray-400 text-[10px]">
                <thead>
                    <tr class="bg-gray-100 text-gray-900">
                        <th class="border border-gray-400 px-2 py-1 w-8 text-center">No</th>
                        <th class="border border-gray-400 px-2 py-1 w-32 text-left">Kegiatan</th>
                        <th class="border border-gray-400 px-2 py-1 w-16 text-center">Predikat</th>
                        <th class="border border-gray-400 px-2 py-1 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($extracurriculars as $idx => $ekskul)
                        <tr class="align-top">
                            <td class="border border-gray-400 px-2 py-1 text-center">{{ $idx + 1 }}</td>
                            <td class="border border-gray-400 px-2 py-1 font-bold">{{ $ekskul->activity_name }}</td>
                            <td class="border border-gray-400 px-2 py-1 text-center font-bold">{{ $ekskul->predicate }}</td>
                            <td class="border border-gray-400 px-2 py-1 text-[9.5px] leading-tight text-gray-700">
                                {{ $ekskul->description ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border border-gray-400 px-2 py-2 text-center text-gray-400 italic">
                                Belum ada catatan kegiatan ekstrakurikuler.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- C. Rekapitulasi Presensi (1 Kolom) -->
        <div>
            <h4 class="font-bold text-[11px] uppercase mb-1.5">
                C. Ketidakhadiran
            </h4>
            <table class="w-full border-collapse border border-gray-400 text-[10px]">
                <thead>
                    <tr class="bg-gray-100 text-gray-900">
                        <th class="border border-gray-400 px-2 py-1 text-left">Alasan</th>
                        <th class="border border-gray-400 px-2 py-1 w-16 text-center">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-400 px-2 py-1">Sakit (S)</td>
                        <td class="border border-gray-400 px-2 py-1 text-center font-bold">{{ $attendance['sick'] }} hari</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-2 py-1">Izin (I)</td>
                        <td class="border border-gray-400 px-2 py-1 text-center font-bold">{{ $attendance['permission'] }} hari</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-400 px-2 py-1">Tanpa Keterangan (A)</td>
                        <td class="border border-gray-400 px-2 py-1 text-center font-bold text-red-600">{{ $attendance['alpha'] }} hari</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold">
                        <td class="border border-gray-400 px-2 py-1 text-right">Total:</td>
                        <td class="border border-gray-400 px-2 py-1 text-center">{{ $attendance['total'] }} hari</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- D. CATATAN WALI KELAS & KEPUTUSAN KENAIKAN KELAS -->
    <div class="border border-gray-400 p-2.5 rounded mb-4 text-[10.5px]">
        <div class="mb-2">
            <h4 class="font-bold uppercase text-[10px] text-gray-700 mb-0.5">Catatan Perkembangan Karakter & Motivasi:</h4>
            <p class="italic text-gray-900 leading-relaxed pl-2 border-l-2 border-indigo-600">
                "{{ $report->homeroom_notes ?: 'Peserta didik menunjukkan perkembangan yang baik dan konsisten selama pembelajaran semester ini. Pertahankan semangat belajar dan prestasinya.' }}"
            </p>
        </div>
        @if($report->promotion_status)
            <div class="mt-2 pt-1.5 border-t border-dashed border-gray-300 flex items-center justify-between">
                <span class="font-bold text-[10px] uppercase text-gray-700">Keputusan Akhir Semester / Kenaikan Kelas:</span>
                <span class="font-extrabold text-[11px] px-2 py-0.5 bg-gray-100 border border-gray-300 rounded">
                    {{ $report->promotion_status }}
                </span>
            </div>
        @endif
    </div>

    <!-- E. TITIMANGSA, TANDA TANGAN & QR CODE VERIFIKASI -->
    <div class="mt-4 pt-2">
        <div class="flex justify-between items-start text-[10.5px]">
            
            <!-- QR Code Keaslian Dokumen Resmi HadirYuk -->
            <div class="flex items-center gap-3 border border-gray-300 p-2 rounded bg-gray-50/70 max-w-xs">
                <div class="shrink-0 bg-white p-1 rounded border border-gray-200">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($verificationUrl) }}"
                        alt="QR Verifikasi"
                        class="w-14 h-14 object-contain"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                    <div class="qr-container w-14 h-14 hidden" data-code="{{ $verificationUrl }}"></div>
                </div>
                <div class="text-[9px] text-gray-600 leading-tight">
                    <div class="font-bold text-gray-900 flex items-center gap-1">
                        <span class="text-emerald-700">✔</span>
                        <span>Dokumen Resmi Terverifikasi</span>
                    </div>
                    <p class="mt-0.5">Pindai QR Code untuk memvalidasi keaslian rapor digital via sistem HadirYuk.</p>
                    <p class="font-mono text-[8px] text-gray-400 mt-1 truncate max-w-[140px]">
                        Hash: {{ substr($report->verification_hash, 0, 16) }}...
                    </p>
                </div>
            </div>

            <!-- Titimangsa & Tanda Tangan Wali Kelas -->
            <div class="text-center w-56">
                <p>{{ $tenant->city ?? 'Jakarta' }}, {{ $report->published_at ? $report->published_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold mt-1">Wali Kelas,</p>
                <div class="h-14"></div>
                <p class="font-bold underline">{{ $waliKelas->name ?? '-' }}</p>
                <p class="text-[9px] text-gray-600">NIP: {{ $waliKelas->profile?->nip ?? $waliKelas->nip ?? '-' }}</p>
            </div>
        </div>

        <!-- Tanda Tangan Baris Bawah: Orang Tua & Kepala Sekolah -->
        <div class="grid grid-cols-2 gap-8 text-[10.5px] mt-4 pt-2">
            <div class="text-center">
                <p class="font-bold">Mengetahui,</p>
                <p class="font-bold">Orang Tua / Wali Siswa,</p>
                <div class="h-14"></div>
                <p class="border-b border-gray-800 w-48 mx-auto"></p>
            </div>

            <div class="text-center">
                <p class="font-bold">Kepala Sekolah,</p>
                <p class="font-bold">{{ $tenant->name ?? 'Sekolah' }}</p>
                <div class="h-14"></div>
                <p class="font-bold underline">{{ $headmaster->name ?? 'Kepala Sekolah' }}</p>
                <p class="text-[9px] text-gray-600">NIP: {{ $headmaster->profile?->nip ?? $headmaster->nip ?? '-' }}</p>
            </div>
        </div>
    </div>

</div>
