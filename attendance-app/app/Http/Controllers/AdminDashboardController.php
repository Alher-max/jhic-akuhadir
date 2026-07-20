<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Protect: If user is staff_student (member), redirect to member dashboard
        if (Auth::user()->role === 'staff_student') {
            return redirect()->route('member.dashboard');
        }

        $tenantId = Auth::user()->tenant_id;
        $today = date('Y-m-d');

        $totalMembers = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'staff_student')
            ->count();

        $presentToday = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->where('date', $today)
            ->count();

        $absentToday = max(0, $totalMembers - $presentToday);

        // Fetch today's detailed attendance list
        $attendances = \App\Models\Attendance::with('user')
            ->where('tenant_id', $tenantId)
            ->where('date', date('Y-m-d'))
            ->get();

        // Count pending leave requests only for manager_teacher and owner
        $pendingLeavesCount = 0;
        if (in_array(Auth::user()->role, ['owner', 'manager_teacher'])) {
            $pendingLeavesCount = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();
        }

        return view('dashboard', compact('totalMembers', 'presentToday', 'absentToday', 'attendances', 'pendingLeavesCount'));
    }
}
