<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Pengumuman Wali Kelas') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6 bg-white shadow rounded-lg mb-6">
                <div
                    class="mb-4 p-3 bg-red-50 border border-red-100 rounded-xl flex items-center gap-2 text-sm text-red-800">
                    <i class="fa-solid fa-school-flag text-red-600"></i>
                    <span>Pengumuman ini akan dikirim otomatis ke kelas binaan Anda: <strong
                            class="font-semibold text-red-600">{{ $homeroomClass?->nama_kelas ?? $homeroomClass?->name ?? '-' }}</strong></span>
                </div>
                <form action="{{ route('teacher.announcements.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="block font-medium text-sm text-gray-700">Judul</label>
                        <input type="text" name="title"
                            class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium text-sm text-gray-700">Target Audience</label>
                        <select name="target_audience"
                            class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            required>
                            <option value="students">Siswa Saja</option>
                            <option value="parents">Orang Tua Saja</option>
                            <option value="both">Siswa & Orang Tua</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium text-sm text-gray-700">Deskripsi</label>
                        <textarea name="description"
                            class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Lampiran</label>
                        <input type="file" name="attachment"
                            class="form-control mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    </div>
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg transition-all">Kirim
                        Pengumuman</button>
                </form>
            </div>

            <h3 class="text-lg font-bold mb-4">Riwayat Pengumuman</h3>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 font-bold text-gray-700">Judul</th>
                            <th class="p-4 font-bold text-gray-700">Target</th>
                            <th class="p-4 font-bold text-gray-700">Tanggal</th>
                            <th class="p-4 font-bold text-gray-700">Lampiran</th>
                            <th class="p-4 font-bold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr class="border-b hover:bg-gray-50/50">
                                <td class="p-4 font-medium text-gray-900">{{ $announcement->title }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $announcement->target_audience === 'students' ? 'bg-blue-100 text-blue-800' : ($announcement->target_audience === 'parents' ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800') }}">
                                        {{ $announcement->target_audience === 'students' ? 'Siswa' : ($announcement->target_audience === 'parents' ? 'Orang Tua' : 'Semua (Siswa & Ortu)') }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-gray-500">
                                    {{ $announcement->created_at?->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="p-4 text-sm">
                                    @if($announcement->attachment_path)
                                        <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold underline">
                                            <i class="fa-solid fa-paperclip"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <form action="{{ route('teacher.announcements.destroy', $announcement->id) }}"
                                        method="POST" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800 font-bold text-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-sm text-gray-500">
                                    Belum ada pengumuman yang dibuat untuk kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>