<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-headset text-brand-primary"></i>
            {{ __('Detail Tiket Bantuan') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-brand-bg min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Back Link -->
            <a href="{{ route('support-tickets.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-brand-primary transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Tiket Saya
            </a>

            <!-- Ticket Card Detail -->
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
                        <p class="text-xs text-gray-400 mt-0.5">Dikirim pada {{ $ticket->created_at->format('d F Y, H:i') }} WIB</p>
                    </div>

                    <div>
                        @if($ticket->status === 'pending')
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-clock"></i> Status: Mengantre
                            </span>
                        @elseif($ticket->status === 'processing')
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-spinner animate-spin"></i> Status: Sedang Diproses Operator
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i> Status: Kendala Selesai
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Original Message -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-envelope-open-text text-gray-400"></i> Pesan Keluhan Anda
                    </h4>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-800 leading-relaxed whitespace-pre-line">
                        {{ $ticket->message }}
                    </div>
                </div>

                <!-- Operator Response Section -->
                <div class="space-y-2 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-user-shield text-brand-primary"></i> Tanggapan Operator Sekolah
                    </h4>

                    @if($ticket->operator_response)
                        <div class="p-4 bg-emerald-50/70 rounded-xl border border-emerald-200 text-xs text-emerald-950 leading-relaxed whitespace-pre-line">
                            <div class="font-bold text-emerald-900 mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-comment-dots"></i> Tanggapan Resmi Operator:
                            </div>
                            {{ $ticket->operator_response }}
                        </div>
                    @else
                        <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 text-xs text-amber-900 leading-relaxed flex items-center gap-2">
                            <i class="fa-solid fa-hourglass-half text-amber-600 text-base shrink-0"></i>
                            <span>Belum ada tanggapan tertulis dari Operator Sekolah. Mohon menunggu, tiket Anda saat ini sedang dalam penanganan.</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
