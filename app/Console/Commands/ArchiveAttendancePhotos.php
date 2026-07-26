<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveAttendancePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:archive-photos {--days=90 : Batas umur foto presensi (dalam hari)} {--delete : Hapus berkas fisik dan kosongkan photo_path di DB setelah di-zip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arsip foto presensi harian lama ke dalam file ZIP dan opsi pembersihan storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) ($this->option('days') ?: 90);
        $shouldDelete = (bool) $this->option('delete');

        $cutoffDate = Carbon::now()->subDays($days);
        $monthYear = Carbon::now()->format('Y_m');

        $this->info("=== PROSES ARSIP FOTO PRESENSI (> {$days} HARI / SEBELUM " . $cutoffDate->format('Y-m-d') . ") ===");

        $attendances = Attendance::whereNotNull('photo_path')
            ->where('photo_path', '!=', '')
            ->whereDate('date', '<', $cutoffDate)
            ->get();

        if ($attendances->isEmpty()) {
            $this->info("Tidak ada foto presensi yang lebih tua dari {$days} hari.");
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$attendances->count()} record presensi dengan foto.");

        // Menyiapkan folder & file ZIP
        $archivesDir = storage_path('app/archives');
        if (!file_exists($archivesDir)) {
            mkdir($archivesDir, 0755, true);
        }

        $zipFilename = "arsip_foto_{$monthYear}_" . time() . ".zip";
        $zipPath = $archivesDir . DIRECTORY_SEPARATOR . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Gagal membuat file ZIP di: {$zipPath}");
            return Command::FAILURE;
        }

        $archivedCount = 0;
        $totalBytesSaved = 0;

        $progressBar = $this->output->createProgressBar($attendances->count());
        $progressBar->start();

        foreach ($attendances as $attendance) {
            $relativePath = $attendance->photo_path;
            $fullPath = null;
            
            if (Storage::disk('public')->exists($relativePath)) {
                $fullPath = Storage::disk('public')->path($relativePath);
            } elseif (file_exists(storage_path('app/public/' . $relativePath))) {
                $fullPath = storage_path('app/public/' . $relativePath);
            }

            if ($fullPath && file_exists($fullPath)) {
                $fileContent = @file_get_contents($fullPath);
                if ($fileContent !== false) {
                    $fileSize = strlen($fileContent);
                    $entryName = "user_{$attendance->user_id}_" . date('Ymd', strtotime($attendance->date)) . "_" . basename($relativePath);
                    
                    $zip->addFromString($entryName, $fileContent);
                    $archivedCount++;
                    $totalBytesSaved += $fileSize;

                    if ($shouldDelete) {
                        Storage::disk('public')->delete($relativePath);
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                        $attendance->update(['photo_path' => null]);
                    }
                }
            }

            $progressBar->advance();
        }

        @$zip->close();
        $progressBar->finish();
        $this->newLine(2);

        $mbSaved = round($totalBytesSaved / (1024 * 1024), 2);

        $this->info("✅ BERHASIL MENGARSIP {$archivedCount} FOTO!");
        $this->info("📦 File ZIP Arsip : {$zipPath}");
        $this->info("💾 Total Ukuran  : {$mbSaved} MB");
        if ($shouldDelete) {
            $this->warn("🗑️ Berkas fisik foto di storage telah dibersihkan & photo_path di DB di-set NULL.");
        } else {
            $this->comment("💡 Catatan: Berkas fisik foto di storage tidak dihapus (Gunakan flag --delete untuk menghapus berkas fisik).");
        }

        return Command::SUCCESS;
    }
}
