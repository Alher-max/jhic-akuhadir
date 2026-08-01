<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $user = Auth::user();
        
        $query = Schedule::where('tenant_id', $tenantId);

        // Filter for wali_kelas to only see their managed classes
        if ($user->role === 'wali_kelas') {
            $managedClassIds = $user->homeroomClasses()->pluck('id')->toArray();
            $query->whereIn('class_id', $managedClassIds);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('day')) {
            $query->where('day_of_week', $request->day);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();
        
        if ($user->role === 'wali_kelas') {
            $classes = \App\Models\SchoolClass::where('tenant_id', $tenantId)
                ->where('wali_kelas_id', $user->id)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        } else {
            $classes = \App\Models\SchoolClass::where('tenant_id', $tenantId)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        }
        
        $teachers = User::where('role', 'wali_kelas')->where('tenant_id', $tenantId)->get();

        return view('admin.schedules.index', compact('schedules', 'classes', 'teachers'));
    }

    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $user = Auth::user();
        
        if ($user->role === 'wali_kelas') {
            $classes = \App\Models\SchoolClass::where('tenant_id', $tenantId)
                ->where('wali_kelas_id', $user->id)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();
        } else {
            $classes = \App\Models\SchoolClass::where('tenant_id', $tenantId)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        }
        
        $teachers = User::where('role', 'wali_kelas')
            ->where('tenant_id', $tenantId)
            ->get();
            
        $users = User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->get();
            
        return view('admin.schedules.create', compact('users', 'classes', 'teachers'));
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
            'class_id' => 'nullable|exists:school_classes,id',
            'teacher_id' => 'required|exists:users,id',
            'participant_type' => 'required|in:class,manual',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id'
        ]);

        $user = Auth::user();

        // Authorization for wali_kelas
        if ($user->role === 'wali_kelas') {
            $managedClassIds = $user->homeroomClasses()->pluck('id')->toArray();
            if (!in_array($request->class_id, $managedClassIds)) {
                abort(403, 'Anda tidak memiliki akses untuk membuat jadwal di kelas ini.');
            }
        }

        $schedule = Schedule::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'type' => $request->type,
            'day_of_week' => $request->type === 'routine' ? $request->day_of_week : null,
            'specific_date' => $request->type === 'non_routine' ? $request->specific_date : null,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'grace_period_minutes' => $request->grace_period_minutes,
            'class_id' => $request->class_id,
            'teacher_id' => $request->teacher_id,
        ]);

        if ($request->participant_type === 'class' && $request->class_id) {
            $studentIds = User::where('tenant_id', Auth::user()->tenant_id)
                ->where('role', 'student')
                ->where('class_id', $request->class_id)
                ->pluck('id');
            $schedule->users()->sync($studentIds);
        } else if ($request->has('users')) {
            $schedule->users()->sync($request->users);
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $user = Auth::user();

        if ($user->role === 'wali_kelas') {
            $managedClassIds = $user->homeroomClasses()->pluck('id')->toArray();
            if (!in_array($schedule->class_id, $managedClassIds)) {
                abort(403, 'Anda tidak memiliki akses untuk menghapus jadwal di kelas ini.');
            }
        }

        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}
