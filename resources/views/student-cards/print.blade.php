<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Pelajar - {{ $cardConfig['school_name'] }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- EasyQRCode JS Library for reliable offline & client-side QR generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* CR80 Standard ID Card Dimensions (85.6mm x 53.98mm) */
        .cr80-card {
            width: 85.6mm;
            height: 53.98mm;
            border-radius: 3.5mm;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
            }

            .print-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 85.6mm) !important;
                gap: 6mm !important;
                justify-content: center !important;
            }
        }
    </style>
</head>
<body class="p-6 min-h-screen flex flex-col items-center">

    <!-- Floating Print Control Header (No Print) -->
    <div class="no-print w-full max-w-4xl bg-white border border-gray-200 shadow-md rounded-2xl p-4 mb-6 flex items-center justify-between">
        <div>
            <h1 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-id-card text-red-600"></i> Pratinjau Cetak Kartu Pelajar ({{ count($students) }} Kartu)
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Format CR80 Standard (85.6mm × 53.98mm) disesuaikan untuk kertas A4.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-print"></i> Cetak Sekarang
            </button>
        </div>
    </div>

    <!-- Cards Layout Grid -->
    <div class="print-grid grid grid-cols-1 md:grid-cols-2 gap-6 justify-center max-w-4xl">
        @forelse($students as $student)
            @php
                $template = $cardConfig['template'];
                $accent = $cardConfig['accent_color'];
                $qrCodeToken = $student->nisn ?? ($student->nis ?? 'STD-' . $student->id);
            @endphp

            <!-- Single CR80 Card -->
            <div class="cr80-card shadow-lg p-3 flex flex-col justify-between select-none
                @if($template === 'modern')
                    @if($accent === 'red') bg-gradient-to-br from-slate-900 via-slate-800 to-red-950 text-white border border-red-500/30
                    @elseif($accent === 'indigo') bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white border border-indigo-500/30
                    @elseif($accent === 'emerald') bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 text-white border border-emerald-500/30
                    @elseif($accent === 'amber') bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 text-white border border-amber-500/30
                    @else bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white border border-slate-700
                    @endif
                @elseif($template === 'classic')
                    @if($accent === 'red') bg-white text-gray-900 border-2 border-red-600
                    @elseif($accent === 'indigo') bg-white text-gray-900 border-2 border-indigo-600
                    @elseif($accent === 'emerald') bg-white text-gray-900 border-2 border-emerald-600
                    @elseif($accent === 'amber') bg-white text-gray-900 border-2 border-amber-500
                    @else bg-white text-gray-900 border-2 border-slate-800
                    @endif
                @else
                    bg-slate-50 text-gray-900 border border-gray-300
                @endif
            ">
                <!-- Card Header -->
                <div class="flex items-center justify-between border-b pb-1.5 
                    {{ $template === 'modern' ? 'border-white/15' : 'border-gray-200' }}">
                    <div class="flex items-center gap-1.5 truncate">
                        <div class="w-5 h-5 rounded bg-white/20 flex items-center justify-center text-[10px] font-black shrink-0
                            {{ $accent === 'red' ? 'text-red-500' : ($accent === 'indigo' ? 'text-indigo-400' : 'text-emerald-400') }}">
                            H
                        </div>
                        <div class="truncate">
                            <h2 class="text-[9.5px] font-black uppercase tracking-wider leading-none truncate">
                                {{ $cardConfig['school_name'] }}
                            </h2>
                            <span class="text-[7.5px] opacity-75 leading-none block">
                                {{ $cardConfig['card_title'] }}
                            </span>
                        </div>
                    </div>
                    <span class="text-[7.5px] font-bold px-1.5 py-0.5 rounded bg-black/10 shrink-0 ml-1">
                        {{ $cardConfig['academic_year'] }}
                    </span>
                </div>

                <!-- Card Body -->
                <div class="flex items-center justify-between gap-2 my-auto">
                    <!-- Photo Avatar -->
                    <div class="w-12 h-14 rounded bg-gray-200 border border-black/10 overflow-hidden shrink-0 flex flex-col items-center justify-center text-gray-400">
                        @if($student->avatar || $student->master_photo)
                            <img src="{{ asset('storage/' . ($student->avatar ?? $student->master_photo)) }}" 
                                 alt="{{ $student->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-user text-xl"></i>
                        @endif
                    </div>

                    <!-- Student Details -->
                    <div class="flex-1 min-w-0 text-left space-y-0.5 pl-0.5">
                        <h3 class="text-[10.5px] font-bold truncate leading-tight">
                            {{ $student->name }}
                        </h3>
                        <div class="text-[8.5px] leading-tight opacity-90 space-y-0.2">
                            <p><span class="font-semibold">NISN:</span> {{ $student->nisn ?? '-' }}</p>
                            <p><span class="font-semibold">NIS:</span> {{ $student->nis ?? '-' }}</p>
                            <p class="truncate"><span class="font-semibold">Kelas:</span> {{ $student->schoolClass->full_name ?? 'Tanpa Kelas' }}</p>
                        </div>
                    </div>

                    <!-- Student Unique QR Code (Scannable for Attendance) -->
                    <div class="w-13 h-13 bg-white p-1 rounded border border-gray-200 shrink-0 flex flex-col items-center justify-center shadow-xs">
                        <div class="qr-container w-11 h-11" data-code="{{ $qrCodeToken }}"></div>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="text-[7px] text-center pt-1 border-t truncate opacity-70 
                    {{ $template === 'modern' ? 'border-white/15' : 'border-gray-200' }}">
                    {{ $cardConfig['footer_text'] }}
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center p-8 bg-white rounded-2xl border text-gray-400">
                Tidak ada kartu siswa yang dipilih untuk dicetak.
            </div>
        @endforelse
    </div>

    <!-- Client-Side QR Code Generation Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.qr-container').forEach(function (element) {
                var code = element.getAttribute('data-code');
                if (code) {
                    new QRCode(element, {
                        text: code,
                        width: 44,
                        height: 44,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            });
        });
    </script>
</body>
</html>
