<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /**
     * Memastikan hanya operator dan kepala sekolah yang dapat mengakses controller ini.
     */
    protected function authorizeAccess(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        abort_unless(in_array($role, $allowed, true), 403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
    }

    /**
     * Menampilkan daftar tahun ajaran milik tenant aktif.
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess();

        $tenantId = auth()->user()->tenant_id;

        $academicYears = AcademicYear::where('tenant_id', $tenantId)
            ->orderBy('name', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        $activeYear = AcademicYear::where('tenant_id', $tenantId)
            ->active()
            ->first();

        return view('admin.academic-years.index', compact('academicYears', 'activeYear'));
    }

    /**
     * Form penambahan (fallback rute GET jika diakses langsung).
     */
    public function create(): RedirectResponse
    {
        $this->authorizeAccess();
        return redirect()->route('admin.academic-years.index')->with('open_create_modal', true);
    }

    /**
     * Menyimpan data tahun ajaran baru dengan integritas single active invariant.
     */
    public function store(StoreAcademicYearRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess();

        $validated = $request->validated();
        $tenantId = auth()->user()->tenant_id;

        $academicYear = DB::transaction(function () use ($validated, $tenantId) {
            $isActive = !empty($validated['is_active']);

            if ($isActive) {
                AcademicYear::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenantId)
                    ->update(['is_active' => false]);
            }

            return AcademicYear::create([
                'tenant_id' => $tenantId,
                'name' => $validated['name'],
                'semester' => (string) $validated['semester'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $isActive,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil ditambahkan.',
                'data' => $academicYear,
            ], 201);
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Form edit (fallback rute GET jika diakses langsung).
     */
    public function edit(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorizeAccess();
        $this->checkTenantOwnership($academicYear);

        return redirect()->route('admin.academic-years.index')
            ->with('edit_id', $academicYear->id);
    }

    /**
     * Memperbarui data tahun ajaran dengan menjaga single active invariant.
     */
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess();
        $this->checkTenantOwnership($academicYear);

        $validated = $request->validated();
        $tenantId = auth()->user()->tenant_id;

        DB::transaction(function () use ($academicYear, $validated, $tenantId) {
            $isActive = !empty($validated['is_active']);

            if ($isActive) {
                AcademicYear::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenantId)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);
            }

            $academicYear->update([
                'name' => $validated['name'],
                'semester' => (string) $validated['semester'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $isActive,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diperbarui.',
                'data' => $academicYear->fresh(),
            ]);
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Menghapus tahun ajaran.
     */
    public function destroy(Request $request, AcademicYear $academicYear): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess();
        $this->checkTenantOwnership($academicYear);

        $academicYear->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Mengaktifkan tahun ajaran secara aman (Single Active Year Invariant).
     */
    public function activate(Request $request, AcademicYear $academicYear): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess();
        $this->checkTenantOwnership($academicYear);

        $academicYear->activate();

        $message = "Tahun ajaran {$academicYear->name} ({$academicYear->semester_label}) berhasil diaktifkan.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $academicYear->fresh(),
            ]);
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', $message);
    }

    /**
     * Memastikan data milik tenant yang sedang login.
     */
    protected function checkTenantOwnership(AcademicYear $academicYear): void
    {
        $currentTenantId = auth()->user()?->tenant_id;
        if ((int) $academicYear->tenant_id !== (int) $currentTenantId) {
            abort(404, 'Tahun ajaran tidak ditemukan.');
        }
    }
}
