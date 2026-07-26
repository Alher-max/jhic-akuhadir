<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kehadiran - {{ $selectedMonth }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1a1a1a;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
        }
        td.text-left {
            text-align: left;
        }
        .score-good { color: #10b981; font-weight: bold; }
        .score-warn { color: #f59e0b; font-weight: bold; }
        .score-bad { color: #ef4444; font-weight: bold; }
        .alpha-highlight {
            color: #ef4444;
            font-weight: bold;
        }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: landscape; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Cetak Laporan</button>
    </div>

    <div class="header">
        <h1>Laporan Rekapitulasi Kehadiran</h1>
        <p>Institusi: <strong>{{ auth()->user()->tenant->name ?? 'HadirYuk' }}</strong> | Lokasi: <strong>{{ $locationName }}</strong> | Periode: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 200px;">Nama Anggota</th>
                <th>Lokasi / Cabang</th>
                <th>Hadir Tepat Waktu</th>
                <th>Terlambat</th>
                <th>Tingkat Kedisiplinan</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Tugas Luar</th>
                <th>Alpha (Tanpa Keterangan)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $r)
                <tr>
                    <td class="text-left">
                        <strong>{{ $r['name'] }}</strong><br>
                        <span style="color: #666; font-size: 10px;">{{ $r['email'] }}</span>
                    </td>
                    <td>{{ $r['location_name'] }}</td>
                    <td>{{ $r['present'] }}</td>
                    <td>{{ $r['late'] }}</td>
                    <td class="{{ $r['discipline_score'] >= 90 ? 'score-good' : ($r['discipline_score'] >= 75 ? 'score-warn' : 'score-bad') }}">
                        {{ $r['discipline_score'] }}%
                    </td>
                    <td>{{ $r['sick'] }}</td>
                    <td>{{ $r['permission'] }}</td>
                    <td>{{ $r['duty'] }}</td>
                    <td class="{{ $r['alpha'] > 0 ? 'alpha-highlight' : '' }}">
                        {{ $r['alpha'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Belum ada data anggota untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; font-size: 12px; color: #666;">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <p>Oleh: {{ auth()->user()->name }}</p>
    </div>

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
