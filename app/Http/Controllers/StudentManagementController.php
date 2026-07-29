<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\SchoolClass;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StudentManagementController extends Controller
{
    /**
     * Tampilkan daftar siswa di institusi ini.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        
        $myClassIds = SchoolClass::where('wali_kelas_id', $user->id)->pluck('id');
        $isHomeroomTeacher = $myClassIds->isNotEmpty();
        $isTeacherRole = in_array($user->role, ['guru', 'wali_kelas', 'guru_mapel', 'teacher']) || $isHomeroomTeacher;

        // Jika user adalah wali kelas dan tidak memasok class_id di URL, default ke kelas binaan pertamanya
        if ($isHomeroomTeacher && !$request->has('class_id')) {
            $request->merge(['class_id' => $myClassIds->first()]);
        }

        $query = Student::where('tenant_id', $tenantId)
            ->with(['parent', 'schoolClass']);

        // Jika user adalah guru/wali kelas, batasi hanya melihat siswa di kelas asuhannya.
        if ($isTeacherRole && $isHomeroomTeacher) {
            $query->whereIn('class_id', $myClassIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $students = $query->paginate(15)->appends($request->query());
            
        $parents = User::where('tenant_id', $tenantId)
            ->where('role', 'parent')
            ->get();
            
        $classesQuery = SchoolClass::where('tenant_id', $tenantId)
            ->ordered();
            
        // Jika user adalah guru/wali kelas, batasi pilihan kelas hanya ke kelas asuhannya.
        if ($isTeacherRole && $isHomeroomTeacher) {
            $classesQuery->where('wali_kelas_id', $user->id);
        }
        
        $classes = $classesQuery->get();
        
        return view('students.index', compact('students', 'parents', 'classes'));
    }

    /**
     * Simpan data siswa baru secara manual.
     */
    public function store(StoreStudentRequest $request)
    {
        $user = Auth::user();

        // Validasi Otorisasi (Wali Kelas HANYA bisa menyimpan ke kelasnya)
        if (in_array($user->role, ['guru', 'wali_kelas', 'guru_mapel'])) {
            $isHomeroom = SchoolClass::where('id', $request->class_id)
                                    ->where('wali_kelas_id', $user->id)
                                    ->exists();
            if (!$isHomeroom) {
                if ($user->homeroomClasses->isEmpty()) {
                    abort(403, 'Anda bukan wali kelas dan tidak berhak menambah siswa.');
                }
                abort(403, 'Anda tidak berhak menambahkan siswa ke kelas ini.');
            }
        }

        $tenant = Tenant::findOrFail($user->tenant_id);
        
        $parentId = null;

        if ($request->parent_option === 'new' && $request->filled('parent_name')) {
            $parentEmail = $request->parent_email ?: 'ortu.' . $request->nisn . '@hadirsekolah.id';
            
            $parentUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->parent_name,
                'email' => $parentEmail,
                'password' => Hash::make('password'),
                'role' => 'parent',
                'is_active' => true,
                'onboarding_completed' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            $parentId = $parentUser->id;
        } elseif ($request->parent_option === 'existing') {
            $parentId = $request->parent_id;
        }

        // Auto-generate default password format: {NISN}{TANGGAL_LAHIR(DDMMYYYY)}
        $birthDateFormatted = $request->birth_date ? Carbon::parse($request->birth_date)->format('dmY') : '';
        $rawPassword = $request->nisn . $birthDateFormatted;
        if (empty($rawPassword)) {
            $rawPassword = 'password';
        }
        $password = Hash::make($rawPassword);
        
        $masterPhotoPath = null;
        if ($request->hasFile('master_photo')) {
            $masterPhotoPath = $request->file('master_photo')->store('master_photos/' . $tenant->id, 'public');
        }

        $email = $request->email ?: (strtolower(str_replace([' ', ',', '.'], '', $request->name)) . rand(100, 999) . '@' . ($tenant->subdomain ?? strtolower($tenant->code)) . '.hadiryuk.id');

        Student::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $email,
            'nisn' => $request->nisn,
            'nis' => $request->nis,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'religion' => $request->religion,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'parent_phone' => $request->parent_phone,
            'address' => $request->address,
            'blood_type' => $request->blood_type,
            'medical_notes' => $request->medical_notes,
            'password' => $password,
            'is_password_changed' => false,
            'parent_id' => $parentId,
            'class_id' => $request->class_id,
            'master_photo' => $masterPhotoPath,
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        $hasParentData = !empty($parentId) || $request->filled('father_name') || $request->filled('mother_name') || $request->filled('parent_phone');

        $message = $hasParentData 
            ? 'Siswa dan data Orang Tua berhasil ditambahkan.' 
            : 'Data siswa berhasil ditambahkan.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Memperbarui data siswa.
     */
    public function update(UpdateStudentRequest $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $student = Student::where('tenant_id', $tenantId)->findOrFail($id);

        $parentId = $student->parent_id;

        if ($request->parent_option === 'new' && $request->filled('parent_name')) {
            $tenant = Tenant::find($tenantId);
            $domain = $tenant ? ($tenant->subdomain ?? strtolower($tenant->code)) . '.hadiryuk.id' : 'hadirsekolah.id';
            $parentEmail = $request->parent_email ?: 'ortu.' . $request->nisn . '@' . $domain;
            
            $parentUser = User::create([
                'tenant_id' => $tenantId,
                'name' => $request->parent_name,
                'email' => $parentEmail,
                'password' => Hash::make($request->nisn),
                'role' => 'parent',
                'is_active' => true,
                'onboarding_completed' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            $parentId = $parentUser->id;
        } elseif ($request->parent_option === 'existing') {
            $parentId = $request->parent_id;
        } elseif ($request->parent_option === 'none') {
            $parentId = null;
        }

        $email = $request->email ?? $student->email;
        
        $updateData = [
            'name' => $request->name,
            'email' => $email,
            'nisn' => $request->nisn,
            'nis' => $request->nis,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'religion' => $request->religion,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'parent_phone' => $request->parent_phone,
            'address' => $request->address,
            'blood_type' => $request->blood_type,
            'medical_notes' => $request->medical_notes,
            'parent_id' => $parentId,
            'class_id' => $request->class_id,
            'is_active' => (bool)$request->is_active,
        ];

        if ($request->hasFile('master_photo')) {
            if ($student->master_photo) {
                Storage::disk('public')->delete($student->master_photo);
            }
            $updateData['master_photo'] = $request->file('master_photo')->store('master_photos/' . $tenantId, 'public');
        }

        $student->update($updateData);

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Menghapus data siswa.
     */
    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;
        $student = Student::where('tenant_id', $tenantId)->findOrFail($id);

        $student->delete();

        return redirect()->back()->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Impor data siswa dari berkas CSV/TXT.
     */
    public function import(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $user = Auth::user();

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'students_file' => 'required|file|mimes:csv,txt|max:2048'
        ], [
            'class_id.required' => 'Kelas / Rombel tujuan wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'students_file.required' => 'Berkas CSV/TXT siswa wajib diunggah.',
            'students_file.mimes' => 'Format file harus berupa CSV atau TXT.',
            'students_file.max' => 'Ukuran berkas maksimal 2MB.'
        ]);

        // Validasi Otorisasi jika role adalah guru / wali kelas
        if (in_array($user->role, ['guru', 'wali_kelas', 'guru_mapel'])) {
            $isHomeroom = SchoolClass::where('id', $request->class_id)
                                    ->where('wali_kelas_id', $user->id)
                                    ->exists();
            if (!$isHomeroom) {
                return redirect()->back()->with('error', 'Anda hanya dapat mengimpor siswa ke kelas binaan Anda.');
            }
        }

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
                        $student = Student::where('tenant_id', $tenantId)
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
                            $tenant = Tenant::find($tenantId);
                            $domain = $tenant ? ($tenant->subdomain ?? strtolower($tenant->code)) . '.hadiryuk.id' : 'hadirsekolah.id';
                            $emailFinal = $email ?? (strtolower(str_replace([' ', ',', '.'], '', $name)) . rand(100,999) . '@' . $domain);
                            
                            $birthFormatted = $birthDate ? Carbon::parse($birthDate)->format('dmY') : '';
                            $defaultPass = ($nisn ?? '') . $birthFormatted;
                            if (empty($defaultPass)) {
                                $defaultPass = 'password123';
                            }
                            $fieldsToSave['tenant_id'] = $tenantId;
                            $fieldsToSave['name'] = $name;
                            $fieldsToSave['email'] = $emailFinal;
                            $fieldsToSave['nisn'] = $nisn;
                            $fieldsToSave['password'] = Hash::make($defaultPass);
                            $fieldsToSave['is_password_changed'] = false;
                            $fieldsToSave['is_active'] = true;
                            $fieldsToSave['onboarding_completed'] = true;
                            $fieldsToSave['email_verified_at'] = Carbon::now();

                            Student::create($fieldsToSave);
                            $count++;
                        }
                    }
                }
            }
            fclose($handle);
        }

        return redirect()->back()->with('success', "Berhasil mengimpor {$count} siswa ke kelas {$class->nama_kelas}.");
    }

    /**
     * Unduh berkas contoh/template CSV untuk impor siswa.
     */
    public function downloadTemplate()
    {
        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_import_siswa.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'nama_lengkap', 'nisn', 'nis', 'nik', 'gender', 'email', 
            'birth_place', 'birth_date', 'religion', 'father_name', 
            'mother_name', 'parent_phone', 'address', 'blood_type', 'medical_notes'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'Ahmad Rizky Pratama', '1029384756', '2024001', '3201012345670001', 'L', 
                'ahmad.rizky@student.com', 'Jakarta', '2012-05-14', 'Islam', 
                'Budi Pratama', 'Siti Rahma', '081234567890', 'Jl. Merdeka No. 10 Jakarta', 'A', 'Tidak ada'
            ]);
            fputcsv($file, [
                'Bunga Lestari', '1029384757', '2024002', '3201012345670002', 'P', 
                'bunga.lestari@student.com', 'Bandung', '2012-08-20', 'Islam', 
                'Hendra Lestari', 'Dewi Lestari', '081987654321', 'Jl. Mawar No. 5 Bandung', 'O', 'Alergi debu'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Reset password data siswa.
     */
    public function resetPassword($id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $student = Student::where('tenant_id', $tenantId)->findOrFail($id);

        // Validasi Otorisasi jika role adalah guru / wali kelas
        if (in_array($user->role, ['guru', 'wali_kelas', 'teacher', 'guru_mapel'])) {
            $isHomeroom = SchoolClass::where('id', $student->class_id)
                                    ->where('wali_kelas_id', $user->id)
                                    ->exists();
            if (!$isHomeroom && $user->homeroomClasses->isEmpty()) {
                abort(403, 'Anda hanya berhak mereset password siswa di kelas binaan Anda.');
            }
        }

        $newPassword = $student->getDefaultPassword();

        $student->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true,
            'is_password_changed' => false,
        ]);

        return redirect()->back()->with('success', "Password siswa {$student->name} berhasil di-reset ke: {$newPassword}");
    }
}
