<?php

declare(strict_types=1);

namespace App\Http\Controllers\Homeroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homeroom\BatchUpdateStudentReportRequest;
use App\Http\Requests\Homeroom\StoreExtracurricularGradeRequest;
use App\Jobs\SendReportPublishedWhatsAppNotificationJob;
use App\Models\AcademicYear;
use App\Models\ExtracurricularGrade;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\User;
use App\Services\HomeroomReportService;
use App\Services\ReportCardRendererService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeroomReportController extends Controller
{
    public function __construct(
        protected HomeroomReportService $reportService,
        protected ReportCardRendererService $rendererService
    ) {}

    /**
     * Memeriksa otorisasi umum pengguna untuk mengakses modul wali kelas.
     */
    protected function authorizeHomeroomAccess(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['wali_kelas', 'teacher', 'guru', 'guru_mapel', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        abort_unless(in_array($role, $allowed, true), 403, 'Akses ditolak. Halaman khusus wali kelas dan pimpinan.');
    }

    /**
     * Memastikan guru yang login adalah wali kelas dari rombel target, atau operator/kepsek.
     */
    protected function authorizeHomeroomForClass(SchoolClass $class): void
    {
        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);

        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return;
        }

        abort_unless((int) $class->wali_kelas_id === (int) $user->id, 403, 'Akses ditolak. Anda bukan wali kelas dari rombel ini.');
    }

    /**
     * Menampilkan daftar kelas asuhan wali kelas yang login.
     */
    public function index(Request $request): View
    {
        $this->authorizeHomeroomAccess();

        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);

        $query = SchoolClass::where('tenant_id', $tenantId)->with(['waliKelas', 'students']);

        if (!$isElevated) {
            $query->where('wali_kelas_id', $user->id);
        }

        $classes = $query->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('homeroom.reports.index', compact('classes', 'activeYear'));
    }

    /**
     * Menampilkan matriks leger nilai kelas (siswa x seluruh mata pelajaran).
     */
    public function leger(Request $request, SchoolClass $class): View|JsonResponse
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran aktif di sekolah Anda.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran aktif di sekolah Anda.');
        }

        $data = $this->reportService->buildLegerMatrix($class, $activeYear);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'class' => $class,
                'active_year' => $activeYear,
                'data' => $data,
            ]);
        }

        return view('homeroom.reports.leger', array_merge($data, [
            'class' => $class,
            'activeYear' => $activeYear,
        ]));
    }

    /**
     * Tampilan input rekap kehadiran siswa + sinkronisasi HadirYuk.
     */
    public function attendance(Request $request, SchoolClass $class): View
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $students = $class->students()->orderBy('name')->get();
        $reports = collect();

        if ($activeYear) {
            $reports = StudentReport::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('class_id', $class->id)
                ->get()
                ->keyBy('student_id');
        }

        return view('homeroom.reports.attendance', compact('class', 'activeYear', 'students', 'reports'));
    }

    /**
     * Tampilan input catatan perkembangan karakter & status kenaikan kelas.
     */
    public function notes(Request $request, SchoolClass $class): View
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $students = $class->students()->orderBy('name')->get();
        $reports = collect();

        if ($activeYear) {
            $reports = StudentReport::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('class_id', $class->id)
                ->get()
                ->keyBy('student_id');
        }

        return view('homeroom.reports.notes', compact('class', 'activeYear', 'students', 'reports'));
    }

    /**
     * Pengelolaan nilai ekstrakurikuler per siswa.
     */
    public function extracurricular(Request $request, SchoolClass $class): View
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $students = $class->students()->orderBy('name')->get();
        $reports = collect();

        if ($activeYear) {
            foreach ($students as $student) {
                StudentReport::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $class->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'wali_kelas_id' => auth()->id(),
                        'sick_count' => 0,
                        'permission_count' => 0,
                        'alpha_count' => 0,
                        'status' => 'draft',
                    ]
                );
            }

            $reports = StudentReport::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('class_id', $class->id)
                ->with('extracurriculars')
                ->get()
                ->keyBy('student_id');
        }

        return view('homeroom.reports.extracurricular', compact('class', 'activeYear', 'students', 'reports'));
    }

    /**
     * Melakukan auto-pull sinkronisasi presensi dari log attendances HadirYuk.
     */
    public function syncAttendance(Request $request, SchoolClass $class): RedirectResponse|JsonResponse
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran aktif untuk sinkronisasi presensi.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $synced = $this->reportService->syncAttendanceForClass($class, $activeYear, auth()->id());

        $message = "Berhasil menarik data presensi HadirYuk untuk {$synced} siswa.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'synced_count' => $synced,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Menyimpan perubahan data rekapitulasi kehadiran, catatan, dan status kenaikan kelas.
     */
    public function batchUpdate(BatchUpdateStudentReportRequest $request, SchoolClass $class): RedirectResponse|JsonResponse
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran aktif. Perubahan dibatalkan.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $validated = $request->validated();
        $user = auth()->user();

        $savedCount = DB::transaction(function () use ($validated, $tenantId, $activeYear, $class, $user) {
            $count = 0;
            foreach ($validated['reports'] as $item) {
                $attributes = [
                    'wali_kelas_id' => $user->id,
                ];

                if (array_key_exists('sick_count', $item) && $item['sick_count'] !== null) {
                    $attributes['sick_count'] = (int) $item['sick_count'];
                }
                if (array_key_exists('permission_count', $item) && $item['permission_count'] !== null) {
                    $attributes['permission_count'] = (int) $item['permission_count'];
                }
                if (array_key_exists('alpha_count', $item) && $item['alpha_count'] !== null) {
                    $attributes['alpha_count'] = (int) $item['alpha_count'];
                }
                if (array_key_exists('homeroom_notes', $item)) {
                    $attributes['homeroom_notes'] = $item['homeroom_notes'];
                }
                if (array_key_exists('promotion_status', $item)) {
                    $attributes['promotion_status'] = $item['promotion_status'];
                }
                if (array_key_exists('status', $item) && !empty($item['status'])) {
                    $attributes['status'] = $item['status'];
                }

                StudentReport::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $class->id,
                        'student_id' => (int) $item['student_id'],
                    ],
                    $attributes
                );
                $count++;
            }
            return $count;
        });

        $message = "Berhasil memperbarui data rapor untuk {$savedCount} siswa.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Menyimpan nilai ekstrakurikuler siswa.
     */
    public function storeExtracurricular(StoreExtracurricularGradeRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorizeHomeroomAccess();

        $validated = $request->validated();
        $tenantId = (int) auth()->user()->tenant_id;

        $entry = ExtracurricularGrade::create([
            'tenant_id' => $tenantId,
            'student_report_id' => (int) $validated['student_report_id'],
            'activity_name' => $validated['activity_name'],
            'predicate' => $validated['predicate'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai ekstrakurikuler berhasil ditambahkan.',
                'data' => $entry,
            ], 201);
        }

        return back()->with('success', 'Nilai ekstrakurikuler berhasil ditambahkan.');
    }

    /**
     * Menghapus nilai ekstrakurikuler.
     */
    public function destroyExtracurricular(Request $request, ExtracurricularGrade $extracurricularGrade): RedirectResponse|JsonResponse
    {
        $this->authorizeHomeroomAccess();

        $user = auth()->user();
        abort_if((int) $extracurricularGrade->tenant_id !== (int) $user->tenant_id, 404);

        $report = $extracurricularGrade->studentReport;
        if ($report && $report->schoolClass) {
            $this->authorizeHomeroomForClass($report->schoolClass);
        }

        $extracurricularGrade->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai ekstrakurikuler berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Nilai ekstrakurikuler berhasil dihapus.');
    }

    /**
     * Mempublikasikan rapor seluruh siswa di kelas (draft -> published) dan men-generate hash verifikasi unik.
     */
    public function publishClassReports(Request $request, SchoolClass $class): RedirectResponse|JsonResponse
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran aktif.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $students = $class->students()->get();

        $publishedReports = [];
        $publishedCount = DB::transaction(function () use ($students, $tenantId, $activeYear, $class, &$publishedReports) {
            $count = 0;
            foreach ($students as $student) {
                $report = StudentReport::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $class->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'wali_kelas_id' => auth()->id(),
                        'sick_count' => 0,
                        'permission_count' => 0,
                        'alpha_count' => 0,
                        'status' => 'draft',
                    ]
                );

                $report->status = 'published';
                $report->published_at = now();
                if (empty($report->verification_hash)) {
                    $report->verification_hash = hash('sha256', (string) $report->id . '-' . (string) $tenantId . '-' . \Illuminate\Support\Str::random(32));
                }
                $report->save();
                $publishedReports[] = $report;
                $count++;
            }
            return $count;
        });

        // Pengiriman notifikasi WhatsApp otomatis ke orang tua & siswa secara asinkron
        $notifyWhatsApp = $request->boolean('notify_whatsapp', true);
        if ($notifyWhatsApp) {
            foreach ($publishedReports as $publishedReport) {
                SendReportPublishedWhatsAppNotificationJob::dispatch($publishedReport);
            }
        }

        $message = "Rapor kelas berhasil dipublikasikan dan antrean notifikasi WhatsApp telah dikirimkan ke orang tua.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'published_count' => $publishedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Mengunci seluruh rapor siswa di kelas agar tidak dapat diedit kembali oleh guru mapel.
     */
    public function lockClassReports(Request $request, SchoolClass $class): RedirectResponse|JsonResponse
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran aktif.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $lockedCount = DB::transaction(function () use ($tenantId, $activeYear, $class) {
            return StudentReport::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('class_id', $class->id)
                ->update(['status' => 'locked']);
        });

        $message = "Berhasil mengunci rapor kelas untuk {$lockedCount} siswa.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'locked_count' => $lockedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Menampilkan lembar cetak A4 resmi untuk satu siswa tertentu.
     */
    public function printSingle(Request $request, SchoolClass $class, User $student): View
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $user = auth()->user();
        abort_if((int) $student->tenant_id !== (int) $user->tenant_id, 404);
        abort_unless((int) $student->class_id === (int) $class->id, 404);

        $tenantId = (int) $user->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();
        abort_unless($activeYear, 404, 'Tahun ajaran aktif tidak ditemukan.');

        $report = StudentReport::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'academic_year_id' => $activeYear->id,
                'class_id' => $class->id,
                'student_id' => $student->id,
            ],
            [
                'wali_kelas_id' => $user->id,
                'sick_count' => 0,
                'permission_count' => 0,
                'alpha_count' => 0,
                'status' => 'draft',
            ]
        );

        $data = $this->rendererService->getStudentReportCardData($report);

        return view('reports.print-single', $data);
    }

    /**
     * Menampilkan lembar cetak A4 resmi massal untuk seluruh siswa dalam satu rombel.
     */
    public function printBatch(Request $request, SchoolClass $class): View
    {
        $this->authorizeHomeroomAccess();
        $this->authorizeHomeroomForClass($class);

        $tenantId = (int) auth()->user()->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();
        abort_unless($activeYear, 404, 'Tahun ajaran aktif tidak ditemukan.');

        $cardItems = $this->rendererService->getClassReportCardsData($class, $activeYear);

        return view('reports.print-batch', [
            'cardItems' => $cardItems,
            'schoolClass' => $class,
            'academicYear' => $activeYear,
        ]);
    }
}
