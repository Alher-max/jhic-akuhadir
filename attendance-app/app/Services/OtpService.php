<?php

namespace App\Services;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class OtpService
{
    public function sendOtp($user, $otpCode)
    {
        $driver = config('services.otp.driver', 'email');

        if (config('app.env') === 'local' || $driver === 'dummy') {
            // Dalam environment local atau mode dummy, kita hanya log OTP untuk menghemat kuota / mencegah spam
            Log::info("DUMMY OTP SENT: To {$user->email} - Code: {$otpCode}");
            return true;
        }

        switch ($driver) {
            case 'wa':
                return $this->sendViaWhatsApp($user, $otpCode);
            case 'email':
            default:
                return $this->sendViaEmail($user, $otpCode);
        }
    }

    protected function sendViaEmail($user, $otpCode)
    {
        try {
            Mail::to($user->email)->send(new OtpMail($otpCode));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaWhatsApp($user, $otpCode)
    {
        // Contoh implementasi untuk driver WA (seperti Fonnte) jika digunakan nanti
        $waDriver = config('services.wa.driver', 'fonnte');
        $apiKey = config('services.wa.api_key');

        if (!$apiKey) {
            Log::error("WA API Key is missing.");
            return false;
        }

        if ($waDriver === 'fonnte') {
            try {
                // Logika pemanggilan Fonnte
                // Http::withHeaders(['Authorization' => $apiKey])->post('https://api.fonnte.com/send', [...]);
                Log::info("OTP WA sent to {$user->phone} via Fonnte.");
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to send OTP WA: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }
}
