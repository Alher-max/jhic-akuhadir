<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Announcement;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HomeroomAnnouncementController extends Controller
{
    private function authorizeHomeroomTeacher()
    {
        abort_if(auth()->user()->homeroomClasses->isEmpty(), 403, 'Akses khusus Wali Kelas.');
    }

    public function index()
    {
        $this->authorizeHomeroomTeacher();
        
        $teacher = Auth::user();
        $homeroomClass = $teacher->homeroomClasses->first();
        
        $announcements = Announcement::with(['schoolClass', 'teacher'])
            ->where('school_class_id', $homeroomClass?->id)
            ->whereHas('schoolClass', function ($q) use ($teacher) {
                $q->where('tenant_id', $teacher->tenant_id);
            })
            ->latest()
            ->get();

        return view('teacher.announcements.index', compact('announcements', 'homeroomClass'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'target_audience' => 'required|in:students,parents,both',
            'expired_at' => 'nullable|date',
        ]);

        $teacher = Auth::user();
        $homeroomClass = $teacher->homeroomClasses->first();
        abort_if(!$homeroomClass, 403, 'Anda belum ditugaskan sebagai Wali Kelas.');
        abort_if($homeroomClass->tenant_id !== $teacher->tenant_id, 403, 'Akses tidak sah untuk kelas ini.');

        $data = $request->only(['title', 'description', 'target_audience', 'expired_at']);
        $data['school_class_id'] = $homeroomClass->id;
        $data['teacher_id'] = $teacher->id;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('announcements', 'public');
            $data['attachment_path'] = $path;
        }

        $announcement = Announcement::create($data);

        // PWA Push Notification Logic
        try {
            $audience = collect();
            $class = SchoolClass::find($data['school_class_id']);
            
            if ($data['target_audience'] === 'students' || $data['target_audience'] === 'both') {
                $students = User::where('class_id', $class->id)->where('role', 'student')->get();
                $audience = $audience->concat($students);
            }

            if ($data['target_audience'] === 'parents' || $data['target_audience'] === 'both') {
                $parents = User::whereHas('students', function($q) use ($class) {
                    $q->where('class_id', $class->id);
                })->where('role', 'parent')->get();
                $audience = $audience->concat($parents);
            }

            if ($audience->isNotEmpty()) {
                $pushService = new \App\Services\PushNotificationService();
                $pushService->sendToMany(
                    $audience->unique('id'),
                    "Pengumuman Baru: " . $announcement->title,
                    "Pesan dari Wali Kelas: " . $teacher->name,
                    route('dashboard')
                );
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send PWA Push Notification: " . $e->getMessage());
        }

        return back()->with('success', 'Pengumuman berhasil dikirim.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::with('schoolClass')->findOrFail($id);
        $teacher = auth()->user();

        abort_if(
            !$teacher->homeroomClasses->contains('id', $announcement->school_class_id) ||
            $announcement->schoolClass?->tenant_id !== $teacher->tenant_id,
            403,
            'Anda tidak memiliki akses.'
        );

        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }
        $announcement->delete();
        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
