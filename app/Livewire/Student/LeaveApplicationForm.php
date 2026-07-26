<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;

class LeaveApplicationForm extends Component
{
    use WithFileUploads;

    public $start_date;
    public $end_date;
    public $type = 'sakit';
    public $reason;
    public $attachment;

    protected $rules = [
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'type' => 'required|in:sakit,izin,cuti',
        'reason' => 'required|string|max:500',
        'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ];

    public function submit()
    {
        $this->validate();

        $path = null;
        if ($this->attachment) {
            $path = $this->attachment->store('leave_attachments', 'public');
        }

        LeaveApplication::create([
            'user_id' => Auth::id(),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'type' => $this->type,
            'reason' => $this->reason,
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        session()->flash('message', 'Leave application submitted successfully.');
        $this->reset(['start_date', 'end_date', 'type', 'reason', 'attachment']);
    }

    public function render()
    {
        return view('livewire.student.leave-application-form');
    }
}
