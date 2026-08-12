<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Protect: If user is staff_student (member), redirect to member dashboard
        if (Auth::user()->role === 'student') {
            return redirect()->route('member.dashboard');
        }

        $tenantId = Auth::user()->tenant_id;
        $today = date('Y-m-d');

        $totalSiswa = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->count();

        $opSiswaHadirTepat = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->where('status', 'present')
            ->whereHas('user', function ($q) { $q->where('users.role', 'student'); })
            ->count();
            
        $opSiswaTerlambat = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->where('status', 'late')
            ->whereHas('user', function ($q) { $q->where('users.role', 'student'); })
            ->count();

        $opSiswaIzinSakit = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'approved')
            ->whereHas('user', function ($q) { $q->where('users.role', 'student'); })
            ->count();

        $opSiswaAlpa = $totalSiswa - ($opSiswaHadirTepat + $opSiswaTerlambat + $opSiswaIzinSakit);
        if ($opSiswaAlpa < 0) $opSiswaAlpa = 0;

        $totalGuruStaff = \App\Models\User::where('tenant_id', $tenantId)
            ->whereNotIn('role', ['student', 'parent', 'kepala_sekolah'])
            ->count();

        $opGuruHadir = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->whereHas('user', function ($q) {
                $q->whereNotIn('users.role', ['student', 'parent', 'kepala_sekolah']);
            })
            ->count();

        $opGuruIzinSakit = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('user', function ($q) {
                $q->whereNotIn('users.role', ['student', 'parent', 'kepala_sekolah']);
            })
            ->count();
            
        $totalRombel = \App\Models\SchoolClass::where('tenant_id', $tenantId)
            ->count();

        $opRombelKosong = \App\Models\SchoolClass::where('tenant_id', $tenantId)
            ->whereNull('wali_kelas_id')
            ->count();
            
        $opPendingInvitations = \Illuminate\Support\Facades\DB::table('invitations')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $opPendingTickets = \App\Models\SupportTicket::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $sudahHadirHariIni = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->count();

        $opSetting = \App\Models\AttendanceSetting::where('tenant_id', $tenantId)->first();
        $sysPwaActive = $opSetting ? (bool) $opSetting->method_pwa : true; // Default true per requirements if setting exists
        $sysRfidActive = $opSetting ? (bool) $opSetting->method_rfid : false;
        $sysQrcodeActive = $opSetting ? (bool) $opSetting->method_qrcode : false;
        $sysBiometricActive = $opSetting ? (bool) $opSetting->method_biometric : false;
        $sysWifiActive = $opSetting ? (bool) $opSetting->method_wifi : false;
        $sysGpsActive = $opSetting ? ($opSetting->latitude && $opSetting->longitude) : false;
        
        $waConfigKey = config('services.wa.api_key') 
            ?: config('services.whatsapp.api_key') 
            ?: env('WA_API_KEY') 
            ?: env('WA_TOKEN') 
            ?: env('WA_GATEWAY_URL');
        $sysWaReady = !empty($waConfigKey);

        // --- WALI KELAS SPECIFIC STATS & FILTER PREP ---
        $waliTotalSiswa = 0;
        $waliHadirHariIni = 0;
        $waliIzinSakit = 0;
        $waliBelumAbsen = 0;
        $waliClassName = 'Kelas Binaan';
        
        $pendingLeavesCount = 0;
        if (in_array(Auth::user()->role, ['kepala_sekolah', 'wali_kelas'])) {
            $pendingLeavesCount = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();
        }
        
        $homeroomClasses = \App\Models\SchoolClass::where('wali_kelas_id', Auth::id())->get();
        $isHomeroom = $homeroomClasses->isNotEmpty();
        $homeroomClass = $homeroomClasses->first();
        $isWaliKelas = $isHomeroom || Auth::user()->role === 'wali_kelas';

        $availableClasses = collect();

        if ($isHomeroom) {
            $availableClasses = $homeroomClasses;
            $classIds = $availableClasses->pluck('id');
            
            $waliClassesCount = $availableClasses->count();
            if ($waliClassesCount === 1) {
                $waliClassName = 'Kelas ' . $availableClasses->first()->nama_kelas;
            } elseif ($waliClassesCount > 1) {
                $waliClassName = $waliClassesCount . ' Kelas Binaan (' . $availableClasses->pluck('nama_kelas')->implode(', ') . ')';
            } else {
                $waliClassName = 'Belum Ada Kelas Binaan';
            }

            $waliTotalSiswa = \App\Models\User::where('tenant_id', $tenantId)
                ->where('role', 'student')
                ->whereIn('class_id', $classIds)
                ->count();

            $waliHadirHariIni = \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', $today)
                ->whereHas('user', function ($q) use ($classIds) {
                    $q->whereIn('users.class_id', $classIds);
                })
                ->whereIn('status', ['present', 'late'])
                ->count();

            $waliIzinSakit = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereHas('user', function ($q) use ($classIds) {
                    $q->whereIn('users.class_id', $classIds);
                })
                ->count();

            $waliBelumAbsen = $waliTotalSiswa - $waliHadirHariIni - $waliIzinSakit;
            if ($waliBelumAbsen < 0) $waliBelumAbsen = 0;
        }

        $dayMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        $todayDayName = $dayMap[date('N')] ?? 'Senin';

        $todayTeacherSchedules = \App\Models\ClassSchedule::where('tenant_id', $tenantId)
            ->where('teacher_id', Auth::id())
            ->where('day_name', $todayDayName)
            ->with(['schoolClass.students', 'subject', 'attendances' => function($q) use ($today) {
                $q->whereDate('date', $today);
            }])
            ->orderBy('period_number', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $isTeacherRole = in_array(Auth::user()->role, ['teacher', 'wali_kelas', 'guru', 'guru_mapel', 'manager_teacher']);
        $teacherTaughtClassIds = collect();

        if ($isTeacherRole) {
            $teacherTaughtClassIds = $todayTeacherSchedules->pluck('class_id')->filter();
            if ($isHomeroom && isset($homeroomClasses)) {
                $teacherTaughtClassIds = $teacherTaughtClassIds->merge($homeroomClasses->pluck('id'));
            }
            $teacherTaughtClassIds = $teacherTaughtClassIds->unique()->values();

            $availableClasses = \App\Models\SchoolClass::where('tenant_id', $tenantId)
                ->whereIn('id', $teacherTaughtClassIds)
                ->get();
        } else {
            $availableClasses = \App\Models\SchoolClass::where('tenant_id', $tenantId)->get();
        }

        // Fetch today's detailed attendance list with filter & pagination
        $query = \App\Models\Attendance::with('user.schoolClass')
            ->where('tenant_id', $tenantId)
            ->where('date', $today);

        if ($isTeacherRole) {
            $query->whereHas('user', function ($q) use ($teacherTaughtClassIds) {
                $q->whereIn('users.class_id', $teacherTaughtClassIds);
            });
        } elseif ($isWaliKelas && $availableClasses->isNotEmpty()) {
            $classIds = $availableClasses->pluck('id');
            $query->whereHas('user', function ($q) use ($classIds) {
                $q->whereIn('users.class_id', $classIds);
            });
        }

        if (request()->filled('class_id')) {
            $query->whereHas('user', function ($q) {
                $q->where('users.class_id', request('class_id'));
            });
        }

        if (request()->filled('search')) {
            $search = request('search');
            $lowerSearch = '%' . strtolower($search) . '%';
            $query->whereHas('user', function ($q) use ($lowerSearch) {
                $q->whereRaw("LOWER(users.name) LIKE ?", [$lowerSearch]);
            });
        }

        $attendances = $query->orderByRaw('COALESCE(clock_in, updated_at) DESC')->paginate(10)->withQueryString();

        $tenant = \App\Models\Tenant::find($tenantId);

        $teachers = \App\Models\User::activeTeachers()->where('tenant_id', $tenantId)->get();

        $studentsForBantuAbsen = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        $viewName = match(true) {
            request()->routeIs('homeroom.dashboard') => 'homeroom.dashboard',
            in_array(Auth::user()->role, ['teacher', 'wali_kelas', 'guru', 'guru_mapel', 'manager_teacher']) => 'teacher.dashboard',
            default => 'dashboard'
        };

        return view($viewName, compact(
            'totalSiswa', 'totalGuruStaff', 'totalRombel', 'sudahHadirHariIni', 
            'attendances', 'pendingLeavesCount', 'tenant', 
            'waliTotalSiswa', 'waliHadirHariIni', 'waliIzinSakit', 'waliBelumAbsen', 'waliClassName', 'availableClasses',
            'opSiswaHadirTepat', 'opSiswaTerlambat', 'opSiswaIzinSakit', 'opSiswaAlpa',
            'opGuruHadir', 'opGuruIzinSakit', 'opRombelKosong', 'opPendingInvitations', 'opPendingTickets',
            'opSetting', 'sysPwaActive', 'sysRfidActive', 'sysQrcodeActive', 'sysBiometricActive', 'sysWifiActive', 'sysGpsActive', 'sysWaReady', 'teachers', 'studentsForBantuAbsen',
            'isHomeroom', 'homeroomClass', 'homeroomClasses', 'todayDayName', 'todayTeacherSchedules'
        ));
    }

    public function homeroomIndex()
    {
        return $this->index();
    }

    public function storeManualAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'status' => 'nullable|in:present,late',
            'notes' => 'nullable|string|max:255',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $student = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->findOrFail($request->student_id);

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $now = \Carbon\Carbon::now();

        // Calculate status automatically based on cut-off time if not explicitly passed
        if ($request->filled('status')) {
            $status = $request->status;
        } else {
            $setting = \App\Models\AttendanceSetting::where('tenant_id', $tenantId)->first();
            $lateCutoff = $setting ? ($setting->start_time ?? '07:15:00') : '07:15:00';
            $status = ($now->format('H:i:s') > $lateCutoff) ? 'late' : 'present';
        }

        // Audit Trail: Record Teacher Name & ID in database notes for traceability
        $teacherName = Auth::user()->name;
        $teacherId = Auth::id();
        $auditTrailNote = "Bantu absen (Clock In) oleh Guru: {$teacherName} (ID: {$teacherId})";

        $notes = $request->notes 
            ? ($request->notes . " | " . $auditTrailNote) 
            : $auditTrailNote;

        \App\Models\Attendance::updateOrCreate(
            [
                'user_id' => $student->id,
                'tenant_id' => $tenantId,
                'date' => $today,
                'class_schedule_id' => null,
            ],
            [
                'attendance_type' => 'school',
                'clock_in' => $now->format('Y-m-d H:i:s'),
                'status' => $status,
                'notes' => $notes,
            ]
        );

        return redirect()->back()->with('success', "Clock In presensi {$student->name} berhasil dicatat oleh Guru {$teacherName}!");
    }

    public function resetStudentPhoto($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $student = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->findOrFail($id);

        if ($student->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($student->avatar);
        }
        if ($student->master_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->master_photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($student->master_photo);
        }

        $student->update([
            'avatar' => null,
            'master_photo' => null,
        ]);

        return redirect()->back()->with('success', "Foto profil {$student->name} berhasil di-reset. Siswa kini dapat mengunggah foto baru 1x lagi.");
    }

    public function updateBanner(Request $request)
    {
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $errorCode = $file->getError();
            $errorMessage = $file->getErrorMessage();

            \Illuminate\Support\Facades\Log::warning('Banner Image Upload Debug Log', [
                'is_valid' => $file->isValid(),
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'original_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'ini_upload_max_filesize' => ini_get('upload_max_filesize'),
                'ini_post_max_size' => ini_get('post_max_size'),
            ]);

            if (!$file->isValid()) {
                $errorExplanation = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas upload_max_filesize di php.ini server (' . ini_get('upload_max_filesize') . ').',
                    UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas MAX_FILE_SIZE form.',
                    UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload (upload_tmp_dir) di server tidak ditemukan.',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server (I/O error / permission error).',
                    default => 'Error upload PHP: ' . $errorMessage . ' (Kode: ' . $errorCode . ')',
                };

                return redirect()->back()->withErrors([
                    'banner_image' => 'File gambar banner gagal diunggah (PHP Error Code ' . $errorCode . ': ' . $errorExplanation . ')'
                ])->withInput();
            }
        }

        $request->validate([
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_title' => 'nullable|string|max:255',
            'banner_description' => 'nullable|string|max:500',
            'banner_color' => 'nullable|string|in:red,blue,green,slate'
        ], [
            'banner_image.uploaded' => 'File gambar banner gagal diunggah. Silakan pilih file dengan ukuran lebih kecil (maksimal 2MB).',
            'banner_image.max' => 'Ukuran file gambar banner maksimal adalah 2MB.',
            'banner_image.mimes' => 'Format gambar harus berupa JPEG, PNG, JPG, atau WEBP.',
        ]);

        $tenant = \App\Models\Tenant::find(Auth::user()->tenant_id);
        
        if ($request->hasFile('banner_image')) {
            $path = $this->compressAndStoreBanner($request->file('banner_image'), 'banners');
            
            // Delete old banner if exists
            if ($tenant->banner_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tenant->banner_path);
            }
            
            $tenant->banner_path = $path;
        }

        if ($request->filled('banner_title')) {
            $tenant->banner_title = $request->banner_title;
        }
        
        if ($request->filled('banner_description')) {
            $tenant->banner_description = $request->banner_description;
        }

        if ($request->filled('banner_color')) {
            $tenant->banner_color = $request->banner_color;
        }

        $tenant->save();

        return redirect()->back()->with('success', 'Pengaturan Banner berhasil diperbarui.');
    }

    /**
     * Kompres dan simpan file gambar banner ke direktori storage public.
     */
    private function compressAndStoreBanner($file, string $directory = 'banners'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = \Illuminate\Support\Str::random(40) . '.' . ($extension === 'png' ? 'png' : 'jpg');
        $destinationPath = $directory . '/' . $filename;
        
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($directory);
        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($destinationPath);

        if (!extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        try {
            $realPath = $file->getRealPath();
            if (!$realPath || !file_exists($realPath)) {
                return $file->store($directory, 'public');
            }

            $imageInfo = @getimagesize($realPath);
            if (!$imageInfo) {
                return $file->store($directory, 'public');
            }

            [$width, $height, $type] = $imageInfo;

            $srcImage = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($realPath),
                IMAGETYPE_PNG => @imagecreatefrompng($realPath),
                IMAGETYPE_WEBP => @imagecreatefromwebp($realPath),
                default => null,
            };

            if (!$srcImage) {
                return $file->store($directory, 'public');
            }

            $maxWidth = 1920;
            $maxHeight = 1080;

            $newWidth = $width;
            $newHeight = $height;

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int) round($width * $ratio);
                $newHeight = (int) round($height * $ratio);
            }

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($type === IMAGETYPE_PNG) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            if ($type === IMAGETYPE_PNG) {
                imagepng($dstImage, $fullPath, 7);
            } else {
                imagejpeg($dstImage, $fullPath, 82);
            }

            imagedestroy($srcImage);
            imagedestroy($dstImage);

            return $destinationPath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Banner image compression failed, fallback to store(): ' . $e->getMessage());
            return $file->store($directory, 'public');
        }
    }

    /**
     * Simpan Presensi KBM Kelas secara kolektif per jadwal pelajaran.
     */
    public function storeKbmAttendance(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:class_schedules,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|string|in:present,sick,permission,alpha,late',
            'attendances.*.notes' => 'nullable|string|max:255',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $teacherName = Auth::user()->name;

        $schedule = \App\Models\ClassSchedule::where('tenant_id', $tenantId)->findOrFail($request->schedule_id);

        $savedCount = 0;
        foreach ($request->attendances as $item) {
            $studentId = $item['student_id'];
            $status = $item['status'];
            $userNote = trim($item['notes'] ?? '');

            $auditNote = "Presensi KBM ({$schedule->subject?->name}) oleh Guru: {$teacherName}";
            $fullNote = $userNote ? ($userNote . " | " . $auditNote) : $auditNote;

            \App\Models\Attendance::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $studentId,
                    'date' => $today,
                    'class_schedule_id' => $schedule->id,
                ],
                [
                    'attendance_type' => 'class',
                    'clock_in' => $now,
                    'status' => $status,
                    'notes' => $fullNote,
                ]
            );
            $savedCount++;
        }

        $subjectName = $schedule->subject?->name ?? 'KBM';
        $className = $schedule->schoolClass?->full_name ?? 'Kelas';

        return redirect()->back()->with('success', "Presensi KBM {$subjectName} ({$className}) berhasil disimpan untuk {$savedCount} siswa!");
    }
}
