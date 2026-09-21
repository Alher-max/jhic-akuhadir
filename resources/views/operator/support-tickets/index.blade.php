<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-headset text-brand-primary"></i>
            {{ __('Manajemen Tiket Bantuan Kendala Operator') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-brand-bg min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Top Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('operator.support-tickets.index', ['status' => 'pending']) }}" class="bg-white rounded-2xl p-5 border border-brand-border shadow-xs hover:border-amber-400 transition flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Antrean Baru (Pending)</p>
                        <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $pendingCount }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </a>

                <a href="{{ route('operator.support-tickets.index', ['status' => 'processing']) }}" class="bg-white rounded-2xl p-5 border border-brand-border shadow-xs hover:border-indigo-400 transition flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sedang Diproses</p>
                        <p class="text-3xl font-extrabold text-indigo-600 mt-1">{{ $processingCount }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                </a>

                <a href="{{ route('operator.support-tickets.index', ['status' => 'resolved']) }}" class="bg-white rounded-2xl p-5 border border-brand-border shadow-xs hover:border-emerald-400 transition flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tiket Selesai</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $resolvedCount }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </a>
            </div>

            <!-- Main Tickets Table -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden space-y-4 p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-inbox text-brand-primary"></i> Daftar Tiket Masuk
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Kelola, beri tanggapan, dan perbarui status kendala yang diajukan oleh pengguna institusi.
                        </p>
                    </div>

                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('operator.support-tickets.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <select name="status" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-xl font-semibold focus:ring-brand-primary focus:border-brand-primary py-2 px-3">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Mengantre (Pending)</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Diproses (Processing)</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Selesai (Resolved)</option>
                        </select>

                        <div class="relative flex-1 sm:w-56">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari subjek/nama..." class="w-full text-xs border border-gray-300 rounded-xl pl-8 pr-3 py-2 focus:ring-brand-primary focus:border-brand-primary">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Pengirim</th>
                                <th class="p-3">Kategori & Subjek Kendala</th>
                                <th class="p-3">Tanggal Pengajuan</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="p-3">
                                        <span class="font-bold text-gray-900 block">{{ $ticket->user?->name ?? 'User Terhapus' }}</span>
                                        <span class="text-[10px] text-gray-400 capitalize">
                                            Role: {{ $ticket->user?->role ?? '-' }} | {{ $ticket->user?->email ?? '' }}
                                        </span>
                                    </td>
                                    <td class="p-3 max-w-xs">
                                        <span class="px-2 py-0.5 rounded text-[9.5px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                            {{ $ticket->category }}
                                        </span>
                                        <span class="font-bold text-gray-900 block truncate mt-1">{{ $ticket->subject }}</span>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        <span class="text-gray-800">{{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        @if($ticket->status === 'pending')
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-clock"></i> Mengantre
                                            </span>
                                        @elseif($ticket->status === 'processing')
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-spinner animate-spin"></i> Diproses
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i> Selesai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right whitespace-nowrap">
                                        <a href="{{ route('operator.support-tickets.show', $ticket->id) }}" class="px-3 py-1.5 bg-brand-primary hover:bg-red-700 text-white font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Tanggapi
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 font-medium">
                                        <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
                                        Belum ada tiket bantuan kendala yang mengantre.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tickets->hasPages())
                    <div class="pt-2">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
