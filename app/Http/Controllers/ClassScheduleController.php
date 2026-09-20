<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\TeacherService;

class ClassScheduleController extends Controller
{
    protected TeacherService $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

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
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();

            // Jika tidak ada kelas binaan khusus, tampilkan semua kelas tenant
            if ($classes->isEmpty()) {
                $classes = SchoolClass::where('tenant_id', $tenantId)->orderBy('tingkat')->orderBy('nama_kelas')->get();
            }
        } else {
            $classes = SchoolClass::where('tenant_id', $tenantId)->orderBy('tingkat')->orderBy('nama_kelas')->get();
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
            $schedules = ClassSchedule::with(['subject', 'teacher', 'schoolClass'])
                ->where('tenant_id', $tenantId)
                ->where('class_id', $selectedClass->id)
                ->orderBy('period_number', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
        }

        // Kelompokkan jadwal berdasarkan hari
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $groupedSchedules = [];
        foreach ($days as $day) {
            $groupedSchedules[$day] = $schedules->where('day_name', $day)->values();
        }

        $subjects = Subject::where('tenant_id', $tenantId)->orderBy('name')->get();
        $teachers = $this->teacherService->getActiveTeachers($tenantId);

        $activities = \App\Models\ActivitySchedule::with(['members'])
            ->withCount('members')
            ->where('tenant_id', $tenantId)
            ->orderBy('start_time', 'asc')
            ->get();

        $allStudents = User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'nisn', 'class_id']);

        return view('schedules.class_subject', compact(
            'classes',
            'selectedClass',
            'selectedClassId',
            'groupedSchedules',
            'days',
            'subjects',
            'teachers',
            'activities',
            'allStudents'
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
            'teacher_id' => 'required|exists:users,id',
            'day_name' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'period_number' => 'required|integer|min:1',
            'start_time' => 'required',
            'end_time' => ['required', function ($attribute, $value, $fail) use ($request) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
                $end = \Carbon\Carbon::createFromFormat('H:i', $value);
                if ($end->lte($start)) {
                    $fail('Jam Selesai harus lebih akhir daripada Jam Mulai.');
                }
            }],
        ], [
            'class_id.required' => 'Kelas wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'teacher_id.required' => 'Guru pengampu wajib dipilih.',
            'teacher_id.exists' => 'Guru pengampu yang dipilih tidak valid.',
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

        if ($request->has('code') && is_string($request->code)) {
            $request->merge([
                'code' => strtoupper(trim($request->code)),
            ]);
        }

        $subjectId = $request->input('subject_id');

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                'alpha_dash',
                \Illuminate\Validation\Rule::unique('subjects', 'code')->where('tenant_id', $tenantId)->ignore($subjectId),
            ],
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.max' => 'Kode mapel maksimal 10 karakter.',
            'code.alpha_dash' => 'Kode mapel hanya boleh berisi huruf, angka, dan tanda hubung tanpa spasi.',
            'code.unique' => 'Kode mapel ini sudah digunakan.',
        ]);

        if ($subjectId) {
            $subject = Subject::where('tenant_id', $tenantId)->findOrFail($subjectId);
            $subject->update([
                'code' => $request->code,
                'name' => $request->name,
            ]);
            $msg = 'Mata pelajaran berhasil diperbarui.';
        } else {
            Subject::create([
                'tenant_id' => $tenantId,
                'code' => $request->code,
                'name' => $request->name,
            ]);
            $msg = 'Mata pelajaran baru berhasil ditambahkan.';
        }

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', $msg);
    }

    /**
     * Memperbarui mata pelajaran (Master Subject - Operator / Admin Only).
     */
    public function updateSubject(Request $request, Subject $subject)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if ($subject->tenant_id !== $tenantId) {
            abort(403);
        }

        if (!in_array($user->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Operator / Admin Sekolah yang berhak mengubah Mata Pelajaran.');
        }

        if ($request->has('code') && is_string($request->code)) {
            $request->merge([
                'code' => strtoupper(trim($request->code)),
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                'alpha_dash',
                \Illuminate\Validation\Rule::unique('subjects', 'code')->where('tenant_id', $tenantId)->ignore($subject->id),
            ],
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.max' => 'Kode mapel maksimal 10 karakter.',
            'code.alpha_dash' => 'Kode mapel hanya boleh berisi huruf, angka, dan tanda hubung tanpa spasi.',
            'code.unique' => 'Kode mapel ini sudah digunakan.',
        ]);

        $subject->update([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', 'Mata pelajaran berhasil diperbarui.');
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
                    'preset_type' => 'kemendikbud',
                ]
            );
            if ($subject->wasRecentlyCreated) {
                $addedCount++;
            } else {
                $subject->update(['is_preset' => true, 'preset_type' => 'kemendikbud']);
            }
        }

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', "Preset Mata Pelajaran Kurikulum Standar Kemendikbud berhasil dimuat ($addedCount mapel baru ditambahkan).");
    }

    /**
     * Memuat preset daftar Mata Pelajaran Kurikulum Kemenag (KMA No. 1503 Tahun 2025).
     */
    public function loadSubjectPresetsKemenag()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if (!in_array($user->role, ['kepala_sekolah', 'admin_dapodik', 'operator', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Operator / Admin Sekolah yang berhak memuat preset Mata Pelajaran.');
        }

        $presets = [
            // Mata Pelajaran Umum (Kurikulum Merdeka Madrasah)
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Ilmu Pengetahuan Alam (IPA)', 'code' => 'IPA'],
            ['name' => 'Ilmu Pengetahuan Sosial (IPS)', 'code' => 'IPS'],
            ['name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'code' => 'PJOK'],
            ['name' => 'Seni Budaya', 'code' => 'SBD'],
            ['name' => 'Pendidikan Kewarganegaraan', 'code' => 'PKN'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
            ['name' => 'Informatika', 'code' => 'INF'],

            // Mata Pelajaran Keagamaan Khas Madrasah (KMA 1503/2025)
            ['name' => 'Al-Qur\'an Hadits', 'code' => 'QH'],
            ['name' => 'Aqidah Akhlak', 'code' => 'AA'],
            ['name' => 'Fiqih', 'code' => 'FIQ'],
            ['name' => 'Sejarah Kebudayaan Islam (SKI)', 'code' => 'SKI'],
            ['name' => 'Bahasa Arab', 'code' => 'BAR'],
            ['name' => 'Pendidikan Agama Islam (PAI)', 'code' => 'PAI'],
            ['name' => 'Tasawuf', 'code' => 'TSW'],
            ['name' => 'Bahasa Daerah', 'code' => 'BDA'],
            ['name' => 'Pendidikan Karakter', 'code' => 'PKR'],
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
                    'preset_type' => 'kemenag',
                ]
            );
            if ($subject->wasRecentlyCreated) {
                $addedCount++;
            } else {
                $subject->update(['is_preset' => true, 'preset_type' => 'kemenag']);
            }
        }

        return redirect()->route('class-schedules.index', ['tab' => 'subjects'])->with('success', "Preset Mata Pelajaran Kurikulum Kemenag (KMA No. 1503 Tahun 2025) berhasil dimuat ($addedCount mapel baru ditambahkan).");
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
     * Menyimpan atau memperbarui kegiatan / ekskul (Activity Hub).
     */
    public function storeActivity(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $activityId = $request->input('activity_id');

        $request->validate([
            'name' => 'required|string|max:255',
            'day_name' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'late_tolerance_minutes' => 'nullable|integer|min:0|max:180',
            'target_scope' => 'required|string|in:all,class,members',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'integer|exists:school_classes,id',
        ], [
            'name.required' => 'Nama kegiatan / ekskul wajib diisi.',
            'day_name.required' => 'Hari pelaksanaan wajib dipilih.',
            'day_name.in' => 'Pilihan hari pelaksanaan tidak valid.',
            'start_time.required' => 'Jam mulai kegiatan wajib diisi.',
            'end_time.required' => 'Jam selesai kegiatan wajib diisi.',
            'end_time.after' => 'Jam Selesai harus lebih akhir daripada Jam Mulai.',
            'target_scope.required' => 'Target peserta wajib dipilih.',
            'target_scope.in' => 'Pilihan target peserta tidak valid.',
        ]);

        $data = [
            'tenant_id' => $tenantId,
            'created_by' => $user->id,
            'name' => $request->name,
            'day_name' => $request->day_name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'late_tolerance_minutes' => $request->input('late_tolerance_minutes', 15),
            'target_scope' => $request->target_scope,
            'target_class_ids' => $request->target_scope === 'class' ? $request->input('target_class_ids', []) : null,
            'is_preset' => false,
        ];

        if ($activityId) {
            $activity = \App\Models\ActivitySchedule::where('tenant_id', $tenantId)->findOrFail($activityId);
            $activity->update($data);
            $msg = 'Kegiatan / Ekskul berhasil diperbarui.';
        } else {
            \App\Models\ActivitySchedule::create($data);
            $msg = 'Kegiatan / Ekskul baru berhasil ditambahkan.';
        }

        return redirect()->route('class-schedules.index', ['tab' => 'activities'])->with('success', $msg);
    }

    /**
     * Memuat preset Kegiatan & Ekskul Sekolah Standar.
     */
    public function loadActivityPresets()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $presets = [
            ['name' => 'Upacara Bendera Senin', 'day_name' => 'Senin', 'start_time' => '07:00:00', 'end_time' => '08:00:00', 'late_tolerance_minutes' => 10, 'target_scope' => 'all'],
            ['name' => 'Pramuka Wajib', 'day_name' => 'Jumat', 'start_time' => '15:00:00', 'end_time' => '17:00:00', 'late_tolerance_minutes' => 15, 'target_scope' => 'all'],
            ['name' => 'Senam & Olahraga Jumat', 'day_name' => 'Jumat', 'start_time' => '07:00:00', 'end_time' => '08:00:00', 'late_tolerance_minutes' => 10, 'target_scope' => 'all'],
            ['name' => 'Palang Merah Remaja (PMR)', 'day_name' => 'Sabtu', 'start_time' => '15:00:00', 'end_time' => '16:30:00', 'late_tolerance_minutes' => 15, 'target_scope' => 'members'],
            ['name' => 'Sholat Dhuhur Berjamaah', 'day_name' => 'Senin', 'start_time' => '12:00:00', 'end_time' => '13:00:00', 'late_tolerance_minutes' => 10, 'target_scope' => 'all'],
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
                    'day_name' => $preset['day_name'],
                    'start_time' => $preset['start_time'],
                    'end_time' => $preset['end_time'],
                    'late_tolerance_minutes' => $preset['late_tolerance_minutes'],
                    'target_scope' => $preset['target_scope'],
                    'is_preset' => true,
                ]
            );
            if ($activity->wasRecentlyCreated) {
                $addedCount++;
            } else {
                $activity->update([
                    'day_name' => $preset['day_name'],
                    'target_scope' => $preset['target_scope'],
                    'is_preset' => true,
                ]);
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

    /**
     * Mengupdate daftar anggota siswa kegiatan / ekskul (Batch Sync).
     */
    public function updateActivityMembers(Request $request, \App\Models\ActivitySchedule $activity)
    {
        $tenantId = Auth::user()->tenant_id;

        if ($activity->tenant_id !== $tenantId) {
            abort(403);
        }

        $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer|exists:users,id',
        ]);

        $studentIds = $request->input('student_ids', []);
        
        $validStudentIds = User::where('tenant_id', $tenantId)
            ->whereIn('id', $studentIds)
            ->where('role', 'student')
            ->pluck('id')
            ->toArray();

        $activity->members()->sync($validStudentIds);

        return redirect()->route('class-schedules.index', ['tab' => 'activities'])
            ->with('success', "Daftar anggota kegiatan \"{$activity->name}\" berhasil diperbarui.");
    }

    /**
     * Menambahkan siswa tunggal sebagai anggota kegiatan.
     */
    public function addActivityMember(Request $request, \App\Models\ActivitySchedule $activity)
    {
        $tenantId = Auth::user()->tenant_id;

        if ($activity->tenant_id !== $tenantId) {
            abort(403);
        }

        $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        $student = User::where('tenant_id', $tenantId)->where('role', 'student')->findOrFail($request->student_id);

        $activity->members()->syncWithoutDetaching([$student->id]);

        return redirect()->back()->with('success', "Siswa {$student->name} berhasil ditambahkan ke kegiatan {$activity->name}.");
    }

    /**
     * Menghapus siswa dari anggota kegiatan.
     */
    public function removeActivityMember(\App\Models\ActivitySchedule $activity, User $student)
    {
        $tenantId = Auth::user()->tenant_id;

        if ($activity->tenant_id !== $tenantId || $student->tenant_id !== $tenantId) {
            abort(403);
        }

        $activity->members()->detach($student->id);

        return redirect()->back()->with('success', "Siswa {$student->name} berhasil dihapus dari anggota kegiatan.");
    }
}
