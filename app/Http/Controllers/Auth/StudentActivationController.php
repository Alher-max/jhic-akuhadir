<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class StudentActivationController extends Controller
{
    /**
     * Tampilkan form registrasi / aktivasi akun siswa.
     */
    public function create()
    {
        return view('auth.student-activate');
    }

    /**
     * Proses registrasi / aktivasi akun siswa pertama kali (Opsi A - Tanpa Kode Sekolah).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nisn_or_email' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nisn_or_email.required' => 'NISN atau Email siswa wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $input = trim($request->input('nisn_or_email'));

        // Cari data siswa di tabel users (role student) berdasarkan NISN atau Email tanpa global scope tenancy
        $student = User::withoutGlobalScopes()
            ->where('role', 'student')
            ->where(function ($q) use ($input) {
                $q->where('nisn', $input)
                  ->orWhere('email', strtolower($input));
            })
            ->first();

        // SKENARIO 1: Data Tidak Ditemukan
        if (!$student) {
            throw ValidationException::withMessages([
                'nisn_or_email' => 'Data NISN atau Email belum terdaftar di sistem sekolah. Silakan hubungi Wali Kelas atau Operator Anda.',
            ]);
        }

        // SKENARIO 2: Data Ada tapi Sudah Aktif / Punya Password
        if ((bool)$student->is_active && !empty($student->password)) {
            throw ValidationException::withMessages([
                'nisn_or_email' => 'Akun dengan NISN/Email ini sudah diaktivasi. Silakan masuk melalui halaman Login.',
            ]);
        }

        // SKENARIO 3: Data Ada & Belum Aktif
        $student->update([
            'password' => Hash::make($request->password),
            'is_active' => true,
            'email_verified_at' => $student->email_verified_at ?? now(),
        ]);

        Auth::login($student);

        return redirect()->route('dashboard');
    }
}
