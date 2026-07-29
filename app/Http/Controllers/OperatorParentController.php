<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OperatorParentController extends Controller
{
    /**
     * Tampilkan daftar orang tua beserta siswa yang terhubung.
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = User::where('tenant_id', $tenantId)
            ->where('role', 'parent')
            ->with(['students.schoolClass']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        $parents = $query->orderBy('name')->paginate(15)->withQueryString();

        $allStudents = User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        return view('operator.parents.index', compact('parents', 'allStudents'));
    }

    /**
     * Simpan akun orang tua baru yang dibuat langsung oleh Operator.
     */
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'parent_phone' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required'  => 'Nama lengkap orang tua wajib diisi.',
            'email.email'    => 'Format alamat surel tidak valid.',
            'email.unique'   => 'Alamat surel sudah terdaftar di sistem.',
        ]);

        // Auto-generate dummy email jika dikosongkan
        $email = $request->filled('email')
            ? $request->email
            : 'ortu.' . time() . rand(100, 999) . '@hadirsekolah.id';

        // Buat user orang tua sementara untuk resolve default password
        $parentData = [
            'tenant_id'            => $tenantId,
            'name'                 => $request->name,
            'email'                => $email,
            'parent_phone'         => $request->parent_phone,
            'role'                 => 'parent',
            'is_active'            => true,
            'onboarding_completed' => true,
            'email_verified_at'    => now(),
            'password'             => Hash::make('placeholder'),
        ];

        $parent = User::create($parentData);

        // Terapkan default password sesuai policy (No. HP → '12345678')
        $defaultPassword = $parent->getDefaultPassword();
        $parent->update(['password' => Hash::make($defaultPassword)]);

        return redirect()->route('operator.parents.index')
            ->with('success', "Akun orang tua {$parent->name} berhasil dibuat. Password default: {$defaultPassword}");
    }

    /**
     * Tautkan siswa ke akun orang tua via pivot parent_student.
     */
    public function linkStudent(Request $request, User $parent)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($parent->tenant_id !== $tenantId) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'student_id' => 'required|exists:users,id',
            'relationship' => 'required|in:Ayah,Ibu,Wali',
        ], [
            'student_id.required' => 'Pilihan siswa wajib diisi.',
            'student_id.exists' => 'Siswa yang dipilih tidak terdaftar.',
            'relationship.required' => 'Hubungan keluarga wajib dipilih.',
            'relationship.in' => 'Hubungan keluarga harus berupa Ayah, Ibu, atau Wali.',
        ]);

        $student = User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->findOrFail($request->student_id);

        $parent->students()->syncWithoutDetaching([
            $student->id => ['relationship' => $request->relationship]
        ]);

        return redirect()->back()->with('success', "Berhasil menautkan siswa {$student->name} ({$request->relationship}) ke akun orang tua {$parent->name}.");
    }

    /**
     * Lepas tautan siswa dari akun orang tua.
     */
    public function unlinkStudent(User $parent, User $student)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($parent->tenant_id !== $tenantId || $student->tenant_id !== $tenantId) {
            abort(403, 'Akses ditolak.');
        }

        $parent->students()->detach($student->id);

        return redirect()->back()->with('success', "Berhasil melepas tautan siswa {$student->name} dari akun orang tua {$parent->name}.");
    }

    /**
     * Reset password akun orang tua ke default.
     */
    public function resetPassword(User $parent)
    {
        $tenantId = Auth::user()->tenant_id;
        if ($parent->tenant_id !== $tenantId || $parent->role !== 'parent') {
            abort(403, 'Akses ditolak.');
        }

        $defaultPassword = $parent->getDefaultPassword();

        $parent->update([
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
            'is_password_changed' => false,
        ]);

        return redirect()->back()->with('success', "Password akun orang tua {$parent->name} berhasil di-reset ke: {$defaultPassword}");
    }
}
