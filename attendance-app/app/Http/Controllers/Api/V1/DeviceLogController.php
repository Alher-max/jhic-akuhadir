<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Models\ActivitySchedule;
use App\Models\AttendanceLog;
use App\Jobs\ProcessAttendanceWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeviceLogController extends Controller
{
    public function push(Request $request)
    {
        // Expected payload format:
        // { "serial_number": "M100-XYZ", "pin": "1002", "punch_time": "2026-07-19 08:20:15" }
        $validated = $request->validate([
            'serial_number' => 'required|string',
            'pin' => 'required|string',
            'punch_time' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $device = Device::where('serial_number', $validated['serial_number'])->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // M100 pushes logs. Find the user by pin within the device's tenant
        $user = User::withoutGlobalScopes()
            ->where('tenant_id', $device->tenant_id)
            ->where('pin', $validated['pin'])
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found for this PIN'], 404);
        }

        $punchTime = Carbon::parse($validated['punch_time']);
        $lockKey = "attendance_lock:{$device->tenant_id}:{$user->id}";

        // Double-tap prevention (5 minutes)
        $lock = Cache::lock($lockKey, 300);

        if (!$lock->get()) {
            return response()->json(['message' => 'Duplicate log ignored (Double-tap prevention)'], 429);
        }

        try {
            // Determine active schedule
            // For simplicity, we find a schedule where the punch_time is within the schedule's day and bounds
            $currentTimeString = $punchTime->format('H:i:s');
            
            // Note: Since this is an agnostic backend, we'll try to find an applicable schedule
            $schedule = ActivitySchedule::withoutGlobalScopes()
                ->where('tenant_id', $device->tenant_id)
                ->where('start_time', '<=', $currentTimeString)
                ->orderBy('start_time', 'desc')
                ->first();

            $status = 'tepat_waktu';
            $punchType = 'in'; // Simplified for this context, ideally determined by schedule or last punch

            if ($schedule) {
                $scheduleStart = Carbon::parse($punchTime->format('Y-m-d') . ' ' . $schedule->start_time);
                $tolerance = $schedule->late_tolerance_minutes;

                if ($punchTime->gt($scheduleStart->copy()->addMinutes($tolerance))) {
                    $status = 'terlambat';
                }
            }

            // Create log
            $log = AttendanceLog::withoutGlobalScopes()->create([
                'tenant_id' => $device->tenant_id,
                'user_id' => $user->id,
                'device_id' => $device->id,
                'activity_schedule_id' => $schedule ? $schedule->id : null,
                'punch_time' => $punchTime,
                'punch_type' => $punchType,
                'status' => $status,
            ]);

            // Dispatch Webhook Job
            $payload = [
                'event' => 'attendance.finalized',
                'tenant_id' => $device->tenant_id,
                'user' => [
                    'id' => $user->id,
                    'pin' => $user->pin,
                    'name' => $user->name,
                ],
                'schedule' => [
                    'name' => $schedule ? $schedule->name : 'N/A',
                    'status' => $status,
                ],
                'device' => [
                    'location' => $device->location_name,
                ],
                'punch_time' => $punchTime->format('Y-m-d H:i:s'),
            ];

            ProcessAttendanceWebhook::dispatch($payload);

            return response()->json(['message' => 'Log processed successfully'], 201);
        } catch (\Exception $e) {
            $lock->release(); // Release lock on failure
            Log::error('Device Log Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
