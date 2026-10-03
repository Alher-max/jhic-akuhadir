<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip UKK - {{ $student->name }} - {{ $class->nama_kelas }}</title>
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
                <h3 class="font-extrabold text-sm text-gray-900">Transkrip UKK: {{ $student->name }}</h3>
                <p class="text-[11px] text-gray-500">Kelas {{ $class->nama_kelas }} &bull; {{ $assessment->scheme_name }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition flex items-center gap-2">
                <span>🖨️ Cetak Transkrip UKK (A4)</span>
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
                    TRANSKRIP HASIL UJI KOMPETENSI KEAHLIAN (UKK)
                </h2>
                <p class="text-[10px] text-gray-600 mt-0.5">
                    {{ $tenant->address ?? 'Jl. Pendidikan Kejuruan' }} &bull; NPSN: {{ $tenant->npsn ?? '-' }}
                </p>
            </div>

            <!-- Identitas Siswa & Skema Uji -->
            <div class="grid grid-cols-2 gap-x-6 gap-y-1 mb-4 text-[11px] pb-3 border-b border-gray-300">
                <div class="space-y-0.5">
                    <div class="flex">
                        <span class="w-32 text-gray-600">Nama Siswa</span>
                        <span class="font-extrabold text-gray-900">: {{ $student->name }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">NISN / NIS</span>
                        <span class="font-bold text-gray-800">: {{ $student->nisn ?? '-' }} / {{ $student->nis ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">Kelas / Rombel</span>
                        <span class="font-bold text-gray-800">: {{ $class->nama_kelas }}</span>
                    </div>
                </div>
                <div class="space-y-0.5">
                    <div class="flex">
                        <span class="w-32 text-gray-600">Lembaga Sertifikasi</span>
                        <span class="font-bold text-gray-800">: {{ $assessment->institution_name }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">Tahun Ajaran</span>
                        <span class="font-bold text-gray-800">: {{ $activeYear?->formatted_period ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-32 text-gray-600">No. Sertifikat</span>
                        <span class="font-mono font-bold text-gray-900">: {{ $assessment->certificate_number ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Informasi Skema Sertifikasi -->
            <div class="mb-4 bg-gray-50/70 p-3 rounded-lg border border-gray-200">
                <div class="text-[10px] font-extrabold text-gray-500 uppercase">Skema Sertifikasi / Klaster Kompetensi:</div>
                <h3 class="text-xs font-black text-gray-900 mt-0.5">{{ $assessment->scheme_name }}</h3>
                <div class="text-[10px] text-gray-600 mt-1">
                    Asesor / Penguji Eksternal: <span class="font-bold text-gray-800">{{ $assessment->assessor_name }}</span>
                </div>
            </div>

            <!-- Tabel Nilai Hasil UKK -->
            <div class="mb-6">
                <h4 class="text-[11px] font-bold text-gray-800 mb-2 uppercase tracking-wide">
                    A. Nilai Hasil Uji Kompetensi Kejuruan
                </h4>
                <table class="w-full text-left border-collapse border border-gray-400 text-[11px]">
                    <thead>
                        <tr class="bg-gray-100 text-gray-800 font-bold border-b border-gray-400">
                            <th class="p-2 border border-gray-400 w-10 text-center">No</th>
                            <th class="p-2 border border-gray-400">Komponen Penilaian Kejuruan</th>
                            <th class="p-2 border border-gray-400 w-24 text-center">Bobot</th>
                            <th class="p-2 border border-gray-400 w-28 text-center">Nilai Angka</th>
                            <th class="p-2 border border-gray-400 w-28 text-center">Nilai Terbobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($assessment->theory_score !== null)
                            <tr class="border-b border-gray-300">
                                <td class="p-2 border border-gray-300 text-center">1</td>
                                <td class="p-2 border border-gray-300 font-semibold">Ujian Teori Kejuruan</td>
                                <td class="p-2 border border-gray-300 text-center">30%</td>
                                <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($assessment->theory_score, 1) }}</td>
                                <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($assessment->theory_score * 0.30, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="border-b border-gray-300">
                            <td class="p-2 border border-gray-300 text-center">{{ $assessment->theory_score !== null ? 2 : 1 }}</td>
                            <td class="p-2 border border-gray-300 font-semibold">Ujian Praktik Kejuruan (Kinerja & Produk)</td>
                            <td class="p-2 border border-gray-300 text-center">{{ $assessment->theory_score !== null ? '70%' : '100%' }}</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">{{ number_format($assessment->practice_score, 1) }}</td>
                            <td class="p-2 border border-gray-300 text-center font-bold">
                                {{ number_format($assessment->theory_score !== null ? ($assessment->practice_score * 0.70) : $assessment->practice_score, 2) }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50/80 font-black border-t-2 border-gray-400">
                            <td colspan="3" class="p-2 border border-gray-400 text-right uppercase">Nilai Akhir UKK :</td>
                            <td colspan="2" class="p-2 border border-gray-400 text-center text-sm text-gray-900">
                                {{ number_format($assessment->final_score, 2) }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50/80 font-black">
                            <td colspan="3" class="p-2 border border-gray-400 text-right uppercase">Keputusan / Predikat Kelulusan :</td>
                            <td colspan="2" class="p-2 border border-gray-400 text-center text-sm text-indigo-900">
                                {{ $assessment->predicate }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Keterangan Standar Kelulusan UKK -->
            <div class="text-[9px] text-gray-500 bg-gray-50 p-2.5 rounded border border-gray-200 mb-6">
                <strong>Standar Predikat Kelulusan:</strong>
                <span class="ml-2">&bull; <strong>Sangat Kompeten</strong>: &ge; 85.00</span>
                <span class="ml-2">&bull; <strong>Kompeten</strong>: 70.00 - 84.99</span>
                <span class="ml-2">&bull; <strong>Belum Kompeten</strong>: &lt; 70.00</span>
            </div>
        </div>

        <!-- Tanda Tangan 3 Pihak Resmi -->
        <div class="pt-2 text-[10px] text-gray-900">
            <div class="flex justify-between items-start mb-10">
                <div class="text-center w-56">
                    <p>Asesor / Penguji Eksternal,</p>
                    <p class="font-bold">{{ $assessment->institution_name }}</p>
                    <div class="h-14"></div>
                    <p class="font-black text-gray-900 underline">{{ $assessment->assessor_name }}</p>
                    <p class="text-[9px] text-gray-600">Asesor Penguji</p>
                </div>

                <div class="text-center w-56">
                    <p>{{ $tenant->city ?? 'Jakarta' }}, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="font-bold">Penguji Internal / Wali Kelas,</p>
                    <div class="h-14"></div>
                    <p class="font-black text-gray-900 underline">{{ $class->waliKelas?->name ?? 'Penguji Internal' }}</p>
                    <p class="text-[9px] text-gray-600">NIP: {{ $class->waliKelas?->nip ?? '-' }}</p>
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
