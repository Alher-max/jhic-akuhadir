<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $now = now('Asia/Jakarta');
        $today = $now->format('Y-m-d');

        $tenant = $user->tenant;
        $attendanceService = app(\App\Services\AttendanceService::class);
        if ($tenant && !$attendanceService->isAttendanceDayAllowed($tenant, $user, $now)) {
            return redirect()->back()->with('error', 'Presensi hari Minggu belum diaktifkan dan tidak ada jadwal resmi.');
        }
        if ($tenant && $tenant->attendance_method === 'gps') {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');

            if (!$lat || !$lng) {
                return redirect()->back()->with('error', 'Akses lokasi (GPS) wajib diaktifkan untuk melakukan absensi.');
            }

            $isFallback = $request->input('is_fallback') == '1';
            
            if (!$isFallback) {
                $actualDistance = 0;
                $isValidLocation = false;
                
                if ($user->location_id) {
                    $location = $user->location;
                    if ($location && $location->latitude && $location->longitude) {
                        $actualDistance = $this->calculateDistance($lat, $lng, $location->latitude, $location->longitude);
                        if ($actualDistance <= ($location->radius ?? 100)) $isValidLocation = true;
                    }
                } else {
                    $clientLocations = $tenant->locations()->where('type', 'client')->get();
                    if ($clientLocations->count() > 0) {
                        foreach ($clientLocations as $loc) {
                            if ($loc->latitude && $loc->longitude) {
                                $actualDistance = $this->calculateDistance($lat, $lng, $loc->latitude, $loc->longitude);
                                if ($actualDistance <= ($loc->radius ?? 100)) {
                                    $isValidLocation = true;
                                    break;
                                }
                            }
                        }
                    } else {
                        // Default titik acuan sekolah / Tenant
                        $targetLat = $tenant->gps_lat ?? -7.8732;
                        $targetLng = $tenant->gps_lng ?? 110.3956;
                        $targetRadius = $tenant->gps_radius ?? 100;
                        
                        $actualDistance = $this->calculateDistance($lat, $lng, $targetLat, $targetLng);
                        if ($actualDistance <= $targetRadius) $isValidLocation = true;
                    }
                }

                if (!$isValidLocation) {
                    $formattedDistance = number_format($actualDistance, 0, ',', '.');
                    return redirect()->back()->with('error', "Presensi Gagal: Anda berada di luar radius lokasi sekolah (Jarak Anda: {$formattedDistance} meter dari titik sekolah).");
                }
            }
        }

        $tenant = $user->tenant;
        $isSessionBased = $tenant && $attendanceService->isSessionBasedMode($tenant);

        $classScheduleId = null;
        $attendanceType = 'school';
        $dayNameIndo = $attendanceService->getDayNameInIndonesian(now());

        if ($isSessionBased) {
            $classSchedule = $attendanceService->findActiveSession($user, now());
            $classScheduleId = $classSchedule?->id;

            if ($classScheduleId) {
                if ($attendanceService->hasAttendedClassSession($user, $today, $classScheduleId)) {
                    $sessionLabel = $classSchedule?->subject?->name ?? 'Sesi KBM';
                    return redirect()->back()->with('error', "Anda sudah melakukan presensi untuk {$sessionLabel} hari ini.");
                }
                $attendanceType = 'class';
            } else {
                if ($attendanceService->hasAttendedSchool($user, $today)) {
                    return redirect()->back()->with('error', 'Anda sudah melakukan clock-in kedatangan sekolah hari ini.');
                }
            }

            $valResult = $attendanceService->validateSchoolAttendance($tenant, now()->format('H:i:s'), $dayNameIndo, $classSchedule);
            $status = $valResult['status'];
        } else {
            if ($attendanceService->hasAttendedSchool($user, $today)) {
                return redirect()->back()->with('error', 'Anda sudah melakukan clock-in kedatangan sekolah hari ini.');
            }

            $valResult = $attendanceService->validateSchoolAttendance($tenant, now()->format('H:i:s'), $dayNameIndo);
            $status = $valResult['status'];
        }

        // Handle Base64 Photo
        $photoPath = null;
        if ($request->filled('image_data')) {
            $imageData = $request->input('image_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new \Exception('invalid image type');
                }
                $imageData = str_replace(' ', '+', $imageData);
                $imageName = 'presensi/' . $user->tenant_id . '/' . $user->id . '_' . time() . '.' . $type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($imageName, base64_decode($imageData));
                $photoPath = $imageName;
            }
        }

        $notes = null;
        if ($request->input('is_fallback') == '1') {
            $notes = 'Presensi menggunakan fitur Upload Foto Manual (Fallback).';
        }

        $clientIp = $request->ip();
        $isWifiVerified = in_array($clientIp, ['127.0.0.1', '::1']) || str_starts_with($clientIp, '192.168.') || str_starts_with($clientIp, '10.');

        Attendance::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'attendance_type' => $attendanceType,
            'class_schedule_id' => $classScheduleId,
            'date' => $today,
            'clock_in' => now('Asia/Jakarta'),
            'status' => $status,
            'photo_path' => $photoPath,
            'notes' => $notes,
            'ip_address' => $clientIp,
            'is_wifi_verified' => $isWifiVerified,
        ]);

        return redirect()->back()->with('success', 'Berhasil masuk (clock-in).');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $now = now('Asia/Jakarta');
        $today = $now->format('Y-m-d');

        $tenant = $user->tenant;
        if ($tenant && $tenant->attendance_method === 'gps') {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');

            if (!$lat || !$lng) {
                return redirect()->back()->with('error', 'Akses lokasi (GPS) wajib diaktifkan untuk melakukan absensi.');
            }

            $isFallback = $request->input('is_fallback') == '1';
            
            if (!$isFallback) {
                $actualDistance = 0;
                $isValidLocation = false;
                
                if ($user->location_id) {
                    $location = $user->location;
                    if ($location && $location->latitude && $location->longitude) {
                        $actualDistance = $this->calculateDistance($lat, $lng, $location->latitude, $location->longitude);
                        if ($actualDistance <= ($location->radius ?? 100)) $isValidLocation = true;
                    }
                } else {
                    $clientLocations = $tenant->locations()->where('type', 'client')->get();
                    if ($clientLocations->count() > 0) {
                        foreach ($clientLocations as $loc) {
                            if ($loc->latitude && $loc->longitude) {
                                $actualDistance = $this->calculateDistance($lat, $lng, $loc->latitude, $loc->longitude);
                                if ($actualDistance <= ($loc->radius ?? 100)) {
                                    $isValidLocation = true;
                                    break;
                                }
                            }
                        }
                    } else {
                        $targetLat = $tenant->gps_lat ?? -7.8732;
                        $targetLng = $tenant->gps_lng ?? 110.3956;
                        $targetRadius = $tenant->gps_radius ?? 100;
                        
                        $actualDistance = $this->calculateDistance($lat, $lng, $targetLat, $targetLng);
                        if ($actualDistance <= $targetRadius) $isValidLocation = true;
                    }
                }

                if (!$isValidLocation) {
                    $formattedDistance = number_format($actualDistance, 0, ',', '.');
                    return redirect()->back()->with('error', "Presensi Gagal: Anda berada di luar radius lokasi sekolah (Jarak Anda: {$formattedDistance} meter dari titik sekolah).");
                }
            }
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->where(function($q) {
                $q->where('attendance_type', 'school')
                  ->orWhereNull('class_schedule_id');
            })
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Anda belum melakukan clock-in hari ini.');
        }

        if ($attendance->clock_out) {
            return redirect()->back()->with('error', 'Anda sudah melakukan clock-out hari ini.');
        }

        $attendance->update([
            'clock_out' => now('Asia/Jakarta'),
        ]);

        return redirect()->back()->with('success', 'Berhasil pulang (clock-out).');
    }
}
