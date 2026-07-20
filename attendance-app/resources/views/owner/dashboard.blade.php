<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Pimpinan (Owner)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Global Monitoring -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Anggota Institusi</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalMembers }}</p>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-lg">
                        <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Super Admin</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $superAdmins->count() }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    </div>
                </div>
            </div>

            @if(!$hasActiveSuperAdmin)
            <!-- Mode Mandiri (Bypass Operasional) -->
            <div class="mb-8 p-6 bg-indigo-50 border border-indigo-100 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-indigo-900">Operasional Mandiri</h3>
                        <p class="text-indigo-700 text-sm mt-1">Institusi Anda belum memiliki Super Admin yang aktif. Anda dapat mengelola operasional harian (jadwal & absensi) secara mandiri.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded shadow hover:bg-indigo-700 transition">
                            Masuk Dasbor Operasional
                        </a>
                        <a href="{{ route('schedules.index') }}" class="px-4 py-2 bg-white text-indigo-600 border border-indigo-200 text-sm font-semibold rounded shadow-sm hover:bg-indigo-50 transition">
                            Kelola Jadwal
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Manage Super Admins -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Undang Super Admin Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg lg:col-span-1">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Undang Super Admin</h3>
                        <form method="POST" action="{{ route('owner.super-admin.store') }}">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="email" value="Alamat Email" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" required placeholder="email@contoh.com" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <x-primary-button class="w-full justify-center">
                                Kirim Undangan
                            </x-primary-button>
                        </form>

                        @if($pendingInvitations->isNotEmpty())
                        <div class="mt-8 border-t border-gray-100 pt-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-4">Undangan Terkirim (Pending)</h4>
                            <div class="space-y-3">
                                @foreach($pendingInvitations as $invitation)
                                <div class="bg-gray-50 rounded-lg p-3 text-sm flex justify-between items-center border border-gray-100">
                                    <span class="text-gray-600 truncate mr-2">{{ $invitation->email }}</span>
                                    <span class="text-xs font-medium text-amber-600 bg-amber-100 px-2 py-1 rounded-full whitespace-nowrap">Pending</span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1 mb-2">
                                    Tautan: <a href="{{ route('register.super-admin', $invitation->token) }}" class="text-indigo-500 hover:underline" target="_blank">Salin/Buka Tautan</a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Daftar Super Admin -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Daftar Super Admin</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 font-medium border-b border-gray-100">Nama</th>
                                    <th class="px-6 py-4 font-medium border-b border-gray-100">Email</th>
                                    <th class="px-6 py-4 font-medium border-b border-gray-100">Status</th>
                                    <th class="px-6 py-4 font-medium border-b border-gray-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($superAdmins as $admin)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $admin->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $admin->email }}</td>
                                        <td class="px-6 py-4">
                                            @if($admin->is_active)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form method="POST" action="{{ route('owner.super-admin.toggle', $admin->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm font-semibold {{ $admin->is_active ? 'text-rose-600 hover:text-rose-900' : 'text-emerald-600 hover:text-emerald-900' }}">
                                                    {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            Belum ada Super Admin yang didaftarkan.
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
