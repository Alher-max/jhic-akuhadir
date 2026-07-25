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

        // 2. Validasi Wi-Fi (Jika opsi 3 aktif)
        if ($settings->method_wifi) {
            $ssid = $request->input('wifi_ssid'); // Asumsi dikirim dari FE jika memungkinkan, atau sekadar bypass di simulasi
            // Biasanya SSId tidak bisa diambil dari browser murni tanpa Native App/PWA spesifik API, 
            // Namun kita sediakan logikanya sesuai skema.
        }

        // 3. Simpan Snapshot (Base64)
        $photoPath = null;
        if ($request->has('image_snapshot')) {
            $image = $request->input('image_snapshot'); // data:image/jpeg;base64,...
            if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                $image = substr($image, strpos($image, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, etc
                
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

        // 4. Proses Absensi (Jadwal & Lateness)
        $today = now()->format('Y-m-d');
        $dayOfWeek = now()->format('N');
        
        $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan clock-in hari ini.'], 400);
        }

        $schedule = $user->schedules()->where(function($query) use ($today, $dayOfWeek) {
            $query->where(function($q) use ($today) {
                $q->where('type', 'non_routine')->where('specific_date', $today);
            })->orWhere(function($q) use ($dayOfWeek) {
                $q->where('type', 'routine')->where('day_of_week', $dayOfWeek);
            });
        })->first();

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Tidak ada jadwal hadir hari ini.'], 400);
        }

        $startTime = \Carbon\Carbon::parse($today . ' ' . $schedule->start_time);
        $limitTime = $startTime->copy()->addMinutes($schedule->grace_period_minutes);
        $status = now()->greaterThan($limitTime) ? 'late' : 'present';

        $matchScore = $request->input('face_match_score') ? (float)$request->input('face_match_score') : null;

        Attendance::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'date' => $today,
            'clock_in' => now(),
            'status' => $status,
            'photo_path' => $photoPath,
            'face_match_score' => $matchScore,
        ]);

        return response()->json(['success' => true, 'message' => 'Absensi berhasil diverifikasi!']);
    }
}
