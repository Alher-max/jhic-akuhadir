<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Device;
use App\Models\AttendanceLog;

class LiveDeviceMonitor extends Component
{
    // Polling every 5 seconds
    public function render()
    {
        $devices = Device::all();
        $recentLogs = AttendanceLog::with(['user', 'device'])
            ->orderBy('punch_time', 'desc')
            ->take(20)
            ->get();

        return view('livewire.admin.live-device-monitor', [
            'devices' => $devices,
            'recentLogs' => $recentLogs
        ]);
    }
}
