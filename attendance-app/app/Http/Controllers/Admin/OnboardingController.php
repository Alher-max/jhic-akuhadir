<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;
        
        // If already completed, redirect to dashboard
        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return view('admin.onboarding', compact('tenant'));
    }

    public function finish(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'attendance_method' => 'required|in:hardware,wifi,gps,liveness',
            'wifi_bssid' => 'required_if:attendance_method,wifi|nullable|string',
            'gps_lat' => 'required_if:attendance_method,gps|nullable|string',
            'gps_lng' => 'required_if:attendance_method,gps|nullable|string',
            'gps_radius' => 'required_if:attendance_method,gps|nullable|integer|min:1',
        ]);

        $tenant->attendance_method = $request->attendance_method;

        if ($request->attendance_method === 'hardware') {
            $tenant->device_token = Str::random(40);
        } elseif ($request->attendance_method === 'wifi') {
            $tenant->wifi_bssid = $request->wifi_bssid;
        } elseif ($request->attendance_method === 'gps') {
            $tenant->gps_lat = $request->gps_lat;
            $tenant->gps_lng = $request->gps_lng;
            $tenant->gps_radius = $request->gps_radius;
        }

        $tenant->onboarding_completed = true;
        $tenant->save();

        return redirect()->route('dashboard')->with('success', 'Pengaturan awal absensi berhasil disimpan!');
    }
}
