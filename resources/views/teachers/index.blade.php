<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pendidik & Tenaga Kependidikan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-brand-bg min-h-screen" x-data="{ showModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
                <div class="mb-4 sm:mb-0">
                    <p class="text-sm text-gray-600">Tambah, edit, dan atur data guru (wali kelas) di institusi Anda.</p>
                </div>
                <div>
                    <button @click="showModal = true" class="px-4 py-2 bg-brand-primary text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        + Tambah Guru / Staf Baru
                    </button>
                </div>
            </div>

            <!-- Pesan Sukses / Error -->
            @if(session('success'))
                <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tabel Data Guru -->
            <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Nama Lengkap</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Surel</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Kelas yang Diampu (Wali)</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border">Status</th>
                                <th class="px-6 py-4 font-medium border-b border-brand-border text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            @forelse($teachers as $teacher)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm mr-3">
                                                {{ substr($teacher->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div>{{ $teacher->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $teacher->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($teacher->homeroomClasses->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($teacher->homeroomClasses as $class)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $class->nama_kelas }} ({{ $class->jenjang }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($teacher->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-chalkboard-user text-4xl mb-3 text-gray-300"></i>
                                            <p class="font-medium">Belum ada data guru / tenaga pendidik.</p>
                                            <p class="text-sm mt-1">Silakan tambah guru baru melalui tombol di atas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Guru -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-brand-surface rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-brand-border">
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-brand-text-main" id="modal-title">Tambah Guru / Pendidik Baru</h3>
                        <p class="mt-2 text-sm text-brand-text-muted">Masukkan informasi guru di bawah ini.</p>
                    </div>
                    
                    <form action="{{ route('teachers.store') }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" required placeholder="Cth: Budi Santoso, S.Pd." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="nip" class="block text-sm font-medium text-gray-700">NUPTK (Opsional)</label>
                                <input type="text" name="nip" id="nip" placeholder="Masukkan NUPTK" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Surel (Opsional)</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-primary focus:border-brand-primary sm:text-sm" placeholder="Otomatis jika kosong">
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 mt-6">
                            <button type="button" @click="showModal = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-lg shadow-sm bg-brand-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary sm:w-auto sm:text-sm">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
