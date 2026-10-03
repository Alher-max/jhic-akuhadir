<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Rapor Kelas {{ $schoolClass->nama_kelas }} - {{ $academicYear->name }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- QRCode.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
                page-break-after: always !important;
                break-after: page !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
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
                <h3 class="font-extrabold text-sm text-gray-900">Cetak Massal Rapor: Kelas {{ $schoolClass->nama_kelas }}</h3>
                <p class="text-[11px] text-gray-500">Total: {{ $cardItems->count() }} Siswa • TA {{ $academicYear->name }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition flex items-center gap-2">
                <span>🖨️ Cetak Semua Rapor Rombel (A4)</span>
            </button>
        </div>
    </div>

    <!-- Loop All Report Sheets with Page Break -->
    @forelse($cardItems as $item)
        @include('reports.partials.report-sheet', $item)

        @if(!$loop->last)
            <div class="page-break w-full my-8 border-b-2 border-dashed border-gray-300 no-print"></div>
        @endif
    @empty
        <div class="bg-white p-12 rounded-2xl border border-gray-200 text-center max-w-md">
            <p class="text-sm font-bold text-gray-700">Tidak ada lembar rapor yang tersedia untuk dicetak pada kelas ini.</p>
        </div>
    @endforelse

    <!-- Client-Side QRCode Rendering Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.qr-container').forEach(function (element) {
                var code = element.getAttribute('data-code');
                if (code && typeof QRCode !== 'undefined') {
                    new QRCode(element, {
                        text: code,
                        width: 56,
                        height: 56,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            });

            // Optional auto print triggered if requested via ?autoprint=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                setTimeout(function() {
                    window.print();
                }, 600);
            }
        });
    </script>
</body>
</html>
