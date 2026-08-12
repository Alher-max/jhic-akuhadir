<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
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
        $selectedPosition = $request->get('position');

        $search = $request->get('search');

         $query = User::with('homeroomClasses')
             ->where('tenant_id', $tenantId)
             ->whereIn('role', User::getTeacherRoles());

        if (!empty($selectedPosition)) {
            $query->where('position', $selectedPosition);
        }

         if (!empty($search)) {
             $query->where(function($q) use ($search) {
                 $q->where('name', 'ilike', '%'.$search.'%')
                   ->orWhere('email', 'ilike', '%'.$search.'%')
                   ->orWhere('nisn', 'ilike', '%'.$search.'%');
             });
         }

        $teachers = $query->orderBy('name')->paginate(10)->withQueryString();
        $classes = \App\Models\SchoolClass::where('tenant_id', $tenantId)->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('operator.teachers.index', compact('teachers', 'selectedPosition', 'search', 'classes'));
    }

    public function store(StoreTeacherRequest $request)
    {
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
            $identifier = $request->nip ?: Str::lower(Str::random(6));
            $email = 'guru.' . $identifier . '@hadirsekolah.id';
        }

        $nipOrPhone = $request->nip;
        $defaultPassword = 'hadiryuk123';
        if (!empty($nipOrPhone)) {
            $defaultPassword = $nipOrPhone;
        }

        $password = Hash::make($defaultPassword);
        $position = $request->role ?: 'guru';

        // Determine system role based on position for access control
        $systemRole = User::getSystemRoleFromPosition($position);

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
            'role' => $systemRole, // System role for access control
            'position' => $position, // Actual position/jabatan
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
                // Enhanced header detection for various formats
                if (str_starts_with($lowerLine, 'nama,nip') || str_starts_with($lowerLine, 'nama;') || 
                    str_starts_with($lowerLine, 'name,') || str_starts_with($lowerLine, 'name;') ||
                    str_starts_with($lowerLine, 'namalengkap,nuptk') || str_starts_with($lowerLine, 'namalengkap;nuptk') ||
                    str_starts_with($lowerLine, 'namalengkap,nuptk,email') || str_starts_with($lowerLine, 'namalengkap;nuptk;email') ||
                    // Check if it looks like a header by examining column content after splitting
                    $this->isHeaderRow($line)) {
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
                    'guru mapel' => 'guru_mapel',
                    'guru penggerak' => 'guru_penggerak',
                    'guru koordinator' => 'guru_penggerak',
                    'guru' => 'guru',
                    'guru bk' => 'guru_bk',
                    'guru inklusi' => 'guru_inklusi',
                    'guru kejuruan' => 'guru_kejuruan',
                    'wali kelas' => 'wali_kelas',
                    'kepala sekolah' => 'kepala_sekolah',
                    'headmaster' => 'kepala_sekolah',
                    'wakil kepala sekolah' => 'guru',
                    'wakasek' => 'guru',
                    'manajemen' => 'operator',
                    'operator' => 'operator',
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
                    'kebersihan' => 'caraka',
                    'siswa' => 'siswa',
                    'parent' => 'parent'
                ];

                // Jika roleInput sudah merupakan clean role yang valid, gunakan langsung
                if (in_array($roleInput, ['operator', 'kepala_sekolah', 'guru', 'siswa', 'parent'])) {
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

                    $position = $role; // Use the mapped role as position
                    $systemRole = User::getSystemRoleFromPosition($position);

                    User::updateOrCreate(
                        ['email' => $email],
                        [
                            'tenant_id' => $tenantId,
                            'name' => $name,
                            'nisn' => $nisnValue,
                            'password' => Hash::make($defaultPassword),
                            'must_change_password' => true,
                            'role' => $systemRole, // System role for access control
                            'position' => $position, // Actual position/jabatan
                            'is_active' => true,
                            'onboarding_completed' => true,
                            'email_verified_at' => Carbon::now(),
                        ]
                    );
                }
            }
        }
    }

    public function update(UpdateTeacherRequest $request, User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id || !in_array($teacher->role, User::getTeacherRoles())) {
            abort(403);
        }

        $email = $request->email;
        if (!$email && !$teacher->email) {
            $identifier = $request->nip ?: Str::lower(Str::random(6));
            $email = 'guru.' . $identifier . '@hadirsekolah.id';
        } elseif (!$email) {
            $email = $teacher->email;
        }

        $position = $request->role;
        $systemRole = User::getSystemRoleFromPosition($position);

        $updateData = [
            'name' => $request->name,
            'email' => $email,
            'nisn' => $request->nip,
            'role' => $systemRole, // System role for access control
            'position' => $position, // Actual position/jabatan
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
        if ($teacher->tenant_id !== Auth::user()->tenant_id || !in_array($teacher->role, User::getTeacherRoles())) {
            abort(403);
        }

        $teacher->load('homeroomClasses');
        $classes = \App\Models\SchoolClass::where('tenant_id', Auth::user()->tenant_id)->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('operator.teachers.show', compact('teacher', 'classes'));
    }

    public function destroy(User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id || !in_array($teacher->role, User::getTeacherRoles())) {
            abort(403);
        }

        // Prevent self-deletion
        if ($teacher->id === Auth::id()) {
            return redirect()->route('operator.teachers.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $teacher->delete();

        return redirect()->route('operator.teachers.index')->with('success', 'Pendidik / Staf berhasil dihapus.');
    }

    public function resetPassword(User $teacher)
    {
        if ($teacher->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $teacher->load('profile');
        
        // For teachers with dummy emails, always use '12345678' as default password
        $isDummyEmail = str_starts_with($teacher->email, 'guru.') && str_ends_with($teacher->email, '@hadirsekolah.id');
        $newPassword = $isDummyEmail ? '12345678' : $teacher->getDefaultPassword();

        $teacher->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true,
        ]);

        return redirect()->back()->with('success', "Password {$teacher->name} berhasil direset menjadi default: '{$newPassword}'");
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
            ['Drs. Supriyadi, M.Si.', '197501122000031001', 'supriyadi@smkn2garuda.sch.id', 'kepala_sekolah'],
            ['Hendra Gunawan, S.Kom.', '198807092015031002', 'hendra.gunawan@smkn2garuda.sch.id', 'it_support'],
            ['Siti Rahmah, S.Ag.', '198203112006042005', 'siti.rahmah@smkn2garuda.sch.id', 'guru_mapel'],
            ['Budi Santoso, S.Pd.', '199005152018031003', 'budi.santoso@smkn2garuda.sch.id', 'wali_kelas'],
            ['Dewi Lestari, S.Pd.', '199208202019032004', 'dewi.lestari@smkn2garuda.sch.id', 'guru_bk'],
            ['Ahmad Fauzi, S.T.', '198511102010031005', 'ahmad.fauzi@smkn2garuda.sch.id', 'guru_kejuruan'],
            ['Sari Indah, S.Pd.', '199503252020032006', 'sari.indah@smkn2garuda.sch.id', 'staff'],
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

    public function exportCsv(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $selectedPosition = $request->get('position');
        $search = $request->get('search');

        $query = User::with('homeroomClasses')
            ->where('tenant_id', $tenantId)
            ->whereIn('role', User::getTeacherRoles());

        if (!empty($selectedPosition)) {
            $query->where('position', $selectedPosition);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', '%'.$search.'%')
                  ->orWhere('email', 'ilike', '%'.$search.'%')
                  ->orWhere('nisn', 'ilike', '%'.$search.'%');
            });
        }

        $teachers = $query->orderBy('name')->get();

        $positionLabels = [
            'guru_kelas' => 'Guru Kelas',
            'guru_mapel' => 'Guru Mapel',
            'guru_bk' => 'Guru BK',
            'guru_inklusi' => 'Guru Inklusi',
            'guru_kejuruan' => 'Guru Kejuruan',
            'wali_kelas' => 'Wali Kelas',
            'kepala_sekolah' => 'Kepala Sekolah',
            'guru_penggerak' => 'Guru Penggerak / Koordinator',
            'staff' => 'Tata Usaha / Staf Admin',
            'pustakawan' => 'Pustakawan',
            'laboran' => 'Laboran',
            'it_support' => 'IT Support / Tim Teknis',
            'satpam' => 'Petugas Keamanan',
            'caraka' => 'Petugas Kebersihan',
        ];

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=data-guru-" . now()->format('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Lengkap', 'Surel/Email', 'Peran/Jabatan', 'Kelas yang Diampu', 'Status', 'Status Akun'];

        $callback = function() use($teachers, $columns, $positionLabels) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 encoding to support special characters in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);
            
            foreach ($teachers as $teacher) {
                $position = $teacher->position ?? $teacher->role;
                $positionLabel = $positionLabels[$position] ?? ucfirst(str_replace('_', ' ', $position));
                
                $classes = $teacher->homeroomClasses->pluck('nama_kelas')->implode(', ');
                if (empty($classes)) {
                    $classes = 'Belum ada';
                }
                
                $status = $teacher->is_active ? 'Aktif' : 'Nonaktif';
                
                $accountStatus = $teacher->must_change_password ? 'Kredensial Default' : 'Aktif';
                
                $row = [
                    $teacher->name,
                    $teacher->email,
                    $positionLabel,
                    $classes,
                    $status,
                    $accountStatus,
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Check if a CSV line is a header row by examining its content.
     * Headers typically contain non-numeric, descriptive text.
     */
    private function isHeaderRow(string $line): bool
    {
        $delimiter = strpos($line, ';') !== false ? ';' : ',';
        $items = str_getcsv($line, $delimiter);

        // If we have at least 4 columns, check if they look like headers
        if (count($items) >= 4) {
            $firstItem = strtolower(trim($items[0]));
            $secondItem = strtolower(trim($items[1]));
            $thirdItem = strtolower(trim($items[2]));
            $fourthItem = strtolower(trim($items[3]));

            // Common header keywords
            $headerKeywords = [
                'nama', 'name', 'namalengkap', 'fullname',
                'nip', 'nuptk', 'nipnuptk', 'employee',
                'email', 'e-mail', 'mail',
                'peran', 'role', 'jabatan', 'position', 'jabatan',
                'kelas', 'class', 'tingkat', 'grade'
            ];

            // Check if any column contains header-like keywords
            foreach ($items as $item) {
                $item = strtolower(trim($item));
                foreach ($headerKeywords as $keyword) {
                    if (str_contains($item, $keyword)) {
                        return true;
                    }
                }
            }

            // Additional check: if first column is "nama" or "name" and second is "nip" or "nuptk"
            if (in_array($firstItem, ['nama', 'name', 'namalengkap', 'fullname']) &&
                in_array($secondItem, ['nip', 'nuptk', 'nipnuptk', 'employeeid', 'employee_id'])) {
                return true;
            }
        }

        return false;
    }
}
