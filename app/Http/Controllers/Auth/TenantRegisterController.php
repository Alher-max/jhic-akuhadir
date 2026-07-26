<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TenantRegisterController extends Controller
{
    public function checkSchoolCode($code)
    {
        $code = strtoupper(trim($code));
        $tenant = Tenant::where(function ($q) use ($code) {
            $q->where('code', $code);
            if (\Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                $q->orWhere('npsn', $code);
            }
        })->first();

        if ($tenant) {
            return response()->json([
                'success' => true,
                'school_name' => $tenant->name,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'NPSN / Kode Sekolah tidak ditemukan',
        ]);
    }

    public function searchStudents(Request $request)
    {
        $schoolCode = strtoupper(trim($request->query('school_code', '')));
        $query = trim($request->query('q', ''));

        if (empty($schoolCode) || strlen($query) < 2) {
            return response()->json([]);
        }

        $tenant = Tenant::where(function ($q) use ($schoolCode) {
            $q->where('code', $schoolCode);
            if (\Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                $q->orWhere('npsn', $schoolCode);
            }
        })->first();

        if (!$tenant) {
            return response()->json([]);
        }

        $students = User::with('schoolClass')
            ->where('tenant_id', $tenant->id)
            ->where('role', 'student')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('nisn', 'like', "%{$query}%");
            })
            ->take(10)
            ->get();

        $result = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn ?? '-',
                'class_name' => $student->schoolClass->nama_kelas ?? null,
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $roleType = $request->input('role_type', 'kepala_sekolah');
        $userEmail = $request->filled('email') ? strtolower(trim($request->email)) : null;
        
        $invitation = null;
        if (!empty($userEmail)) {
            $invitation = DB::table('invitations')->where('email', $userEmail)->where('status', 'pending')->first();
        }

        // Base Validation
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Dynamic Validation based on role_type
        if (in_array($roleType, ['kepala_sekolah', 'owner'])) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
            $rules['tenant_name'] = ['required', 'string', 'max:255'];
        } elseif (in_array($roleType, ['teacher', 'guru', 'manager'])) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
            if (!$invitation) {
                $rules['tenant_code'] = ['required', 'string', 'max:20'];
            }
        } elseif ($roleType === 'parent') {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
            if (!$invitation) {
                $rules['tenant_code'] = ['required', 'string', 'max:20'];
            }
        } else {
            // student / member
            $rules['name'] = ['nullable', 'string', 'max:255'];
            $rules['email'] = ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
            $rules['tenant_code'] = ['nullable', 'string', 'max:20'];
            $rules['nisn'] = ['nullable', 'string', 'max:50'];
        }

        $request->validate($rules);

        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        if (in_array($roleType, ['kepala_sekolah', 'owner'])) {
            $npsnCode = $request->filled('npsn') ? trim($request->npsn) : null;
            $code = $npsnCode ?: Str::upper(Str::random(8));
            while (Tenant::where('code', $code)->exists()) {
                $code = Str::upper(Str::random(8));
            }

            // Generate subdomain automatically
            $subdomain = Str::slug($request->tenant_name);
            if (Tenant::where('subdomain', $subdomain)->exists()) {
                $subdomain .= '-' . strtolower(Str::random(4));
            }

            $user = DB::transaction(function () use ($request, $code, $subdomain, $otpCode) {
                $tenantData = [
                    'name' => $request->tenant_name,
                    'institution_type' => 'Sekolah / Madrasah',
                    'business_category' => 'education',
                    'slug' => Str::slug($request->tenant_name) . '-' . strtolower($code),
                    'code' => $code,
                    'subdomain' => $subdomain,
                ];

                if ($request->filled('npsn') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                    $tenantData['npsn'] = $request->npsn;
                }

                $tenant = Tenant::create($tenantData);

                return User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'kepala_sekolah',
                    'otp_code' => $otpCode,
                    'otp_expires_at' => Carbon::now()->addMinutes(5)
                ]);
            });

            // Send OTP via Email
            app(\App\Services\OtpService::class)->sendOtp($user, $otpCode);
            session(['verify_email' => $user->email]);
            return redirect()->route('register.verify-otp');

        } else {
            // Manager/Teacher, Student, or Parent logic
            if (in_array($roleType, ['student', 'member'])) {
                $userNisn = trim($request->nisn ?? $request->student_nisn);
                if (empty($userNisn)) {
                    return back()->withErrors(['nisn' => 'NISN wajib diisi untuk registrasi/aktivasi siswa.'])->withInput();
                }

                // Query presisi tanpa Global Scope (bypass multi-tenancy) & select kolom yang dibutuhkan
                $studentQuery = \App\Models\Student::withoutGlobalScopes()
                    ->select(['id', 'tenant_id', 'name', 'nisn', 'birth_date', 'password', 'is_active', 'email_verified_at'])
                    ->where('nisn', $userNisn);

                if ($request->filled('birth_date')) {
                    $studentQuery->whereDate('birth_date', $request->birth_date);
                }

                $student = $studentQuery->first();

                if (!$student) {
                    return back()->withErrors(['nisn' => 'Kombinasi NISN dan Tanggal Lahir tidak cocok.'])->withInput();
                }

                if ((bool)$student->is_active && !empty($student->password) && !Hash::check('unactivated', $student->password)) {
                    return back()->withErrors(['nisn' => 'Akun dengan NISN ' . $userNisn . ' sudah diaktivasi. Silakan masuk melalui halaman Login.'])->withInput();
                }

                $student->update([
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                    'email_verified_at' => $student->email_verified_at ?? now(),
                ]);

                Auth::login($student);

                return redirect()->route('dashboard');
            }

            $actualRole = in_array($roleType, ['teacher', 'guru', 'manager']) ? 'teacher' : ($roleType === 'parent' ? 'parent' : 'student');
            if ($invitation && !empty($invitation->role)) {
                $actualRole = $invitation->role;
            }

            $userNisn = $request->nisn ?? $request->student_nisn;
            $isBypassOtp = empty($userEmail) && !empty($userNisn);

            if ($invitation && !empty($invitation->tenant_id)) {
                $tenant = Tenant::find($invitation->tenant_id);
            } else {
                $tenantCode = strtoupper(trim($request->tenant_code ?? ''));
                $tenant = Tenant::where(function ($q) use ($tenantCode) {
                    $q->where('code', $tenantCode);
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                        $q->orWhere('npsn', $tenantCode);
                    }
                })->first();
            }
            
            if (!$tenant) {
                throw ValidationException::withMessages([
                    'tenant_code' => 'NPSN / Kode Sekolah tidak ditemukan, silakan tanyakan kepada pimpinan Anda.',
                ]);
            }

            $user = DB::transaction(function () use ($request, $tenant, $otpCode, $actualRole, $userEmail, $userNisn, $isBypassOtp, $invitation) {
                $createdUser = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $request->name,
                    'email' => $userEmail,
                    'nisn' => $userNisn,
                    'password' => Hash::make($request->password),
                    'role' => $actualRole,
                    'onboarding_completed' => true,
                    'otp_code' => $isBypassOtp ? null : $otpCode,
                    'otp_expires_at' => $isBypassOtp ? null : Carbon::now()->addMinutes(5),
                    'email_verified_at' => $isBypassOtp ? Carbon::now() : null,
                ]);

                if ($invitation) {
                    DB::table('invitations')->where('id', $invitation->id)->update([
                        'status' => 'accepted',
                        'updated_at' => Carbon::now()
                    ]);
                }

                if ($actualRole === 'parent' && $request->filled('student_id')) {
                    User::where('id', $request->input('student_id'))
                        ->where('tenant_id', $tenant->id)
                        ->update(['parent_id' => $createdUser->id]);
                }

                return $createdUser;
            });

            if ($isBypassOtp) {
                return redirect()->route('login')->with('status', 'Registrasi berhasil! Silakan masuk menggunakan ID/NISN Anda.');
            }

            // Normal OTP flow for Teacher, Student, or Parent with email
            app(\App\Services\OtpService::class)->sendOtp($user, $otpCode);
            session(['verify_email' => $user->email]);
            return redirect()->route('register.verify-otp');
        }
    }
}
