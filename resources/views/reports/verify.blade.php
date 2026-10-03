<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isValid ? 'Verifikasi Dokumen Resmi Rapor - HadirYuk' : 'Dokumen Tidak Ditemukan - HadirYuk' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4 flex flex-col items-center justify-center">

    <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="{{ $isValid ? 'bg-gradient-to-r from-emerald-700 to-teal-800' : 'bg-gradient-to-r from-rose-700 to-red-800' }} p-6 text-white text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $isValid ? 'bg-emerald-500/20 text-emerald-200 ring-4 ring-emerald-500/30' : 'bg-rose-500/20 text-rose-200 ring-4 ring-rose-500/30' }} mb-3">
                <span class="material-symbols-outlined text-4xl">
                    {{ $isValid ? 'verified' : 'gpp_bad' }}
                </span>
            </div>
            <h1 class="text-xl font-extrabold uppercase tracking-wide">
                {{ $isValid ? 'DOKUMEN RESMI TERVERIFIKASI' : 'DOKUMEN TIDAK VALID / TIDAK DITEMUKAN' }}
            </h1>
            <p class="text-xs text-white/80 mt-1">
                Layanan Verifikasi Integritas Rapor Digital HadirYuk
            </p>
        </div>

        @if($isValid)
            <!-- Verification Content -->
            <div class="p-6 space-y-6">
                
                <!-- Status Badge Alert -->
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                    <span class="material-symbols-outlined text-emerald-600 text-xl shrink-0 mt-0.5">check_circle</span>
                    <div class="text-xs text-emerald-900 leading-relaxed">
                        <strong class="font-bold">Keaslian Dokumen Terjamin:</strong>
                        Lembar Laporan Hasil Belajar (Rapor) ini terdaftar resmi pada basis data sekolah dan diterbitkan melalui sistem terpadu HadirYuk.
                    </div>
                </div>

                <!-- Identity Grid -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identitas Dokumen & Peserta Didik</h3>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div>
                            <span class="text-slate-500 block text-[11px]">Nama Siswa:</span>
                            <strong class="text-slate-900 font-bold text-sm">{{ $student->name }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">NISN / NIS:</span>
                            <span class="text-slate-800 font-semibold">{{ $student->nisn ?? $student->nis ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Sekolah / Institusi:</span>
                            <span class="text-slate-800 font-semibold">{{ $tenant->name ?? 'HadirYuk School' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Kelas & Fase:</span>
                            <span class="text-slate-800 font-semibold">{{ $schoolClass->nama_kelas }} (Fase {{ $schoolClass->fase ?? 'E' }})</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Tahun Ajaran:</span>
                            <span class="text-slate-800 font-semibold">{{ $academicYear->name }} • Sem. {{ $academicYear->semester }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Tanggal Penerbitan:</span>
                            <span class="text-slate-800 font-semibold">{{ $report->published_at ? $report->published_at->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Academic Summary -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ringkasan Hasil Belajar</h3>
                    
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-indigo-50 border border-indigo-100 p-3 rounded-2xl">
                            <span class="text-[10px] text-indigo-700 uppercase font-bold block">Rata-rata Nilai</span>
                            <strong class="text-xl font-black text-indigo-950">{{ number_format($averageScore, 2) }}</strong>
                        </div>
                        <div class="bg-teal-50 border border-teal-100 p-3 rounded-2xl">
                            <span class="text-[10px] text-teal-700 uppercase font-bold block">Total Mapel</span>
                            <strong class="text-xl font-black text-teal-950">{{ $grades->count() }}</strong>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-3 rounded-2xl">
                            <span class="text-[10px] text-amber-700 uppercase font-bold block">Absensi (S/I/A)</span>
                            <strong class="text-xl font-black text-amber-950">{{ $report->total_absence }} <span class="text-xs font-normal">hari</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Cryptographic Token Details -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[10px] text-slate-500 font-mono break-all">
                    <span class="font-bold text-slate-700 block mb-0.5">Verification Hash:</span>
                    <span>{{ $hash }}</span>
                </div>

            </div>
        @else
            <!-- Invalid / Not Found State -->
            <div class="p-8 text-center space-y-4">
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-900 leading-relaxed">
                    Kode hash dokumen tidak valid, tidak terdaftar pada pangkalan data kami, atau telah dinonaktifkan oleh administrator sekolah. Pastikan Anda memindai kode QR resmi yang tercetak pada lembar rapor fisik/digital HadirYuk.
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] font-mono text-slate-600 break-all">
                    Hash yang diuji: {{ $hash }}
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="bg-slate-50 p-4 border-t border-slate-200 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} HadirYuk • Platform Presensi, Akademik & Rapor Digital Multi-Tenant
        </div>

    </div>

</body>
</html>
