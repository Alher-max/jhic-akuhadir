<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StudentReport;
use App\Models\SubjectGrade;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Sanitasi nomor telepon ke format internasional standar (628...).
     *
     * @param string|null $phone
     * @return string|null
     */
    public function sanitizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', trim($phone));

        if (empty($cleaned)) {
            return null;
        }

        // Ubah awalan '0' menjadi '62' (misal: 081234567890 -> 6281234567890)
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            // Ubah awalan langsung '8' menjadi '628...'
            $cleaned = '62' . $cleaned;
        }

        // Standar nomor WhatsApp Indonesia minimal 10 digit (628xxxxxxx)
        if (!str_starts_with($cleaned, '62') || strlen($cleaned) < 10) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Menyusun template pesan WhatsApp resmi pemberitahuan penerbitan rapor digital.
     *
     * @param StudentReport $report
     * @return string
     */
    public function formatReportPublishedMessage(StudentReport $report): string
    {
        $report->loadMissing(['student.profile', 'schoolClass', 'academicYear', 'tenant']);

        if (empty($report->verification_hash)) {
            $report->generateVerificationHash();
        }

        $tenantName = $report->tenant?->name ?? 'Satuan Pendidikan';
        $studentName = $report->student?->name ?? '-';
        $nisn = $report->student?->nisn ?: '-';
        $className = $report->schoolClass?->nama_kelas ?? '-';

        $semesterName = $report->academicYear?->semester_name 
            ?? ($report->academicYear?->semester === '1' ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)');
        $yearName = $report->academicYear?->name ?? '-';

        // Hitung rata-rata nilai mata pelajaran semester aktif
        $avgScore = SubjectGrade::where('tenant_id', $report->tenant_id)
            ->where('academic_year_id', $report->academic_year_id)
            ->where('class_id', $report->class_id)
            ->where('student_id', $report->student_id)
            ->avg('score');

        $formattedAvg = $avgScore !== null ? number_format((float) $avgScore, 2, ',', '.') : '-';

        $sick = (int) ($report->sick_count ?? 0);
        $permission = (int) ($report->permission_count ?? 0);
        $alpha = (int) ($report->alpha_count ?? 0);

        $verificationUrl = $report->verification_url;

        return "Yth. Orang Tua/Wali dari {$studentName},\n\n"
            . "Pemberitahuan Resmi Penerbitan Rapor Digital {$tenantName}\n"
            . "Tahun Ajaran {$yearName} - {$semesterName}:\n\n"
            . "• Nama Siswa : {$studentName}\n"
            . "• NISN : {$nisn}\n"
            . "• Kelas : {$className}\n"
            . "• Rata-rata Nilai : {$formattedAvg}\n"
            . "• Kehadiran : Sakit ({$sick} hari), Izin ({$permission} hari), Alpa ({$alpha} hari)\n\n"
            . "Rapor digital ananda telah resmi dipublikasikan dan dapat diakses serta diverifikasi melalui tautan resmi berikut:\n"
            . "{$verificationUrl}\n\n"
            . "Terima kasih atas perhatian dan kerja sama Bapak/Ibu.\n"
            . "— {$tenantName}";
    }

    /**
     * Mengumpulkan seluruh nomor telepon penerima yang valid untuk rapor siswa.
     *
     * @param StudentReport $report
     * @return array<int, string>
     */
    public function getRecipientPhones(StudentReport $report): array
    {
        $report->loadMissing(['student.parents.profile', 'student.parent.profile', 'student.profile']);
        $student = $report->student;

        if (!$student) {
            return [];
        }

        $candidates = [];

        // 1. Nomor telepon orang tua langsung pada profil siswa
        if (!empty($student->parent_phone)) {
            $candidates[] = $student->parent_phone;
        }

        // 2. Nomor telepon dari relasi orang tua (many-to-many parent_student)
        if ($student->relationLoaded('parents') && $student->parents->isNotEmpty()) {
            foreach ($student->parents as $parent) {
                if (!empty($parent->parent_phone)) {
                    $candidates[] = $parent->parent_phone;
                }
                if (!empty($parent->profile?->phone_number)) {
                    $candidates[] = $parent->profile->phone_number;
                }
            }
        }

        // 3. Nomor telepon dari relasi parent tunggal (parent_id)
        if ($student->parent) {
            if (!empty($student->parent->parent_phone)) {
                $candidates[] = $student->parent->parent_phone;
            }
            if (!empty($student->parent->profile?->phone_number)) {
                $candidates[] = $student->parent->profile->phone_number;
            }
        }

        // 4. Nomor telepon siswa sendiri jika terdaftar
        if (!empty($student->profile?->phone_number)) {
            $candidates[] = $student->profile->phone_number;
        }

        $sanitizedList = [];
        foreach ($candidates as $cand) {
            $cleaned = $this->sanitizePhoneNumber($cand);
            if ($cleaned !== null) {
                $sanitizedList[] = $cleaned;
            }
        }

        return array_values(array_unique($sanitizedList));
    }

    /**
     * Mengirim pesan WhatsApp ke nomor tujuan melalui gateway API yang dikonfigurasi.
     *
     * @param string $targetPhone
     * @param string $message
     * @return bool
     */
    public function send(string $targetPhone, string $message): bool
    {
        $sanitized = $this->sanitizePhoneNumber($targetPhone);
        if (empty($sanitized)) {
            Log::warning("WhatsApp notification skipped: invalid phone number [{$targetPhone}]");
            return false;
        }

        $apiKey = config('services.wa.api_key')
            ?: config('services.whatsapp.api_key')
            ?: config('services.wa.token');

        if (empty($apiKey) && app()->environment('testing')) {
            $apiKey = 'test-wa-token';
        }

        if (empty($apiKey)) {
            Log::warning("WhatsApp gateway skipped: API Key not configured for [{$sanitized}]");
            return false;
        }

        $gatewayUrl = config('services.wa.gateway_url') ?: 'https://api.fonnte.com/send';

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $apiKey,
                ])
                ->post($gatewayUrl, [
                    'target' => $sanitized,
                    'message' => $message,
                ]);

            if (!$response->successful()) {
                Log::warning("WhatsApp gateway returned error status [{$response->status()}] for phone {$sanitized}: " . $response->body());
                return false;
            }

            Log::info("WhatsApp report notification successfully dispatched to {$sanitized}");
            return true;
        } catch (\Throwable $e) {
            Log::warning("WhatsApp gateway failure for phone {$sanitized}: " . $e->getMessage());
            return false;
        }
    }
}
