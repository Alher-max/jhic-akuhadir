<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Peserta Didik / Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ 
        showModal: false,
        showEditModal: false,
        showImportModal: false,
        activeTab: 'utama',
        activeEditTab: 'utama',
        selectedStudent: null,
        previewUrl: null,
        createPhotoPreview: null,
        parentOption: 'none',
        editForm: { id: null, name: '', nisn: '', nis: '', nik: '', gender: '', email: '', class_id: '', master_photo: null, parent_option: 'unchanged', parent_id: '', parent_name: '', father_name: '', mother_name: '', parent_phone: '', parent_email: '', current_parent_name: null, birth_place: '', birth_date: '', religion: '', address: '', blood_type: '', medical_notes: '' },
        openEditModal(student) {
            this.selectedStudent = student;
            this.editForm.id = student.id;
            this.editForm.name = student.name;
            this.editForm.nisn = student.nisn;
            this.editForm.nis = student.nis || '';
            this.editForm.nik = student.nik || '';
            this.editForm.gender = student.gender || '';
            this.editForm.email = student.email;
            this.editForm.class_id = student.class_id;
            this.editForm.master_photo = student.master_photo || null;
            this.editForm.parent_option = 'unchanged';
            this.editForm.parent_id = student.parent_id || '';
            this.editForm.parent_name = '';
            this.editForm.father_name = student.father_name || '';
            this.editForm.mother_name = student.mother_name || '';
            this.editForm.parent_phone = student.parent_phone || '';
            this.editForm.parent_email = '';
            this.editForm.current_parent_name = student.parent ? student.parent.name : null;
            this.editForm.birth_place = student.birth_place || '';
            this.editForm.birth_date = student.birth_date ? student.birth_date.substring(0, 10) : '';
            this.editForm.religion = student.religion || '';
            this.editForm.address = student.address || '';
            this.editForm.blood_type = student.blood_type || '';
            this.editForm.medical_notes = student.medical_notes || '';
            this.previewUrl = null;
            this.activeEditTab = 'utama';
            this.showEditModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
                <div class="mb-4 sm:mb-0">
                    <p class="text-sm text-gray-600">Tambah, edit, dan atur data siswa serta relasi akun orang tua.</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button @click="showImportModal = true"
                        class="px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm border border-brand-border inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-file-import text-indigo-600"></i> Import Siswa (CSV/Excel)
                    </button>
                    <button @click="showModal = true"
                        class="px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        + Tambah Siswa Baru
                    </button>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div
                class="bg-brand-surface p-4 rounded-xl shadow-sm border border-brand-border mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <form method="GET" action="{{ route('students.index') }}"
                    class="flex flex-col sm:flex-row flex-wrap items-center gap-3 w-full" id="filterForm">

                    <!-- Search Input -->
                    <div class="w-full lg:w-64 relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full pl-9 pr-3 py-2 border border-brand-border rounded-lg text-sm focus:ring-brand-primary focus:border-brand-primary shadow-sm"
                            placeholder="Cari Nama, NISN, atau Email..."
                            onchange="document.getElementById('filterForm').submit()">
                    </div>

                    <!-- Filter Class -->
                    <div class="w-full sm:w-48">
                        <select name="class_id"
                            class="w-full border-brand-border rounded-lg text-sm focus:ring-brand-primary focus:border-brand-primary shadow-sm"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="">Pilih Kelas / Rombel</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="w-full sm:w-40">
                        <select name="status"
                            class="w-full border-brand-border rounded-lg text-sm focus:ring-brand-primary focus:border-brand-primary shadow-sm"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif
                            </option>
                        </select>
                    </div>

                    @if(request('search') || request('class_id') || request('status'))
                        <a href="{{ route('students.index') }}"
                            class="text-sm text-gray-500 hover:text-red-600 font-medium px-2 flex-shrink-0 transition-colors w-full sm:w-auto text-center sm:text-left mt-2 sm:mt-0">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>
            <!-- Tabel Data Siswa -->
            <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border">NISN</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border w-1/3">Nama Lengkap</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border">Kelas/Rombel</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border">Status</th>
                                <th class="px-4 py-3.5 font-medium border-b border-brand-border text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3.5 align-middle">
                                        <span
                                            class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded-md inline-block">{{ $student->nisn ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 align-middle text-left">
                                        <div class="flex flex-col items-start text-left gap-1">
                                            <button type="button" @click="openEditModal(@js($student))"
                                                class="font-semibold text-gray-900 hover:text-indigo-600 text-left transition cursor-pointer block">
                                                {{ $student->name }}
                                            </button>
                                            <div
                                                class="text-xs text-gray-500 flex items-center justify-start text-left gap-1 flex-wrap">
                                                <i class="fa-solid fa-envelope w-3"></i>
                                                {{ $student->email ?? 'Tidak ada surel' }}
                                                @if($student->master_photo)
                                                    <span class="inline-flex items-center text-emerald-600 ml-1"
                                                        title="Foto Master Terdaftar"><i
                                                            class="fa-solid fa-camera-retro"></i></span>
                                                @endif
                                            </div>
                                            <div class="mt-0.5 text-left">
                                                @if($student->parent)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200/60">
                                                        👨‍👩‍👦 Ortu: {{ $student->parent->name }}
                                                    </span>
                                                @else
                                                    <button type="button" @click="openEditModal(@js($student))"
                                                        title="Klik untuk hubungkan data orang tua"
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 transition cursor-pointer">
                                                        🔗 Ortu: Belum terhubung
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 align-middle">
                                        <span
                                            class="bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-medium">{{ $student->schoolClass->full_name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 align-middle">
                                        @if($student->is_active)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 align-middle text-right text-sm">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" @click="openEditModal(@js($student))"
                                                class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition inline-flex items-center gap-1">
                                                <i class="fa-solid fa-eye text-xs"></i><i
                                                    class="fa-solid fa-pen-to-square"></i> Lihat / Edit
                                            </button>
                                            <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                                class="inline m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa {{ addslashes($student->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-users text-4xl mb-3 text-gray-300"></i>
                                            <p class="font-medium">Belum ada data siswa.</p>
                                            <p class="text-sm mt-1">Silakan tambah siswa baru melalui tombol di atas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-brand-border bg-gray-50">
                    {{ $students->links() }}
                </div>
            </div>
        </div>

        <!-- Student Modal Partial (Tambah & Edit) -->
        @include('students.partials.modal')
    </div><!-- /end root x-data -->
</x-app-layout>