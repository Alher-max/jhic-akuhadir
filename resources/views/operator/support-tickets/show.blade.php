<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-headset text-brand-primary"></i>
            {{ __('Tanggapi Tiket Bantuan Kendala') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-brand-bg min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Back Link -->
            <a href="{{ route('operator.support-tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-brand-primary transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Antrean Tiket Operator
            </a>

            <!-- Ticket Detail Card -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm p-6 space-y-6">
                <!-- Header Info -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                {{ $ticket->category }}
                            </span>
                            <span class="text-xs text-gray-400">#TIKET-{{ $ticket->id }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $ticket->subject }}</h3>
                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                            <span><i class="fa-solid fa-user text-gray-400"></i> {{ $ticket->user?->name ?? 'Pengguna' }} ({{ ucfirst($ticket->user?->role ?? '-') }})</span>
                            •
                            <span><i class="fa-solid fa-clock text-gray-400"></i> {{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }} WIB</span>
                        </p>
                    </div>

                    <div>
                        @if($ticket->status === 'pending')
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-clock"></i> Mengantre (Pending)
                            </span>
                        @elseif($ticket->status === 'processing')
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-spinner animate-spin"></i> Sedang Diproses
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i> Selesai (Resolved)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- User Message -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-envelope-open-text text-gray-400"></i> Rincian Pesan Keluhan Pengguna
                    </h4>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-800 leading-relaxed whitespace-pre-line">
                        {{ $ticket->message }}
                    </div>
                </div>

                <!-- Response & Status Form -->
                <form action="{{ route('operator.support-tickets.update', $ticket->id) }}" method="POST" class="space-y-4 pt-4 border-t border-gray-100">
                    @csrf
                    @method('PUT')

                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-comment-dots text-brand-primary"></i> Beri Tanggapan Operator & Perbarui Status
                    </h4>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Status Tiket <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full sm:w-64 border-gray-300 rounded-xl shadow-xs text-xs font-bold focus:ring-brand-primary focus:border-brand-primary py-2 px-3">
                            <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Mengantre (Pending)</option>
                            <option value="processing" {{ $ticket->status === 'processing' ? 'selected' : '' }}>Sedang Diproses (Processing)</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Selesai Diselesaikan (Resolved)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pesan Balasan / Solusi Operator</label>
                        <textarea name="operator_response" rows="4" placeholder="Tuliskan pesan penjelasan, instruksi, atau solusi untuk pengguna..." class="w-full border-gray-300 rounded-xl shadow-xs text-xs focus:ring-brand-primary focus:border-brand-primary px-3 py-2">{{ old('operator_response', $ticket->operator_response) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-brand-primary hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition inline-flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Tanggapan & Perbarui Status
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
