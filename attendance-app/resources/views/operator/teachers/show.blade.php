<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('operator.teachers.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors" title="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profil Pendidik & Staf') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 md:p-8">
                
                <!-- Profil Header -->
                <div class="flex flex-col md:flex-row gap-6 items-start md:items-center border-b border-gray-100 pb-8 mb-8">
                    <!-- Avatar -->
                    <div class="h-24 w-24 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-3xl shadow-inner border border-indigo-200">
                        {{ substr(trim($teacher->name, '"'), 0, 1) }}
                    </div>
                    
                    <div class="flex-grow">
                        <h1 class="text-2xl font-bold text-gray-900">{{ trim($teacher->name, '"') }}</h1>
                        <p class="text-gray-500 font-mono mt-1"><i class="fa-regular fa-id-card w-5"></i> {{ $teacher->nisn ?? 'Belum ada NUPTK' }}</p>
                        <p class="text-gray-500 mt-1"><i class="fa-regular fa-envelope w-5"></i> {{ $teacher->email }}</p>
                    </div>
                    
                    <div class="flex flex-col gap-2 min-w-[120px]">
                        @php
                            $roleLabels = [
                                'guru_kelas' => 'Guru Kelas',
                                'guru' => 'Guru Mapel',
                                'guru_bk' => 'Guru BK',
                                'guru_inklusi' => 'Guru Inklusi',
                                'guru_kejuruan' => 'Guru Kejuruan',
                                'wali_kelas' => 'Wali Kelas',
                                'headmaster' => 'Kepala Sekolah',
                                'manager_teacher' => 'Wakasek / Manajemen',
                                'staff' => 'Staf TU / Ops',
                                'pustakawan' => 'Pustakawan',
                                'laboran' => 'Laboran',
                                'it_support' => 'IT Support',
                                'satpam' => 'Satpam',
                                'caraka' => 'Caraka',
                            ];
                            $labelText = $roleLabels[$teacher->role] ?? ucfirst(str_replace('_', ' ', $teacher->role));
                        @endphp
                        <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 shadow-sm">
                            {{ $labelText }}
                        </span>
                        
                        @if($teacher->is_active)
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">Aktif</span>
                        @else
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200 shadow-sm">Nonaktif</span>
                        @endif
                    </div>
                </div>

                <!-- Status Keamanan Akun -->
                <div class="mb-8">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-l-4 border-brand-primary pl-3">Status Keamanan Akun</h3>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            @if($teacher->must_change_password)
                                <div class="flex items-center text-yellow-600 font-semibold mb-1">
                                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Kredensial Default
                                </div>
                                <p class="text-sm text-gray-500">Pengguna belum pernah login atau belum mengganti kata sandi bawaannya.</p>
                            @else
                                <div class="flex items-center text-emerald-600 font-semibold mb-1">
                                    <i class="fa-solid fa-shield-check mr-2"></i> Kata Sandi Diperbarui
                                </div>
                                <p class="text-sm text-gray-500">Pengguna telah menggunakan kata sandi pribadi yang aman.</p>
                            @endif
                        </div>
                        <form action="{{ route('operator.teachers.reset-password', $teacher->id) }}" method="POST" onsubmit="return confirm('Reset kata sandi ke bawaan sistem?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-amber-600 shadow-sm transition-colors">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Reset Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Kelas yang Diampu -->
                <div>
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-l-4 border-brand-primary pl-3">Tugas Wali Kelas</h3>
                    
                    @if($teacher->homeroomClasses && $teacher->homeroomClasses->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($teacher->homeroomClasses as $class)
                                <a href="{{ route('operator.classes.show', $class->id) }}" class="block p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all group">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $class->nama_kelas }}</h4>
                                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $class->jenjang }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Tingkat {{ $class->tingkat }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500">
                            <i class="fa-solid fa-chalkboard-user text-3xl mb-2 text-gray-400"></i>
                            <p class="text-sm">Tidak ada kelas yang diampu sebagai Wali Kelas saat ini.</p>
                        </div>
                    @endif
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>
