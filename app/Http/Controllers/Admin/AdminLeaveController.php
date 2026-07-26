<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminLeaveController extends Controller
{
    public function index()
    {
        $leaves = LeaveRequest::with('user')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.leaves.index', compact('leaves'));
    }

    public function approve($id)
    {
        $leave = LeaveRequest::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        
        $leave->update(['status' => 'approved']);

        // Sync to attendances table
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $leave->user_id,
                    'tenant_id' => $leave->tenant_id,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'clock_in' => null,
                    'clock_out' => null,
                    'status' => $leave->type,
                    'notes' => $leave->reason,
                ]
            );
        }

        return redirect()->back()->with('success', 'Pengajuan izin berhasil disetujui.');
    }

    public function reject($id)
    {
        $leave = LeaveRequest::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        
        $leave->update(['status' => 'rejected']);
        
        // As per policy, we don't insert rejected leaves into the attendances table.

        return redirect()->back()->with('success', 'Pengajuan izin berhasil ditolak.');
    }

    public function download($id)
    {
        $leave = LeaveRequest::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        if (!$leave->attachment || !Storage::disk('local')->exists($leave->attachment)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk('local')->download($leave->attachment);
    }
}
