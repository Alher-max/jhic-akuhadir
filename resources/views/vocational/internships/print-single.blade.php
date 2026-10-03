<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai PKL - {{ $placement->student?->name }} - {{ $placement->company_name }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .page-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="py-8 px-4 min-h-screen flex flex-col items-center">

    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print w-full max-w-[210mm] mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
        <div class="flex items-center gap-3">
            <button onclick="window.history.back()"
                class="px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                ← Kembali
            </button>
            <div>
                <h3 class="font-extrabold text-sm text-gray-900">Laporan PKL: {{ $placement->student?->name }}</h3>
                <p class="text-[11px] text-gray-500">{{ $placement->company_name }} &bull; Kelas {{ $placement->schoolClass?->nama_kelas }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition flex items-center gap-2">
                <span>🖨️ Cetak Lembar Nilai PKL (A4)</span>
            </button>
        </div>
    </div>

    <!-- The Report Sheet -->
    <div class="page-sheet bg-white p-8 max-w-[210mm] w-full mx-auto shadow-lg rounded-none border border-gray-100 flex flex-col justify-between"
         style="min-height: 297mm; font-size: 11px; line-height: 1.4;">

        <div>
            <!-- Kop Lembaga Pendidikan Resmi -->
            <div class="border-b-2 border-black pb-3 mb-4 text-center">
                <h1 class="text-base font-black tracking-wide uppercase text-gray-900 leading-tight">
                    {{ $tenant->name ?? 'SEKOLAH MENENGAH KEJURUAN' }}
                </h1>
                <h2 class="text-sm font-extrabold tracking-wide uppercase text-gray-800 leading-tight">
                    LAPORAN CAPAIAN & PENILAIAN PRAKTIK KERJA LAPANGAN (PKL)
                </h2>
                <p class="text-[10px] text-gray-600 mt-0.5">
                    {{ $tenant->address ?? 'Jl. Vokasi Nasional' }} &bull; NPSN: {{ $tenant->npsn ?? '-' }}
                </p>
            </div>

            <!-- Identitas Siswa & Penempatan -->
            <div class="grid grid-cols-2 gap-x-6 gap-y-1 mb-4 text-[11px] pb-3 border-b border-gray-300">
                <div class="space-y-0.5">
                    <div class="flex">
                        <span class="w-32 text-gray-600">Nama Siswa</span>
                        <span class="font-extrabold text-gray-900">: {{ $placement->student?->name }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">NISN / NIS</span>
                        <span class="font-bold text-gray-800">: {{ $placement->student?->nisn ?? '-' }} / {{ $placement->student?->nis ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">Kelas / Keahlian</span>
                        <span class="font-bold text-gray-800">: {{ $placement->schoolClass?->nama_kelas }}</span>
                    </div>
                </div>
                <div class="space-y-0.5">
                    <div class="flex">
                        <span class="w-32 text-gray-600">Industri Mitra (DUDI)</span>
                        <span class="font-bold text-gray-800">: {{ $placement->company_name }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">Periode Magang</span>
                        <span class="font-bold text-gray-800">: {{ $placement->start_date->format('d/m/Y') }} s.d. {{ $placement->end_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">Tahun Ajaran</span>
                        <span class="font-bold text-gray-800">: {{ $placement->academicYear?->formatted_period ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Tabel Hasil Evaluasi & Penilaian PKL -->
            <div class="mb-5">
                <h4 class="text-[11px] font-bold text-gray-800 mb-2 uppercase tracking-wide">
                    A. Rekapitulasi Nilai Praktik Kerja Lapangan
                </h4>
                <table class="w-full text-left border-collapse border border-gray-400 text-[11px]">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800 font-bold border-b border-gray-400">
                            <th class="p-2 border border-gray-400 w-10 text-center">No</th>
                            <th class="p-2 border border-gray-400">Komponen Penilaian</th>
                            <th class="p-2 border border-gray-400 w-24 text-center">Bobot</th>
                            <th class="p-2 border border-gray-400 w-28 text-center">Nilai Angka</th>
                            <th class="p-2 border border-gray-400 w-28 text-center">Nilai Terbobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tech = $assessment?->technical_score ?? 0;
                            $soft = $assessment?->softskill_score ?? 0;
                            $att = $assessment?->attendance_score ?? 0;
                            $final = $assessment?->final_score ?? 0;
                        @endphp
                        <tr class="border-b border-gray-300">
                            <td class="p-2 border border-gray-300 text-center">1</td>
                            <td class="p-2 border border-gray-300 font-semibold">Kompetensi Teknis Kejuruan</td>
                            <td class="p-2 border border-gray-300 text-center">50%</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($tech, 1) }}</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($tech * 0.50, 2) }}</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="p-2 border border-gray-300 text-center">2</td>
                            <td class="p-2 border border-gray-300 font-semibold">Budaya Kerja, Karakter, & Soft Skills</td>
                            <td class="p-2 border border-gray-300 text-center">30%</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($soft, 1) }}</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($soft * 0.30, 2) }}</td>
                        </tr>
                        <tr class="border-b border-gray-300">
                            <td class="p-2 border border-gray-300 text-center">3</td>
                            <td class="p-2 border border-gray-300 font-semibold">Rekapitulasi Kehadiran di Industri Mitra</td>
                            <td class="p-2 border border-gray-300 text-center">20%</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($att, 1) }}</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($att * 0.20, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50/80 font-black border-t-2 border-gray-400">
                            <td colspan="3" class="p-2 border border-gray-400 text-right uppercase">Nilai Akhir PKL :</td>
                            <td colspan="2" class="p-2 border border-gray-400 text-center text-sm text-gray-900">
                                {{ number_format($final, 2) }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50/80 font-black">
                            <td colspan="3" class="p-2 border border-gray-400 text-right uppercase">Predikat Kelulusan PKL :</td>
                            <td colspan="2" class="p-2 border border-gray-400 text-center text-sm text-indigo-900">
                                {{ $assessment?->predicate ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Catatan Kinerja & Evaluasi Industri -->
            <div class="space-y-3 mb-6">
                <div>
                    <h4 class="text-[11px] font-bold text-gray-800 mb-1 uppercase tracking-wide">
                        B. Catatan Penguasaan Kompetensi Teknis
                    </h4>
                    <div class="border border-gray-400 p-2.5 rounded text-[10px] text-gray-800 bg-white leading-relaxed min-h-[45px]">
                        {{ $assessment?->technical_notes ?: 'Siswa menunjukkan kemampuan teknis yang baik dalam menyelesaikan tugas-tugas kejuruan selama masa magang industri.' }}
                    </div>
                </div>

                <div>
                    <h4 class="text-[11px] font-bold text-gray-800 mb-1 uppercase tracking-wide">
                        C. Catatan Budaya Kerja & Rekomendasi Industri
                    </h4>
                    <div class="border border-gray-400 p-2.5 rounded text-[10px] text-gray-800 bg-white leading-relaxed min-h-[45px]">
                        {{ $assessment?->softskill_notes ?: 'Siswa memiliki etos kerja, kedisiplinan, dan sikap profesional yang memuaskan selama pelaksanaan praktik kerja lapangan.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tanda Tangan 3 Pihak Resmi -->
        <div class="pt-2 text-[10px] text-gray-900">
            <div class="flex justify-between items-start mb-10">
                <div class="text-center w-56">
                    <p>Pembimbing Lapangan (DUDI),</p>
                    <p class="font-bold">{{ $placement->company_name }}</p>
                    <div class="h-14"></div>
                    <p class="font-black text-gray-900 underline">{{ $placement->mentor_name ?? 'Pembimbing Industri' }}</p>
                    <p class="text-[9px] text-gray-600">{{ $placement->mentor_position ?? 'Instruktur Industri' }}</p>
                </div>

                <div class="text-center w-56">
                    <p>{{ $tenant->city ?? 'Jakarta' }}, {{ $placement->end_date->translatedFormat('d F Y') }}</p>
                    <p class="font-bold">Guru Pembimbing Sekolah,</p>
                    <div class="h-14"></div>
                    <p class="font-black text-gray-900 underline">{{ $placement->teacherSupervisor?->name ?? 'Guru Pembimbing' }}</p>
                    <p class="text-[9px] text-gray-600">NIP: {{ $placement->teacherSupervisor?->nip ?? '-' }}</p>
                </div>
            </div>

            <div class="text-center w-64 mx-auto">
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Satuan Pendidikan</p>
                <div class="h-14"></div>
                <p class="font-black text-gray-900 underline">{{ $headmaster?->name ?? 'Kepala Sekolah' }}</p>
                <p class="text-[9px] text-gray-600">NIP: {{ $headmaster?->nip ?? '-' }}</p>
            </div>
        </div>

    </div>

</body>
</html>
