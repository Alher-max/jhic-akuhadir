<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AttendanceReport extends Component
{
    use WithPagination;

    public $month;
    public $year;

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
    }

    public function render()
    {
        $logs = AttendanceLog::with(['user', 'device', 'activitySchedule'])
            ->whereYear('punch_time', $this->year)
            ->whereMonth('punch_time', $this->month)
            ->orderBy('punch_time', 'desc')
            ->paginate(20);

        return view('livewire.admin.attendance-report', [
            'logs' => $logs
        ]);
    }

    public function exportExcel()
    {
        // Mock export functionality
        session()->flash('message', 'Exporting to Excel is being processed.');
    }

    public function exportPdf()
    {
        // Mock export functionality
        session()->flash('message', 'Exporting to PDF is being processed.');
    }
}
