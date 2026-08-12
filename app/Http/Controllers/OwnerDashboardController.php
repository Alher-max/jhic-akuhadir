<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HeadmasterDashboardService;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OwnerDashboardController extends Controller
{
    public function __construct(
        protected HeadmasterDashboardService $dashboardService,
        protected InvitationService $invitationService
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', 'this_semester');
        $data = $this->dashboardService->getDashboardData($period);

        return view('owner.dashboard', $data);
    }

    // PENCARIAN CEPAT API
    public function searchStudent(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $lowerQuery = '%' . strtolower($query) . '%';

        $students = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('role', 'student')
            ->where(function($q) use ($lowerQuery) {
                $q->whereRaw("LOWER(name) LIKE ?", [$lowerQuery])
                  ->orWhereRaw("LOWER(nisn) LIKE ?", [$lowerQuery]);
            })
            ->with('schoolClass')
            ->take(10)
            ->get();

        return response()->json($students);
    }

    public function searchTeacher(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $lowerQuery = '%' . strtolower($query) . '%';

        $teachers = User::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('role', ['teacher', 'wali_kelas', 'operator', 'admin_dapodik'])
            ->where(function($q) use ($lowerQuery) {
                $q->whereRaw("LOWER(name) LIKE ?", [$lowerQuery])
                  ->orWhereRaw("LOWER(email) LIKE ?", [$lowerQuery]);
            })
            ->take(10)
            ->get();

        return response()->json($teachers);
    }

    public function inviteSuperAdmin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $this->invitationService->inviteOperator($request->email);

        return redirect()->back()->with('success', 'Undangan Operator Sekolah berhasil dibuat. Silakan bagikan tautan kepada yang bersangkutan.');
    }

    public function toggleSuperAdmin(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Password Kepala Sekolah tidak valid. Perubahan status dibatalkan!');
        }

        $this->invitationService->toggleOperatorStatus((int)$id);

        return redirect()->back()->with('success', 'Status Operator Sekolah berhasil diubah.');
    }
}
