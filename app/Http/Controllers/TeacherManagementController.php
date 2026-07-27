<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeacherManagementController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $selectedRole = $request->get('role');
        
        $search = $request->get('search');
        
        $query = User::with('homeroomClasses')
            ->where('tenant_id', $tenantId)
            ->whereIn('role', ['teacher', 'guru_kelas', 'guru', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka']);
            
        if (!empty($selectedRole)) {
            $query->where('role', $selectedRole);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('email', 'like', '%'.$search.'%')
                  ->orWhere('nisn', 'like', '%'.$search.'%');
            });
        }
            
        $teachers = $query->orderBy('name')->paginate(10)->withQueryString();
        $classes = \App\Models\SchoolClass::where('tenant_id', $tenantId)->ordered()->get();
            
        return view('operator.teachers.index', compact('teachers', 'selectedRole', 'search', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required_without:teachers_file|string|max:255',
            'email' => 'nullable|email|max:255',
            'nip' => 'nullable|string|max:50',
            'role' => 'nullable|string|in:teacher,guru_kelas,guru,guru_bk,guru_inklusi,guru_kejuruan,wali_kelas,headmaster,manager_teacher,staff,pustakawan,laboran,it_support,satpam,caraka',
            'avatar' => 'nullable|image|max:2048',
            'teachers_file' => 'nullable|file|mimes:csv,txt|max:2048'
        ]);

        if ($request->has('name') && $request->name) {
            $this->validateAndCreateTeacher($request);
        }

        if ($request->hasFile('teachers_file')) {
            try {
                $this->importFromCsv($request->file('teachers_file'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal memproses baris CSV: ' . $e->getMessage());
            }
        }

        return redirect()->route('operator.teachers.index')->with('success', 'Data Pendidik / Staf berhasil ditambahkan.');
    }
    
    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'nip' => 'nullable|string|max:50',
        ]);
        
        $teacher = $this->validateAndCreateTeacher($request);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pendidik / Guru berhasil ditambahkan.',
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name
                ]
            ]);
        }
        
        return redirect()->back()->with('success', 'Pendidik / Guru berhasil ditambahkan dan dapat dipilih.');
    }
    
    private function validateAndCreateTeacher(Request $request)
    {
        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);
        
        $email = $request->email;
        if (!$email) {
            $identifier = $request->nip ?: Str::random(6);
            $email = 'guru.' . $identifier . '@hadirsekolah.id';
        }
        
        $nipOrPhone = $request->nip;
        $defaultPassword = 'hadiryuk123';
        if (!empty($nipOrPhone)) {
            $defaultPassword = $nipOrPhone;
        }

        $password = Hash::make($defaultPassword);
        $role = $request->role ?: 'wali_kelas';

        $existing = User::where('email', $email)->first();
        if ($existing) return $existing;

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars/' . $tenant->id, 'public');
        }

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $email,
            'avatar' => $avatarPath,
            'nisn' => $request->nip,
            'password' => $password,
            'must_change_password' => true,
            'role' => $role,
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => Carbon::now(),
        ]);
    }

    private function importFromCsv($file)
    {
        $tenantId = Auth::user()->tenant_id;
        $content = file_get_contents($file->getRealPath());
        $lines = explode("\n", str_replace("\r", "", $content));
        
        $isFirstRow = true;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if ($isFirstRow) {
                $isFirstRow = false;
                $lowerLine = strtolower(str_replace(' ', '', $line));
                if (str_starts_with($lowerLine, 'nama,nip') || str_starts_with($lowerLine, 'nama;') || str_starts_with($lowerLine, 'name,') || str_starts_with($lowerLine, 'name;')) {
                    continue;
                }
            }

            $delimiter = strpos($line, ';') !== false ? ';' : ',';
            $items = str_getcsv($line, $delimiter);
            
            // Smart Reverse Parsing: if split fails to yield at least 4 items and it's comma delimited, try raw explode
            if (count($items) < 4 && $delimiter === ',') {
                $items = explode(',', $line);
            }
            
            if (count($items) >= 4) {
                $roleInput = trim(trim(array_pop($items)), " \t\n\r\0\x0B\"'"); // Ambil elemen TERAKHIR (Role)
                $emailInput = trim(trim(array_pop($items)), " \t\n\r\0\x0B\"'"); // Ambil elemen KEDUA DARI BELAKANG (Email)
                $nipInput = trim(trim(array_pop($items)), " \t\n\r\0\x0B\"'"); // Ambil elemen KETIGA DARI BELAKANG (NIP)
                $name = trim(trim(implode(', ', $items)), " \t\n\r\0\x0B\"'"); // SISA elemen di depan disatukan kembali sebagai NAMA
                
                $nip = !empty($nipInput) ? $nipInput : null;
                
                $cleanNama = \Illuminate\Support\Str::slug(explode(',', $name)[0]);
                $fallbackEmail = $cleanNama . '@smkn2garuda.sch.id';
                
                $email = !empty($emailInput) ? $emailInput : $fallbackEmail;
                $roleInput = !empty($roleInput) ? strtolower($roleInput) : 'guru';
                
                $roleMap = [
                    'guru kelas' => 'guru_kelas',
                    'guru' => 'guru',
                    'guru bk' => 'guru_bk',
                    'guru inklusi' => 'guru_inklusi',
                    'guru kejuruan' => 'guru_kejuruan',
                    'wali kelas' => 'wali_kelas',
                    'kepala sekolah' => 'headmaster',
                    'wakil kepala sekolah' => 'manager_teacher',
                    'wakasek' => 'manager_teacher',
                    'manajemen' => 'manager_teacher',
                    'staf' => 'staff',
                    'staff' => 'staff',
                    'tu' => 'staff',
                    'tata usaha' => 'staff',
                    'pustakawan' => 'pustakawan',
                    'laboran' => 'laboran',
                    'it support' => 'it_support',
                    'teknisi' => 'it_support',
                    'satpam' => 'satpam',
                    'keamanan' => 'satpam',
                    'caraka' => 'caraka',
                    'kebersihan' => 'caraka'
                ];
                
                // Jika roleInput sudah merupakan key yang valid (misal: 'headmaster'), gunakan langsung
                if (in_array($roleInput, array_values($roleMap))) {
                    $role = $roleInput;
                } else {
                    $role = $roleMap[$roleInput] ?? 'guru';
                }
                
                if (!empty($name)) {
                    $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
                    if (!$isEmail) {
                        $email = $fallbackEmail;
                    }
                    
                    $nisnValue = $nip;
                    $defaultPassword = 'hadiryuk123';
                    if (!empty($nisnValue)) {
                        $defaultPassword = $nisnValue;
                    }
                    
                    User::updateOrCreate(
                        ['email' => $email],
                        [
                            'tenant_id' => $tenantId,
                            'name' => $name,
                            'nisn' => $nisnValue,
                            'password' => Hash::make($defaultPassword),
                            'must_change_password' => true,
                            'role' => $role,
                            'is_active' => true,
                            'onboarding_completed' => true,
                            'email_verified_at' => Carbon::now(),
                        ]
                    );
                }
            }
        }
    }
    
    public function update(Request $request, User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id || !in_array($teacher->role, ['teacher', 'guru_kelas', 'guru', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'])) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($teacher->id)],
            'nip' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('users', 'nisn')->ignore($teacher->id)],
            'role' => 'required|string|in:teacher,guru_kelas,guru,guru_bk,guru_inklusi,guru_kejuruan,wali_kelas,headmaster,manager_teacher,staff,pustakawan,laboran,it_support,satpam,caraka',
            'is_active' => 'required|boolean',
            'class_id' => 'nullable|exists:school_classes,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $email = $request->email;
        if (!$email && !$teacher->email) {
            $identifier = $request->nip ?: Str::random(6);
            $email = 'guru.' . $identifier . '@hadirsekolah.id';
        } elseif (!$email) {
            $email = $teacher->email;
        }

        $updateData = [
            'name' => $request->name,
            'email' => $email,
            'nisn' => $request->nip,
            'role' => $request->role,
            'is_active' => (bool)$request->is_active,
        ];

        if ($request->hasFile('avatar')) {
            if ($teacher->avatar) {
                Storage::disk('public')->delete($teacher->avatar);
            }
            $updateData['avatar'] = $request->file('avatar')->store('avatars/' . Auth::user()->tenant_id, 'public');
        }

        $teacher->update($updateData);

        if ($request->has('class_id')) {
            \App\Models\SchoolClass::where('tenant_id', Auth::user()->tenant_id)
                ->where('wali_kelas_id', $teacher->id)
                ->update(['wali_kelas_id' => null]);

            if (!empty($request->class_id)) {
                $schoolClass = \App\Models\SchoolClass::where('tenant_id', Auth::user()->tenant_id)
                    ->where('id', $request->class_id)
                    ->first();
                if ($schoolClass) {
                    $schoolClass->update(['wali_kelas_id' => $teacher->id]);
                }
            }
        }

        return redirect()->back()->with('success', 'Data Pendidik / Staf berhasil diperbarui.');
    }

    public function show(User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id || !in_array($teacher->role, ['teacher', 'guru_kelas', 'guru', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'])) {
            abort(403);
        }

        $teacher->load('homeroomClasses');
        $classes = \App\Models\SchoolClass::where('tenant_id', Auth::user()->tenant_id)->ordered()->get();
        
        return view('operator.teachers.show', compact('teacher', 'classes'));
    }

    public function destroy(User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id || !in_array($teacher->role, ['teacher', 'guru_kelas', 'guru', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'])) {
            abort(403);
        }
        
        $teacher->delete();

        return redirect()->route('operator.teachers.index')->with('success', 'Pendidik / Staf berhasil dihapus.');
    }

    public function resetPassword(User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $defaultPassword = 'hadiryuk123';
        if (!empty($teacher->nisn)) {
            $defaultPassword = $teacher->nisn;
        }

        $teacher->update([
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
        ]);

        return redirect()->back()->with('success', "Password {$teacher->name} berhasil direset menjadi default: '{$defaultPassword}'");
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_ptk.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Lengkap', 'NIP / NUPTK', 'Email', 'Peran'];
        
        $data = [
            ['Drs. Supriyadi, M.Si.', '197501122000031001', 'supriyadi@smkn2garuda.sch.id', 'headmaster'],
            ['Hendra Gunawan, S.Kom.', '198807092015031002', 'hendra.gunawan@smkn2garuda.sch.id', 'it_support'],
            ['Siti Rahmah, S.Ag.', '198203112006042005', 'siti.rahmah@smkn2garuda.sch.id', 'guru']
        ];

        $callback = function() use($columns, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
