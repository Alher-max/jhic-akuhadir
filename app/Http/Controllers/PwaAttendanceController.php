<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\AttendanceSetting;
use App\Models\Attendance;
use Illuminate\Support\Str;

class PwaAttendanceController extends Controller
{
    /**
     * Tampilkan halaman UI PWA Clock-In
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ambil pengaturan attendance untuk tenant pengguna saat ini
        $settings = AttendanceSetting::where('tenant_id', $user->tenant_id)->first();
        
        if (!$settings) {
            abort(403, 'Pengaturan presensi belum dikonfigurasi untuk tenant Anda.');
        }

        // Pastikan metode PWA aktif
        if (!$settings->method_pwa) {
            abort(403, 'Metode absensi mandiri (PWA) sedang dinonaktifkan oleh sekolah.');
        }

        $masterPhotoUrl = $user->master_photo ? asset('storage/' . $user->master_photo) : null;

        return view('pwa.clock-in', compact('settings', 'masterPhotoUrl'));
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $settings = AttendanceSetting::where('tenant_id', $user->tenant_id)->first();

        if (!$settings || !$settings->method_pwa) {
            return response()->json(['success' => false, 'message' => 'PWA tidak aktif.'], 403);
        }

        // 1. Validasi Geofencing (Jika ada latitude/longitude di pengaturan)
        if ($settings->latitude && $settings->longitude) {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');

            if (!$lat || !$lng) {
                return response()->json(['success' => false, 'message' => 'Akses lokasi wajib diaktifkan.'], 400);
            }

            $distance = $this->calculateDistance($lat, $lng, $settings->latitude, $settings->longitude);
            if ($distance > ($settings->radius_meters ?? 100)) {
                return response()->json(['success' => false, 'message' => 'Anda berada di luar radius sekolah.'], 400);
            }
        }

        // 2. Simpan Snapshot (Base64)
        $photoPath = null;
        if ($request->has('image_snapshot')) {
            $image = $request->input('image_snapshot');
            if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                $image = substr($image, strpos($image, ',') + 1);
                $type = strtolower($type[1]);
                
                if (in_array($type, ['jpg', 'jpeg', 'png'])) {
                    $image = base64_decode($image);
                    if ($image !== false) {
                        $fileName = Str::random(10) . '_' . time() . '.' . $type;
                        $path = 'attendances/' . $user->tenant_id . '/' . date('Y/m/d');
                        Storage::disk('public')->put($path . '/' . $fileName, $image);
                        $photoPath = $path . '/' . $fileName;
                    }
                }
            }
        }

        // 3. Proses Absensi (Jadwal & Lateness berdasarkan Arsitektur Baru)
        $today = now('Asia/Jakarta')->format('Y-m-d');
        
        $attendanceService = app(\App\Services\AttendanceService::class);
        $tenant = $user->tenant;
        if ($tenant && !app(\App\Services\AttendanceService::class)
            ->isAttendanceDayAllowed($tenant, $user, now('Asia/Jakarta'))) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi hari Minggu belum diaktifkan dan tidak ada jadwal resmi.',
            ], 422);
        }
        $isSessionBased = $tenant && $attendanceService->isSessionBasedMode($tenant);

        $classScheduleId = null;
        $attendanceType = 'school';
        $dayNameIndo = $attendanceService->getDayNameInIndonesian(now('Asia/Jakarta'));

        if ($isSessionBased) {
            $classSchedule = $attendanceService->findActiveSession($user, now('Asia/Jakarta'));
            $classScheduleId = $classSchedule?->id;

            if ($classScheduleId) {
                if ($attendanceService->hasAttendedClassSession($user, $today, $classScheduleId)) {
                    $sessionLabel = $classSchedule?->subject?->name ?? 'Sesi KBM';
                    return response()->json([
                        'success' => true,
                        'already_attended' => true,
                        'message' => "Anda sudah melakukan presensi untuk {$sessionLabel}."
                    ], 200);
                }
                $attendanceType = 'class';
            } else {
                if ($attendanceService->hasAttendedSchool($user, $today)) {
                    return response()->json([
                        'success' => true,
                        'already_attended' => true,
                        'message' => 'Anda sudah melakukan clock-in kedatangan sekolah hari ini.'
                    ], 200);
                }
            }

            $valResult = $attendanceService->validateSchoolAttendance($tenant, now('Asia/Jakarta')->format('H:i:s'), $dayNameIndo, $classSchedule);
            $status = $valResult['status'];
        } else {
            if ($attendanceService->hasAttendedSchool($user, $today)) {
                return response()->json([
                    'success' => true,
                    'already_attended' => true,
                    'message' => 'Anda sudah melakukan clock-in kedatangan sekolah hari ini.'
                ], 200);
            }

            $valResult = $attendanceService->validateSchoolAttendance($tenant, now('Asia/Jakarta')->format('H:i:s'), $dayNameIndo);
            $status = $valResult['status'];
        }

        $matchScore = $request->input('face_match_score') ? (float)$request->input('face_match_score') : null;

        // Tangkap IP Client & Validasi Wi-Fi Sekolah
        $clientIp = $request->ip();
        $isWifiVerified = false;

        $allowedIps = [];
        if ($settings && !empty($settings->biometric_ip_address)) {
            $allowedIps = array_merge($allowedIps, array_map('trim', explode(',', $settings->biometric_ip_address)));
        }

        $deviceIps = \App\Models\AttendanceDevice::where('tenant_id', $user->tenant_id)
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->toArray();
        $allowedIps = array_merge($allowedIps, $deviceIps);

        $envIps = env('SCHOOL_WIFI_IPS') ?: env('WIFI_ALLOWED_IPS') ?: config('services.wifi.allowed_ips');
        if ($envIps) {
            $allowedIps = array_merge($allowedIps, is_array($envIps) ? $envIps : array_map('trim', explode(',', $envIps)));
        }

        if (!empty($allowedIps) && in_array($clientIp, $allowedIps)) {
            $isWifiVerified = true;
        } elseif (in_array($clientIp, ['127.0.0.1', '::1']) || str_starts_with($clientIp, '192.168.') || str_starts_with($clientIp, '10.') || str_starts_with($clientIp, '172.16.')) {
            $isWifiVerified = true;
        }

        Attendance::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'attendance_type' => $attendanceType,
            'class_schedule_id' => $classScheduleId,
            'date' => $today,
            'clock_in' => now('Asia/Jakarta'),
            'status' => $status,
            'photo_path' => $photoPath,
            'face_match_score' => $matchScore,
            'ip_address' => $clientIp,
            'is_wifi_verified' => $isWifiVerified,
        ]);

        return response()->json(['success' => true, 'message' => 'Absensi berhasil diverifikasi!']);
    }
}
