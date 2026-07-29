<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentCardController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan dan pembuatan Kartu Pelajar.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $tenant = $user->tenant;

        // Ambil daftar kelas untuk filter
        $classes = SchoolClass::where('tenant_id', $tenantId)
            ->ordered()
            ->get();

        // Query data siswa
        $query = Student::where('tenant_id', $tenantId)
            ->with(['schoolClass']);

        // Filter berdasarkan peran pengguna (jika guru/wali kelas)
        if (in_array($user->role, ['guru', 'wali_kelas', 'guru_mapel'])) {
            $myClassIds = SchoolClass::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereIn('class_id', $myClassIds);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name', 'asc')->get();

        // Pengaturan default layout & wording kartu
        $defaultSettings = [
            'school_name' => $tenant->name ?? 'SEKOLAH HADIRYUK',
            'academic_year' => date('Y') . '/' . (date('Y') + 1),
            'card_title' => 'KARTU PELAJAR',
            'footer_text' => 'Kartu identitas resmi ini wajib dibawa & digunakan untuk presensi harian.',
            'template' => $request->get('template', 'modern'),
            'accent_color' => $request->get('accent_color', 'red'),
        ];

        return view('student-cards.index', compact('classes', 'students', 'tenant', 'defaultSettings'));
    }

    /**
     * Tampilkan halaman pratinjau & cetak kartu pelajar (CR80 Standard / Grid).
     */
    public function printCards(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $tenant = $user->tenant;

        $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
            'class_id' => 'nullable',
            'template' => 'required|string|in:modern,classic,minimalist',
            'school_name' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:50',
            'card_title' => 'nullable|string|max:100',
            'footer_text' => 'nullable|string|max:255',
            'accent_color' => 'nullable|string|in:red,indigo,emerald,amber,slate',
        ]);

        $query = Student::where('tenant_id', $tenantId)->with(['schoolClass']);

        if ($request->filled('student_ids') && is_array($request->student_ids) && count($request->student_ids) > 0) {
            $query->whereIn('id', $request->student_ids);
        } elseif ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $students = $query->orderBy('name', 'asc')->get();

        $cardConfig = [
            'school_name' => $request->school_name,
            'academic_year' => $request->academic_year ?? (date('Y') . '/' . (date('Y') + 1)),
            'card_title' => $request->card_title ?? 'KARTU PELAJAR',
            'footer_text' => $request->footer_text ?? 'Kartu identitas resmi ini wajib dibawa & digunakan untuk presensi harian.',
            'template' => $request->template,
            'accent_color' => $request->accent_color ?? 'red',
        ];

        return view('student-cards.print', compact('students', 'cardConfig', 'tenant'));
    }
}
