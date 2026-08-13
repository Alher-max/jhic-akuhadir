<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\BiometricRawLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricPushController extends Controller
{
    /**
     * Handle incoming biometric data push from devices (POST/GET).
     */
    public function push(Request $request)
    {
        // Extract serial number from various possible sources
        $serialNumber = $this->extractSerialNumber($request);
        
        if (!$serialNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Serial number not found in request',
            ], 400);
        }

        // Get client IP
        $ipAddress = $request->ip();

        // Find or create device (Auto-Discovery)
        $device = BiometricDevice::firstOrCreate(
            ['serial_number' => $serialNumber],
            [
                'school_id' => null, // Will be set when claimed
                'device_name' => $serialNumber,
                'status' => 'pending',
                'ip_address' => $ipAddress,
                'last_ping_at' => now(),
            ]
        );

        // Update ping info on every request
        $device->update([
            'ip_address' => $ipAddress,
            'last_ping_at' => now(),
        ]);

        // Store raw payload for debugging/processing
        BiometricRawLog::create([
            'serial_number' => $serialNumber,
            'raw_payload' => $request->all(),
            'status' => 'pending',
        ]);

        // If device is not yet claimed by a school, return claim info
        if ($device->status === 'pending' || !$device->school_id) {
            return response()->json([
                'success' => true,
                'message' => 'Device registered, awaiting claim',
                'device' => [
                    'serial_number' => $device->serial_number,
                    'status' => $device->status,
                    'claim_url' => config('app.url') . "/dashboard/attendance-settings?tab=alat&claim={$device->serial_number}",
                ],
            ]);
        }

        // Process attendance data if device is active
        $this->processAttendanceData($device, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data received and processed',
        ]);
    }

    /**
     * Extract serial number from request (supports various formats).
     */
    private function extractSerialNumber(Request $request): ?string
    {
        // Check common parameter names
        $possibleKeys = [
            'sn', 'serial_number', 'device_sn', 'device_serial', 
            'serial', 'mac', 'mac_address', 'device_id'
        ];

        foreach ($possibleKeys as $key) {
            if ($request->has($key) && $request->input($key)) {
                return (string) $request->input($key);
            }
        }

        // Check JSON body
        if ($request->isJson()) {
            $json = $request->json()->all();
            foreach ($possibleKeys as $key) {
                if (isset($json[$key]) && $json[$key]) {
                    return (string) $json[$key];
                }
            }
            
            // Check nested structures
            if (isset($json['device'][$possibleKeys[0]])) {
                return (string) $json['device'][$possibleKeys[0]];
            }
        }

        // Check headers (some devices send SN in headers)
        $headerKeys = ['x-device-sn', 'x-serial-number', 'device-sn', 'serial-number'];
        foreach ($headerKeys as $headerKey) {
            if ($request->header($headerKey)) {
                return $request->header($headerKey);
            }
        }

        return null;
    }

    /**
     * Process attendance data from biometric device.
     */
    private function processAttendanceData(BiometricDevice $device, array $payload): void
    {
        // This is where you'd implement the actual attendance processing logic
        // based on the specific biometric device protocol (ZKTeco, Suprema, etc.)
        
        // For now, we just log it. The actual processing can be done via a queued job
        // or by a separate worker that reads from biometric_raw_logs
        
        Log::info('Biometric attendance data received', [
            'device_id' => $device->id,
            'serial_number' => $device->serial_number,
            'school_id' => $device->school_id,
            'payload_keys' => array_keys($payload),
        ]);
    }

    /**
     * Get device status (for health checks).
     */
    public function status(Request $request, string $serialNumber)
    {
        $device = BiometricDevice::where('serial_number', $serialNumber)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'device' => [
                'serial_number' => $device->serial_number,
                'device_name' => $device->device_name,
                'status' => $device->status,
                'status_label' => $device->status_label,
                'is_online' => $device->isOnline(),
                'last_ping_at' => $device->last_ping_at?->toISOString(),
                'ip_address' => $device->ip_address,
                'school_id' => $device->school_id,
            ],
        ]);
    }
}