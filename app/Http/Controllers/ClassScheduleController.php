<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal KBM mingguan per kelas.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Tentukan kelas yang tersedia berdasarkan role
        if (in_array($user->role, ['guru', 'wali_kelas', 'guru_mapel'])) {
            $classes = SchoolClass::where('tenant_id', $tenantId)
                ->where('wali_kelas_id', $user->id)
                ->ordered()
                ->get();

            // Jika tidak ada kelas binaan khusus, tampilkan semua kelas tenant
            if ($classes->isEmpty()) {
                $classes = SchoolClass::where('tenant_id', $tenantId)->ordered()->get();
            }
        } else {
            $classes = SchoolClass::where('tenant_id', $tenantId)->ordered()->get();
        }

        $selectedClassId = $request->get('class_id', $classes->first()?->id);
        $selectedClass = $classes->firstWhere('id', $selectedClassId) ?? $classes->first();

        // Restriksi Otorisasi: Wali Kelas hanya boleh mengelola kelas binaannya jika ada
        if (in_array($user->role, ['wali_kelas']) && $selectedClass && $selectedClass->wali_kelas_id !== $user->id) {
            $myClass = $classes->first();
            if ($myClass) {
                $selectedClassId = $myClass->id;
                $selectedClass = $myClass;
            }
        }

        $schedules = collect();
        if ($selectedClass) {
            $schedules = ClassSchedule::with(['subject', 'teacher'])
                ->where('tenant_id', $tenantId)
                ->where('class_id', $selectedClass->id)
                ->orderBy('period_number', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
        }

        // Kelompokkan jadwal berdasarkan hari
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $groupedSchedules = [];
        foreach ($days as $day) {
            $groupedSchedules[$day] = $schedules->where('day_name', $day)->values();
        }

        $subjects = Subject::where('tenant_id', $tenantId)->orderBy('name')->get();
        $teachers = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['guru', 'wali_kelas', 'guru_mapel', 'operator', 'staff'])
            ->orderBy('name')
            ->get();

        $activities = \App\Models\ActivitySchedule::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('schedules.class_subject', compact(
            'classes',
            'selectedClass',
            'selectedClassId',
            'groupedSchedules',
            'days',
            'subjects',
            'teachers',
            'activities'
        ));
    }

    /**
     * Menyimpan atau memperbarui slot jadwal KBM.
     */
    public function storeSchedule(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:users,id',
            'day_name' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'period_number' => 'required|integer|min:1',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ], [
            'class_id.required' => 'Kelas wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'day_name.required' => 'Hari wajib dipilih.',
            'period_number.required' => 'Jam ke- (period) wajib diisi.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.after' => 'Jam Selesai harus lebih akhir daripada Jam Mulai.',
        ]);

        // Restriksi Wali Kelas
        $targetClass = SchoolClass::where('id', $request->class_id)->where('tenant_id', $tenantId)->firstOrFail();
        if ($user->role === 'wali_kelas' && $targetClass->wali_kelas_id !== $user->id) {
            return redirect()->back()->with('error', 'Anda hanya berhak mengelola jadwal kelas asuhan Anda.');
        }

        $scheduleId = $request->input('schedule_id');

        // Cek duplikasi jadwal pada kelas, hari, dan jam (period_number) yang sama
        $existingDuplicate = ClassSchedule::where('tenant_id', $tenantId)
            ->where('class_id', $request->class_id)
            ->where('day_name', $request->day_name)
            ->where('period_number', $request->period_number)
            ->when($scheduleId, fn($query) => $query->where('id', '!=', $scheduleId))
            ->exists();

        if ($existingDuplicate) {
            return redirect()->back()->withInput()->with('error', 'Jadwal untuk kelas pada hari dan jam tersebut sudah ada!');
        }

        if ($scheduleId) {
            $schedule = ClassSchedule::where('tenant_id', $tenantId)->findOrFail($scheduleId);
            $schedule->update([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'day_name' => $request->day_name,
                'period_number' => $request->period_number,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
            $msg = 'Jadwal pelajaran berhasil diperbarui.';
        } else {
            ClassSchedule::create([
                'tenant_id' => $tenantId,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'day_name' => $request->day_name,
                'period_number' => $request->period_number,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
            $msg = 'Jadwal pelajaran berhasil ditambahkan.';
        }

        return redirect()->route('class-schedules.index', ['class_id' => $request->class_id, 'tab' => 'schedules'])->with('success', $msg);
    }

    /**
     * Menghapus slot jadwal KBM.
     */
    public function destroySchedule($id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $schedule = ClassSchedule::where('tenant_id', $tenantId)->findOrFail($id);

        if ($user->role === 'wali_kelas' && $schedule->schoolClass?->wali_kelas_id !== $user->id) {
            return redirect()->back()->with('error', 'Anda hanya berhak menghapus jadwal kelas asuhan Anda.');
        }

        $classId = $schedule->class_id;
        $schedule->delete();

        return redirect()->route('class-schedules.index', ['class_id' => $classId, 'tab' => 'schedules'])->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * Menyimpan mata pelajaran baru (Master Subject - Operator / Admin Only).
     */
    public function storeSubject(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if (!in_array($user->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Operator / Admin Sekolah yang berhak membuat Mata Pelajaran baru.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
        ]);

        Subject::create([
            'tenant_id' => $tenantId,
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', 'Mata pelajaran baru berhasil ditambahkan.');
    }

    /**
     * Memuat preset daftar Mata Pelajaran Kurikulum Standar Kemendikbud.
     */
    public function loadSubjectPresets()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if (!in_array($user->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Operator / Admin Sekolah yang berhak memuat preset Mata Pelajaran.');
        }

        $presets = [
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
            ['name' => 'Ilmu Pengetahuan Alam (IPA)', 'code' => 'IPA'],
            ['name' => 'Ilmu Pengetahuan Sosial (IPS)', 'code' => 'IPS'],
            ['name' => 'Informatika', 'code' => 'INF'],
            ['name' => 'Pendidikan Jasmani, Olahraga, & Kesehatan', 'code' => 'PJOK'],
            ['name' => 'Pendidikan Pancasila & Kewarganegaraan', 'code' => 'PPKN'],
            ['name' => 'Pendidikan Agama & Budi Pekerti', 'code' => 'PAIBP'],
            ['name' => 'Seni & Prakarya', 'code' => 'SNB'],
        ];

        $addedCount = 0;
        foreach ($presets as $preset) {
            $subject = Subject::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'name' => $preset['name'],
                ],
                [
                    'code' => $preset['code'],
                    'is_preset' => true,
                ]
            );
            if ($subject->wasRecentlyCreated) {
                $addedCount++;
            } else {
                $subject->update(['is_preset' => true]);
            }
        }

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', "Preset Mata Pelajaran Kurikulum Standar Kemendikbud berhasil dimuat ($addedCount mapel baru ditambahkan).");
    }

    /**
     * Menghapus seluruh preset Mata Pelajaran Standar (is_preset = true).
     */
    public function clearSubjectPresets()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if (!in_array($user->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Operator / Admin Sekolah yang berhak menghapus preset Mata Pelajaran.');
        }

        $deletedCount = Subject::where('tenant_id', $tenantId)->where('is_preset', true)->delete();

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', "Semua preset Mata Pelajaran Standar berhasil dihapus ($deletedCount item dibersihkan).");
    }

    /**
     * Menyimpan kegiatan / ekskul baru.
     */
    public function storeActivity(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'late_tolerance_minutes' => 'nullable|integer|min:0|max:180',
        ], [
            'name.required' => 'Nama kegiatan wajib diisi.',
            'start_time.required' => 'Jam mulai kegiatan wajib diisi.',
            'end_time.required' => 'Jam selesai kegiatan wajib diisi.',
            'end_time.after' => 'Jam Selesai harus lebih akhir daripada Jam Mulai.',
        ]);

        \App\Models\ActivitySchedule::create([
            'tenant_id' => $tenantId,
            'created_by' => $user->id,
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'late_tolerance_minutes' => $request->input('late_tolerance_minutes', 15),
            'is_preset' => false,
        ]);

        return redirect()->route('class-schedules.index', ['tab' => 'activities'])->with('success', 'Kegiatan / Ekskul baru berhasil ditambahkan.');
    }

    /**
     * Memuat preset Kegiatan & Ekskul Sekolah Standar.
     */
    public function loadActivityPresets()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $presets = [
            ['name' => 'Upacara Bendera Senin', 'start_time' => '07:00:00', 'end_time' => '08:00:00', 'late_tolerance_minutes' => 10],
            ['name' => 'Pramuka Wajib', 'start_time' => '15:00:00', 'end_time' => '17:00:00', 'late_tolerance_minutes' => 15],
            ['name' => 'Senam & Olahraga Jumat', 'start_time' => '07:00:00', 'end_time' => '08:00:00', 'late_tolerance_minutes' => 10],
            ['name' => 'Palang Merah Remaja (PMR)', 'start_time' => '15:00:00', 'end_time' => '16:30:00', 'late_tolerance_minutes' => 15],
            ['name' => 'Sholat Dhuhur Berjamaah', 'start_time' => '12:00:00', 'end_time' => '13:00:00', 'late_tolerance_minutes' => 10],
        ];

        $addedCount = 0;
        foreach ($presets as $preset) {
            $activity = \App\Models\ActivitySchedule::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'name' => $preset['name'],
                ],
                [
                    'created_by' => $user->id,
                    'start_time' => $preset['start_time'],
                    'end_time' => $preset['end_time'],
                    'late_tolerance_minutes' => $preset['late_tolerance_minutes'],
                    'is_preset' => true,
                ]
            );
            if ($activity->wasRecentlyCreated) {
                $addedCount++;
            } else {
                $activity->update(['is_preset' => true]);
            }
        }

        return redirect()->route('class-schedules.index', ['tab' => 'activities'])->with('success', "Preset Kegiatan & Ekskul Sekolah Standar berhasil dimuat ($addedCount kegiatan baru ditambahkan).");
    }

    /**
     * Menghapus seluruh preset Kegiatan Standar (is_preset = true).
     */
    public function clearActivityPresets()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $deletedCount = \App\Models\ActivitySchedule::where('tenant_id', $tenantId)->where('is_preset', true)->delete();

        return redirect()->route('class-schedules.index', ['tab' => 'activities'])->with('success', "Semua preset Kegiatan Standar berhasil dihapus ($deletedCount item dibersihkan).");
    }

    /**
     * Menghapus kegiatan / ekskul.
     */
    public function destroyActivity($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $activity = \App\Models\ActivitySchedule::where('tenant_id', $tenantId)->findOrFail($id);
        $activity->delete();

        return redirect()->route('class-schedules.index', ['tab' => 'activities'])->with('success', 'Kegiatan / Ekskul berhasil dihapus.');
    }
}
