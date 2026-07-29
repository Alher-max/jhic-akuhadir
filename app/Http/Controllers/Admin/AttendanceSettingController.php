<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;
use App\Models\AttendanceDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttendanceSettingController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        
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
            ]
        );

        $devices = AttendanceDevice::where('tenant_id', $tenantId)->get();

        return view('attendance-settings.index', compact('settings', 'devices'));
    }

    public function updateSettings(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $tenantId]);

        $request->validate([
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
}
