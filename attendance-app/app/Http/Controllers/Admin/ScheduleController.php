<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::where('tenant_id', Auth::user()->tenant_id)->get();
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $users = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('role', 'staff_student')
            ->get();
            
        return view('admin.schedules.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:routine,non_routine',
            'day_of_week' => 'required_if:type,routine|nullable|integer|between:1,7',
            'specific_date' => 'required_if:type,non_routine|nullable|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_period_minutes' => 'required|integer|min:0',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id'
        ]);

        $schedule = Schedule::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'type' => $request->type,
            'day_of_week' => $request->type === 'routine' ? $request->day_of_week : null,
            'specific_date' => $request->type === 'non_routine' ? $request->specific_date : null,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period_minutes' => $request->grace_period_minutes,
        ]);

        if ($request->has('users')) {
            $schedule->users()->sync($request->users);
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }
}
