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
        $now = now();
        $today = $now->format('Y-m-d');
        $dayOfWeek = $now->format('N');

        $tenant = $user->tenant;
        if ($tenant && $tenant->attendance_method === 'gps') {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');

            if (!$lat || !$lng) {
                return redirect()->back()->with('error', 'Akses lokasi (GPS) wajib diaktifkan untuk melakukan absensi.');
            }

            $isFallback = $request->input('is_fallback') == '1';
            
            // Bypass geofencing for fallback if required (based on user request)
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
                        // Default koordinat SMPN 1 Pleret / Acuan Tenant
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

        // Check if attendance already exists for today
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah melakukan clock-in hari ini.');
        }

        // Find applicable schedule for the user
        $schedule = $user->schedules()->where(function($query) use ($today, $dayOfWeek) {
            $query->where(function($q) use ($today) {
                $q->where('type', 'non_routine')->where('specific_date', $today);
            })->orWhere(function($q) use ($dayOfWeek) {
                $q->where('type', 'routine')->where('day_of_week', $dayOfWeek);
            });
        })->first();

        // Fallback: jika tidak ada jadwal di DB, gunakan jadwal mock agar testing presensi tetap bisa berjalan
        $isMockSchedule = false;
        if (!$schedule) {
            // Cek apakah ini jam kerja normal (06:00 - 23:59)
            $currentHour = now()->hour;
            if ($currentHour >= 6) {
                $isMockSchedule = true;
                $schedule = (object)[
                    'start_time'         => '06:00:00',
                    'end_time'           => '23:59:00',
                    'grace_period_minutes' => 60,
                ];
            } else {
                return redirect()->back()->with('error', 'Anda tidak memiliki jadwal wajib hadir hari ini.');
            }
        }

        // Check lateness
        $startTime = \Carbon\Carbon::parse($today . ' ' . $schedule->start_time);
        $limitTime = $startTime->copy()->addMinutes($schedule->grace_period_minutes);
        $status = now()->greaterThan($limitTime) ? 'late' : 'present';

        // Handle Base64 Photo
        $photoPath = null;
        if ($request->filled('image_data')) {
            $imageData = $request->input('image_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, jpeg
                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new \Exception('invalid image type');
                }
                $imageData = str_replace(' ', '+', $imageData);
                $imageName = 'presensi/' . $user->tenant_id . '/' . $user->id . '_' . time() . '.' . $type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($imageName, base64_decode($imageData));
                $photoPath = $imageName;
            }
        }

        // Check for Fallback mode
        $notes = null;
        if ($request->input('is_fallback') == '1') {
            $notes = 'Presensi menggunakan fitur Upload Foto Manual (Fallback).';
        }

        Attendance::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'date' => $today,
            'clock_in' => now(),
            'status' => $status,
            'photo_path' => $photoPath,
            'notes' => $notes,
        ]);

        return redirect()->back()->with('success', 'Berhasil masuk (clock-in).');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $now = now();
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
                        // Default koordinat SMPN 1 Pleret / Acuan Tenant
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
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Anda belum melakukan clock-in hari ini.');
        }

        if ($attendance->clock_out) {
            return redirect()->back()->with('error', 'Anda sudah melakukan clock-out hari ini.');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->back()->with('success', 'Berhasil pulang (clock-out).');
    }
}
