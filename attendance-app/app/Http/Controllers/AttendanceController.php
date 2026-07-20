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

            $isValidLocation = false;
            if ($user->location_id) {
                $location = $user->location;
                if ($location && $location->latitude && $location->longitude) {
                    $distance = $this->calculateDistance($lat, $lng, $location->latitude, $location->longitude);
                    if ($distance <= ($location->radius ?? 100)) $isValidLocation = true;
                }
            } else {
                $clientLocations = $tenant->locations()->where('type', 'client')->get();
                if ($clientLocations->count() > 0) {
                    foreach ($clientLocations as $loc) {
                        if ($loc->latitude && $loc->longitude) {
                            $distance = $this->calculateDistance($lat, $lng, $loc->latitude, $loc->longitude);
                            if ($distance <= ($loc->radius ?? 100)) {
                                $isValidLocation = true;
                                break;
                            }
                        }
                    }
                } else {
                    $distance = $this->calculateDistance($lat, $lng, $tenant->gps_lat, $tenant->gps_lng);
                    if ($distance <= ($tenant->gps_radius ?? 100)) $isValidLocation = true;
                }
            }

            if (!$isValidLocation) {
                return redirect()->back()->with('error', 'Absensi gagal. Anda berada di luar radius lokasi resmi institusi atau klien.');
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

        if (!$schedule) {
            return redirect()->back()->with('error', 'Anda tidak memiliki jadwal wajib hadir hari ini.');
        }

        // Check lateness
        $startTime = \Carbon\Carbon::parse($today . ' ' . $schedule->start_time);
        $limitTime = $startTime->copy()->addMinutes($schedule->grace_period_minutes);
        $status = now()->greaterThan($limitTime) ? 'late' : 'present';

        Attendance::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'date' => $today,
            'clock_in' => now(),
            'status' => $status,
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

            $isValidLocation = false;
            if ($user->location_id) {
                $location = $user->location;
                if ($location && $location->latitude && $location->longitude) {
                    $distance = $this->calculateDistance($lat, $lng, $location->latitude, $location->longitude);
                    if ($distance <= ($location->radius ?? 100)) $isValidLocation = true;
                }
            } else {
                $clientLocations = $tenant->locations()->where('type', 'client')->get();
                if ($clientLocations->count() > 0) {
                    foreach ($clientLocations as $loc) {
                        if ($loc->latitude && $loc->longitude) {
                            $distance = $this->calculateDistance($lat, $lng, $loc->latitude, $loc->longitude);
                            if ($distance <= ($loc->radius ?? 100)) {
                                $isValidLocation = true;
                                break;
                            }
                        }
                    }
                } else {
                    $distance = $this->calculateDistance($lat, $lng, $tenant->gps_lat, $tenant->gps_lng);
                    if ($distance <= ($tenant->gps_radius ?? 100)) $isValidLocation = true;
                }
            }

            if (!$isValidLocation) {
                return redirect()->back()->with('error', 'Absensi gagal. Anda berada di luar radius lokasi resmi institusi atau klien.');
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
