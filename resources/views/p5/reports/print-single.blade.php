<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor P5 - {{ $student->name }} - {{ $project->title }}</title>
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

            .page-break {
                page-break-after: always;
                break-after: page;
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
                <h3 class="font-extrabold text-sm text-gray-900">Pratinjau Rapor P5: {{ $student->name }}</h3>
                <p class="text-[11px] text-gray-500">Kelas {{ $project->schoolClass->nama_kelas }} &bull; {{ $project->title }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition flex items-center gap-2">
                <span>🖨️ Cetak Lembar Rapor P5 (A4)</span>
            </button>
        </div>
    </div>

    <!-- The Report Sheet -->
    @include('p5.reports.partials.report-sheet')

</body>
</html>
