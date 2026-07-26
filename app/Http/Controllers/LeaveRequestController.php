<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function create()
    {
        return view('member.leaves.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sick,permission,duty_trip,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            // Save to local private storage
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'local');
        }

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'tenant_id' => Auth::user()->tenant_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('member.dashboard')->with('success', 'Pengajuan izin berhasil dikirim dan sedang menunggu persetujuan.');
    }
}
