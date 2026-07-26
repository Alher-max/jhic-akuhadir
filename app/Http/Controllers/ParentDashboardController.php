<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class ParentDashboardController extends Controller
{
    public function index()
    {
        $parent = Auth::user();
        
        $children = User::where('parent_id', $parent->id)->get();
        
        $today = Carbon::today();
        
        $childrenAttendances = [];
        foreach ($children as $child) {
            $attendance = \App\Models\Attendance::where('user_id', $child->id)
                ->whereDate('date', $today)
                ->first();
                
            $childrenAttendances[] = (object)[
                'child' => $child,
                'attendance' => $attendance
            ];
        }

        return view('parent.dashboard', compact('parent', 'children', 'childrenAttendances', 'today'));
    }
}