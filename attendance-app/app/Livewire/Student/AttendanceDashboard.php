<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Auth;

class AttendanceDashboard extends Component
{
    public function render()
    {
        $logs = AttendanceLog::where('user_id', Auth::id())
            ->orderBy('punch_time', 'desc')
            ->take(10)
            ->get();
            
        return view('livewire.student.attendance-dashboard', [
            'logs' => $logs
        ]);
    }
}
