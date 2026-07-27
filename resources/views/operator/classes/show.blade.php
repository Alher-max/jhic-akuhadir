<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('operator.classes.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors" title="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Siswa - ') . $class->full_name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Informasi Kelas</h3>
                            <p class="text-sm text-gray-600 mt-1">Jenjang: <span class="font-semibold">{{ $class->jenjang }}</span> | Tingkat: <span class="font-semibold">{{ $class->tingkat }}</span></p>
                            <p class="text-sm text-gray-600 mt-1">Wali Kelas: <span class="font-semibold">{{ $class->waliKelas ? $class->waliKelas->name : 'Belum ditentukan' }}</span></p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-brand-primary/10 text-brand-primary">
                                Total: {{ $students->count() }} Siswa
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap Siswa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NISN / Email</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($students as $index => $student)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $student->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 font-mono">{{ $student->nisn ?? $student->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('operator.classes.remove-student', ['class' => $class->id, 'student' => $student->id]) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin mengeluarkan {{ addslashes($student->name) }} dari kelas? Siswa tidak akan terhapus dari sistem, hanya dikeluarkan dari rombel ini.');">
                                                @csrf
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 border border-rose-200 bg-rose-50 hover:bg-rose-100 px-3 py-1 rounded transition-colors text-xs font-semibold">
                                                    <i class="fa-solid fa-user-minus mr-1"></i> Keluarkan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                            <i class="fa-solid fa-users-slash text-3xl mb-3 text-gray-300"></i>
                                            <p class="text-sm font-medium">Belum ada siswa yang terdaftar di kelas ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
