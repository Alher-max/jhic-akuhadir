<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassManagementController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        
        $classes = SchoolClass::with(['waliKelas'])
            ->withCount('students')
            ->where('tenant_id', $tenantId)
            ->ordered()
            ->get();
            
        // Kelompokkan kelas berdasarkan jenjang lalu tingkat, dan urutkan tingkat (Ascending)
        $groupedClasses = $classes->groupBy('jenjang')->map(function ($jenjangClasses) {
            return $jenjangClasses->groupBy('tingkat')->sortKeys();
        });

        // Urutkan jenjang berdasarkan hierarki custom
        $jenjangOrder = ['TK' => 1, 'SD' => 2, 'SMP' => 3, 'SMA' => 4, 'SMK' => 5, 'LAINNYA' => 6];
        $groupedClasses = $groupedClasses->sortBy(function ($tingkatGroups, $jenjangName) use ($jenjangOrder) {
            return $jenjangOrder[$jenjangName] ?? 99;
        });

        $teacherRoles = ['teacher', 'guru', 'guru_kelas', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'];
        $teachers = User::where('tenant_id', $tenantId)
            ->whereIn('role', $teacherRoles)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('operator.classes.index', compact('groupedClasses', 'teachers'));
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'jenjang' => ['required', 'string', Rule::in(['SD', 'SMP', 'SMA', 'SMK', 'TK', 'LAINNYA'])],
            'tingkat' => ['required', 'integer', 'min:1', 'max:13'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'wali_kelas_id' => [
                'required', 
                'exists:users,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if ($value) {
                        $teacherRoles = ['teacher', 'guru', 'guru_kelas', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'];
                        $user = User::where('id', $value)->where('tenant_id', $tenantId)->whereIn('role', $teacherRoles)->first();
                        if (!$user) {
                            $fail('Wali kelas yang dipilih tidak valid atau tidak berada di sekolah Anda.');
                        }
                    }
                }
            ],
            'students_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048']
        ]);

        $class = SchoolClass::create([
            'tenant_id' => $tenantId,
            'jenjang' => $request->jenjang,
            'tingkat' => $request->tingkat,
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        if ($request->hasFile('students_file')) {
            $file = $request->file('students_file');
            if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
                fgetcsv($handle, 1000, ","); // Skip header
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if(count($data) >= 2) {
                        $name = trim($data[0]);
                        $emailOrNisn = trim($data[1]);
                        
                        if(!empty($name) && !empty($emailOrNisn)) {
                            $user = User::where('tenant_id', $tenantId)
                                        ->where(function($q) use ($emailOrNisn) {
                                            $q->where('nisn', $emailOrNisn)->orWhere('email', $emailOrNisn);
                                        })->first();
                            
                            if($user) {
                                $user->class_id = $class->id;
                                $user->save();
                            } else {
                                $isEmail = filter_var($emailOrNisn, FILTER_VALIDATE_EMAIL);
                                $nisnVal = $isEmail ? null : $emailOrNisn;
                                \App\Models\Student::create([
                                    'tenant_id' => $tenantId,
                                    'name' => $name,
                                    'email' => $isEmail ? $emailOrNisn : strtolower(str_replace(' ', '', $name)) . rand(100,999) . '@student.com',
                                    'nisn' => $nisnVal,
                                    'class_id' => $class->id,
                                    'password' => \Illuminate\Support\Facades\Hash::make($nisnVal ?: 'password123'),
                                    'is_password_changed' => false,
                                    'onboarding_completed' => true
                                ]);
                            }
                        }
                    }
                }
                fclose($handle);
            }
        }

        return redirect()->route('operator.classes.index')->with('success', 'Kelas berhasil dibuat' . ($request->hasFile('students_file') ? ' beserta impor data murid.' : '.'));
    }
    public function update(Request $request, SchoolClass $class)
    {
        $tenantId = Auth::user()->tenant_id;

        if ($class->tenant_id !== $tenantId) {
            abort(403);
        }

        $request->validate([
            'jenjang' => ['required', 'string', Rule::in(['SD', 'SMP', 'SMA', 'SMK', 'TK', 'LAINNYA'])],
            'tingkat' => ['required', 'integer', 'min:1', 'max:13'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'wali_kelas_id' => [
                'required', 
                'exists:users,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    if ($value) {
                        $teacherRoles = ['teacher', 'guru', 'guru_kelas', 'guru_bk', 'guru_inklusi', 'guru_kejuruan', 'wali_kelas', 'headmaster', 'manager_teacher', 'staff', 'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'];
                        $user = User::where('id', $value)->where('tenant_id', $tenantId)->whereIn('role', $teacherRoles)->first();
                        if (!$user) {
                            $fail('Wali kelas yang dipilih tidak valid atau tidak berada di sekolah Anda.');
                        }
                    }
                }
            ],
        ]);

        $class->update([
            'jenjang' => $request->jenjang,
            'tingkat' => $request->tingkat,
            'nama_kelas' => $request->nama_kelas,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->route('operator.classes.index')->with('success', 'Kelas / Rombel berhasil diperbarui.');
    }
    
    public function destroy(SchoolClass $class)
    {
        if ($class->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }
        
        if ($class->students()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kelas yang masih memiliki siswa terdaftar.');
        }

        $class->delete();

        return redirect()->route('operator.classes.index')->with('success', 'Kelas / Rombel berhasil dihapus.');
    }

    public function show(SchoolClass $class)
    {
        if ($class->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $class->load(['waliKelas']);
        $students = $class->students()->orderBy('name')->get();

        return view('operator.classes.show', compact('class', 'students'));
    }

    public function removeStudent(SchoolClass $class, User $student)
    {
        if ($class->tenant_id !== Auth::user()->tenant_id || $student->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($student->class_id === $class->id) {
            $student->class_id = null;
            $student->save();
            return redirect()->back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas.");
        }

        return redirect()->back()->with('error', 'Siswa tidak ditemukan di kelas ini.');
    }

    public function importStudents(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'students_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $class = SchoolClass::where('id', $request->class_id)->where('tenant_id', $tenantId)->firstOrFail();
        
        $file = $request->file('students_file');
        $count = 0;
        
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            if ($header && count($header) == 1 && strpos($header[0], ';') !== false) {
                rewind($handle);
                $header = fgetcsv($handle, 1000, ";");
                $delimiter = ";";
            } else {
                $delimiter = ",";
                if ($header && strtolower(trim($header[0])) !== 'nama' && strtolower(trim($header[0])) !== 'nama lengkap') {
                    rewind($handle);
                }
            }
            
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (count($data) >= 2) {
                    $name = trim($data[0] ?? '');
                    $col1 = trim($data[1] ?? ''); // NISN or Email
                    
                    if (empty($name) || in_array(strtolower($name), ['nama', 'nama lengkap', 'nama_lengkap'])) {
                        continue;
                    }

                    $nis = !empty($data[2]) ? trim($data[2]) : null;
                    $nik = !empty($data[3]) ? trim($data[3]) : null;
                    $gender = !empty($data[4]) && in_array(strtoupper(trim($data[4])), ['L', 'P']) ? strtoupper(trim($data[4])) : null;
                    $emailCol = !empty($data[5]) ? trim($data[5]) : null;
                    $birthPlace = !empty($data[6]) ? trim($data[6]) : null;
                    $rawDate = !empty($data[7]) ? trim($data[7]) : null;
                    $birthDate = $rawDate && strtotime($rawDate) ? date('Y-m-d', strtotime($rawDate)) : null;
                    $religion = !empty($data[8]) ? trim($data[8]) : null;
                    $fatherName = !empty($data[9]) ? trim($data[9]) : null;
                    $motherName = !empty($data[10]) ? trim($data[10]) : null;
                    $parentPhone = !empty($data[11]) ? trim($data[11]) : null;
                    $address = !empty($data[12]) ? trim($data[12]) : null;
                    $bloodType = !empty($data[13]) ? trim($data[13]) : null;
                    $medicalNotes = !empty($data[14]) ? trim($data[14]) : null;

                    $isEmail = filter_var($col1, FILTER_VALIDATE_EMAIL);
                    $nisn = $isEmail ? null : $col1;
                    $email = $emailCol ?? ($isEmail ? $col1 : null);

                    if (!empty($name) && (!empty($nisn) || !empty($email) || !empty($nis))) {
                        $student = \App\Models\Student::where('tenant_id', $tenantId)
                            ->where(function($q) use ($nisn, $email, $nis) {
                                if ($nisn) $q->orWhere('nisn', $nisn);
                                if ($email) $q->orWhere('email', $email);
                                if ($nis) $q->orWhere('nis', $nis);
                            })->first();

                        $fieldsToSave = [
                            'class_id' => $class->id,
                        ];

                        if ($nis) $fieldsToSave['nis'] = $nis;
                        if ($nik) $fieldsToSave['nik'] = $nik;
                        if ($gender) $fieldsToSave['gender'] = $gender;
                        if ($birthPlace) $fieldsToSave['birth_place'] = $birthPlace;
                        if ($birthDate) $fieldsToSave['birth_date'] = $birthDate;
                        if ($religion) $fieldsToSave['religion'] = $religion;
                        if ($fatherName) $fieldsToSave['father_name'] = $fatherName;
                        if ($motherName) $fieldsToSave['mother_name'] = $motherName;
                        if ($parentPhone) $fieldsToSave['parent_phone'] = $parentPhone;
                        if ($address) $fieldsToSave['address'] = $address;
                        if ($bloodType) $fieldsToSave['blood_type'] = $bloodType;
                        if ($medicalNotes) $fieldsToSave['medical_notes'] = $medicalNotes;
                        
                        if ($student) {
                            $student->update($fieldsToSave);
                            $count++;
                        } else {
                            $tenant = \App\Models\Tenant::find($tenantId);
                            $domain = $tenant ? ($tenant->subdomain ?? strtolower($tenant->code)) . '.hadiryuk.id' : 'hadirsekolah.id';
                            $emailFinal = $email ?? (strtolower(str_replace([' ', ',', '.'], '', $name)) . rand(100,999) . '@' . $domain);

                            $birthFormatted = isset($birthDate) && $birthDate ? \Illuminate\Support\Carbon::parse($birthDate)->format('dmY') : '';
                            $defaultPass = ($nisn ?? '') . $birthFormatted;
                            if (empty($defaultPass)) {
                                $defaultPass = 'password123';
                            }
                            $fieldsToSave['tenant_id'] = $tenantId;
                            $fieldsToSave['name'] = $name;
                            $fieldsToSave['email'] = $emailFinal;
                            $fieldsToSave['nisn'] = $nisn;
                            $fieldsToSave['password'] = \Illuminate\Support\Facades\Hash::make($defaultPass);
                            $fieldsToSave['is_password_changed'] = false;
                            $fieldsToSave['is_active'] = true;
                            $fieldsToSave['onboarding_completed'] = true;
                            $fieldsToSave['email_verified_at'] = \Illuminate\Support\Carbon::now();

                            \App\Models\Student::create($fieldsToSave);
                            $count++;
                        }
                    }
                }
            }
            fclose($handle);
        }

        return redirect()->route('operator.classes.index')->with('success', "Berhasil mengimpor {$count} siswa ke kelas {$class->nama_kelas}.");
    }
}
