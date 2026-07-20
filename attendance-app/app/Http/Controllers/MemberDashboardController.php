<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    public function index()
    {
        // Protect: If user is admin/super_admin/manager_teacher, redirect to admin dashboard
        if (Auth::user()->role !== 'staff_student') {
            return redirect()->route('dashboard');
        }

        // Fetch today's attendance
        $todayAttendance = \App\Models\Attendance::where('user_id', Auth::id())
            ->where('date', date('Y-m-d'))
            ->first();

        // Check if there is an approved leave for today
        $todayLeave = \App\Models\LeaveRequest::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->where('start_date', '<=', date('Y-m-d'))
            ->where('end_date', '>=', date('Y-m-d'))
            ->first();

        return view('member.dashboard', compact('todayAttendance', 'todayLeave'));
    }
}
