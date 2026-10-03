<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\StudentReport;
use App\Services\WhatsAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReportPublishedWhatsAppNotificationJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param StudentReport $report
     */
    public function __construct(
        public StudentReport $report
    ) {}

    /**
     * Helper sanitasi nomor telepon untuk kemudahan testing dan pemanggilan terisolasi.
     *
     * @param string|null $phone
     * @return string|null
     */
    public function sanitizePhone(?string $phone): ?string
    {
        return app(WhatsAppNotificationService::class)->sanitizePhoneNumber($phone);
    }

    /**
     * Helper penyusunan pesan WhatsApp untuk kemudahan pengujian unit/fitur.
     *
     * @param StudentReport $report
     * @return string
     */
    public function formatMessage(StudentReport $report): string
    {
        return app(WhatsAppNotificationService::class)->formatReportPublishedMessage($report);
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppNotificationService $service): void
    {
        try {
            // Ambil seluruh nomor tujuan yang valid (orang tua dan siswa)
            $recipients = $service->getRecipientPhones($this->report);

            if (empty($recipients)) {
                Log::info("SendReportPublishedWhatsAppNotificationJob: Tidak ada nomor telepon terdaftar untuk rapor ID [{$this->report->id}] siswa [{$this->report->student_id}]. Job selesai secara graceful.");
                return;
            }

            // Susun template pesan notifikasi resmi
            $message = $service->formatReportPublishedMessage($this->report);

            foreach ($recipients as $recipientPhone) {
                try {
                    $service->send($recipientPhone, $message);
                } catch (\Throwable $e) {
                    // Isolasi kegagalan per nomor agar tidak menghentikan penerima lain
                    Log::warning("Gagal mengirim notifikasi WhatsApp rapor ke {$recipientPhone}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            // Tangkap seluruh kegagalan gateway tak terduga agar tidak melempar unhandled exception
            Log::warning("Kegagalan menyeluruh pada SendReportPublishedWhatsAppNotificationJob untuk rapor ID [{$this->report->id}]: " . $e->getMessage());
        }
    }
}
