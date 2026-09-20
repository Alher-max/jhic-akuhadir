<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceScanController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $operator = $request->user();
        $code = trim($validated['code']);
        $studentQuery = User::withoutGlobalScopes()
            ->where('tenant_id', $operator->tenant_id)
            ->where('role', 'student')
            ->with('schoolClass');

        if (preg_match('/^STD-(\d+)$/i', $code, $matches)) {
            $studentQuery->whereKey((int) $matches[1]);
        } else {
            $studentQuery->where(function ($query) use ($code) {
                $query->where('nisn', $code)->orWhere('nis', $code);
            });
        }

        $student = $studentQuery->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu tidak dikenali atau siswa bukan bagian dari sekolah ini.',
            ], 404);
        }

        $now = Carbon::now($student->tenant?->timezone ?? config('app.timezone'));
        $date = $now->toDateString();

        if (!$this->attendanceService->isAttendanceDayAllowed($student->tenant, $student, $now)) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi hari ini belum diaktifkan dan tidak ada jadwal resmi.',
            ], 422);
        }

        $existing = Attendance::where('tenant_id', $operator->tenant_id)
            ->where('user_id', $student->id)
            ->where('attendance_type', 'school')
            ->whereDate('date', $date)
            ->whereNotNull('clock_in')
            ->first();
        if ($existing) {
            return $this->duplicateResponse($student, $existing);
        }

        $status = $this->attendanceService->validateAttendanceStatus(
            $student->tenant,
            $now->format('H:i:s'),
            $this->attendanceService->getDayNameInIndonesian($now),
            null
        )['status'];

        $attendance = Attendance::create([
            'user_id' => $student->id,
            'tenant_id' => $operator->tenant_id,
            'recorded_by_user_id' => $operator->id,
            'attendance_type' => 'school',
            'date' => $date,
            'clock_in' => $now,
            'status' => $status,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dicatat',
            'student' => $this->studentPayload($student),
            'status' => $status,
            'time' => $attendance->clock_in->format('H:i'),
        ]);
    }

    private function duplicateResponse(User $student, Attendance $attendance): JsonResponse
    {
        return response()->json([
            'success' => false,
            'already_attended' => true,
            'message' => 'Siswa sudah presensi hari ini pada ' . $attendance->clock_in->format('H:i'),
            'student' => $this->studentPayload($student),
        ], 409);
    }

    private function studentPayload(User $student): array
    {
        $avatar = $student->avatar ?: $student->master_photo;

        return [
            'id' => $student->id,
            'name' => $student->name,
            'nisn' => $student->nisn,
            'class_name' => $student->schoolClass?->full_name ?? $student->schoolClass?->nama_kelas,
            'avatar_url' => $avatar ? Storage::url($avatar) : null,
        ];
    }
}
