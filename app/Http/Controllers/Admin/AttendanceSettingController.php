<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;
use App\Models\AttendanceDevice;
use App\Models\BiometricDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttendanceSettingController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $tenantId = Auth::user()->tenant_id;
        $tab = $request->query('tab', 'umum');
        $claimSn = $request->query('claim');
        
        // Dapatkan atau buat default settings jika belum ada
        $settings = AttendanceSetting::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'method_pwa' => true,
                'method_rfid' => false,
                'method_qrcode' => false,
                'method_biometric' => false,
                'method_manual' => false,
                'method_wifi' => false,
                'wifi_allowed_ssids' => [],
                'wifi_allowed_macs' => [],
                'rfid_secret_key' => Str::random(32),
                'biometric_secret_key' => Str::random(32),
            ]
        );

        // Generate biometric_secret_key if not exists
        if (empty($settings->biometric_secret_key)) {
            $settings->update(['biometric_secret_key' => Str::random(32)]);
        }

        $devices = AttendanceDevice::where('tenant_id', $tenantId)->get();
        
        // Get pending biometric devices for this school (Auto-discovered but not claimed)
        $pendingBiometricDevices = BiometricDevice::where('school_id', $tenantId)
            ->where('status', 'pending')
            ->get();
        
        // Get claimed biometric devices for this school
        $claimedBiometricDevices = BiometricDevice::where('school_id', $tenantId)
            ->where('status', '!=', 'pending')
            ->get();

        return view('attendance-settings.index', compact('settings', 'devices', 'tab', 'tenant', 'claimSn', 'pendingBiometricDevices', 'claimedBiometricDevices'));
    }

    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
        ]);

        $tenant = Auth::user()->tenant;
        if ($tenant) {
            $tenant->update(['timezone' => $request->timezone]);
        }

        return back()->with('success', 'Zona waktu sekolah berhasil diperbarui.');
    }

    public function updateSettings(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $tenantId]);

        $request->validate([
            'timezone' => 'nullable|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
            'method_rfid' => 'boolean',
            'method_qrcode' => 'boolean',
            'method_biometric' => 'boolean',
            'method_pwa' => 'boolean',
            'method_manual' => 'boolean',
            'method_wifi' => 'boolean',
            'wifi_allowed_ssids' => 'nullable|string', // dikirim sebagai JSON string atau dipisah koma
            'wifi_allowed_macs' => 'nullable|string',
            'rfid_secret_key' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'radius_meters' => 'nullable|integer|min:10',
            'biometric_ip_address' => 'nullable|string',
            'is_liveness_active' => 'boolean',
        ]);

        $tenant = Auth::user()->tenant;
        if ($tenant && $request->filled('timezone')) {
            $tenant->update(['timezone' => $request->timezone]);
        }

        // Parsing comma-separated string ke array untuk SSID & MAC
        $ssids = [];
        if ($request->filled('wifi_allowed_ssids')) {
            $ssids = array_map('trim', explode(',', $request->wifi_allowed_ssids));
        }

        $macs = [];
        if ($request->filled('wifi_allowed_macs')) {
            $macs = array_map('trim', explode(',', $request->wifi_allowed_macs));
        }

        $biometricIpAddress = null;
        if ($request->filled('biometric_ip_address')) {
            $ipList = array_map('trim', explode(',', $request->biometric_ip_address));
            $ipList = array_filter($ipList, fn($ip) => $ip !== '');
            $biometricIpAddress = !empty($ipList) ? implode(', ', $ipList) : null;
        }

        $settings->update([
            'method_rfid' => $request->has('method_rfid'),
            'method_qrcode' => $request->has('method_qrcode'),
            'method_biometric' => $request->has('method_biometric'),
            'method_pwa' => $request->has('method_pwa'),
            'method_manual' => $request->has('method_manual'),
            'method_wifi' => $request->has('method_wifi'),
            'is_liveness_active' => $request->boolean('is_liveness_active'),
            'wifi_allowed_ssids' => $ssids,
            'wifi_allowed_macs' => $macs,
            'rfid_secret_key' => $request->rfid_secret_key ?? $settings->rfid_secret_key,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meters' => $request->radius_meters ?? 100,
            'biometric_ip_address' => $biometricIpAddress,
        ]);

        if ($request->has('generate_new_key')) {
            $settings->update(['rfid_secret_key' => Str::random(32)]);
        }

        return back()->with('success', 'Pengaturan presensi berhasil diperbarui.');
    }

    public function storeDevice(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|in:rfid,qrcode,biometric,wifi_router',
            'location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'status' => 'required|in:online,offline,maintenance',
        ]);

        AttendanceDevice::create([
            'tenant_id' => $tenantId,
            'device_name' => $request->device_name,
            'device_type' => $request->device_type,
            'location' => $request->location,
            'ip_address' => $request->ip_address,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Perangkat berhasil didaftarkan.');
    }

    public function destroyDevice($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $device = AttendanceDevice::where('tenant_id', $tenantId)->findOrFail($id);
        $device->delete();

        return back()->with('success', 'Perangkat berhasil dihapus.');
    }

    public function claimDevice(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $request->validate([
            'serial_number' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
        ]);

        // Find the pending biometric device
        $device = BiometricDevice::where('serial_number', $request->serial_number)
            ->where(function ($query) use ($tenantId) {
                $query->where('school_id', $tenantId)
                      ->orWhereNull('school_id');
            })
            ->firstOrFail();

        // Check if device is already claimed by another school
        if ($device->school_id && $device->school_id !== $tenantId) {
            return back()->with('error', 'Perangkat ini sudah diklaim oleh sekolah lain.');
        }

        // Update device to claimed status
        $device->update([
            'school_id' => $tenantId,
            'device_name' => $request->device_name,
            'status' => 'active',
        ]);

        // Also create an AttendanceDevice record for backward compatibility
        AttendanceDevice::firstOrCreate(
            ['tenant_id' => $tenantId, 'device_name' => $request->device_name, 'device_type' => 'biometric'],
            [
                'location' => 'Auto-claimed via Self-Service',
                'ip_address' => $device->ip_address,
                'status' => 'online',
            ]
        );

        return back()->with('success', 'Mesin biometrik berhasil diklaim dan terhubung!');
    }

    /**
     * Generate a new secret key for biometric integration
     */
    public function generateSecretKey(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? auth()->user()->id;

        // Generate a secure random key
        $secretKey = Str::random(64);

        // Save to attendance settings
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $tenantId]);
        $settings->update([
            'biometric_secret_key' => $secretKey,
        ]);

        return response()->json([
            'success' => true,
            'secret_key' => $secretKey,
            'message' => 'Secret Key baru berhasil di-generate!'
        ]);
    }
}
