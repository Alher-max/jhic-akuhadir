<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Orang Tua / Wali Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-brand-surface overflow-hidden shadow-sm sm:rounded-xl border border-brand-border p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-users-line text-brand-primary"></i>
                            Daftar Orang Tua / Wali Siswa
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Kelola data akun orang tua dan keterhubungannya dengan siswa terdaftar.</p>
                    </div>

                    <form method="GET" action="{{ route('operator.parents.index') }}" class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / HP..." class="bg-gray-50 border border-gray-200 text-xs rounded-xl px-3 py-2 focus:ring-brand-primary focus:border-brand-primary">
                        <button type="submit" class="bg-brand-primary text-white text-xs px-4 py-2 rounded-xl font-medium hover:bg-brand-primary/90 transition shadow-xs">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-4 py-3 font-medium border-b border-brand-border">Nama Orang Tua</th>
                                <th class="px-4 py-3 font-medium border-b border-brand-border">Email / Kontak</th>
                                <th class="px-4 py-3 font-medium border-b border-brand-border">Anak Terhubung</th>
                                <th class="px-4 py-3 font-medium border-b border-brand-border text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($parents as $parent)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3.5 font-bold text-gray-900">
                                        {{ $parent->name }}
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-600">
                                        <div>{{ $parent->email }}</div>
                                        <div class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $parent->parent_phone ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($parent->students->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($parent->students as $student)
                                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-900 border border-amber-200 px-2 py-0.5 rounded-md font-medium text-[11px]">
                                                        <i class="fa-solid fa-child text-amber-600"></i>
                                                        {{ $student->name }} ({{ $student->schoolClass->nama_kelas ?? 'Tanpa Kelas' }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Belum terhubung dengan anak</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        @if($parent->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">
                                                Non-aktif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fa-solid fa-users-slash text-3xl mb-2 text-gray-300"></i>
                                        <p class="font-medium">Belum ada akun orang tua yang terdaftar.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $parents->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
