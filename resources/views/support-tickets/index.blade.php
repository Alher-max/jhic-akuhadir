<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-text-main leading-tight flex items-center gap-2">
            <i class="fa-solid fa-headset text-brand-primary"></i>
            {{ __('Bantuan & Layanan Kendala') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-brand-bg min-h-screen" x-data="{ showCreateModal: false }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Header Section -->
            <div class="bg-brand-surface p-6 rounded-2xl border border-brand-border shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-life-ring text-amber-500"></i> Tiket Bantuan Kendala Saya
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Kirimkan pertanyaan atau kendala seputar absensi, akun, atau sistem untuk langsung ditanggapi oleh Operator Sekolah.
                    </p>
                </div>
                <button @click="showCreateModal = true" class="px-5 py-2.5 bg-brand-primary hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition inline-flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus"></i> Buat Tiket Bantuan
                </button>
            </div>

            <!-- Ticket Table / List -->
            <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Daftar Tiket Kendala</span>
                    <div class="flex gap-1.5">
                        <a href="{{ route('support-tickets.index') }}" class="px-3 py-1 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-600' }}">Semua</a>
                        <a href="{{ route('support-tickets.index', ['status' => 'pending']) }}" class="px-3 py-1 rounded-lg text-xs font-semibold {{ request('status') == 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600' }}">Mengantre</a>
                        <a href="{{ route('support-tickets.index', ['status' => 'processing']) }}" class="px-3 py-1 rounded-lg text-xs font-semibold {{ request('status') == 'processing' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">Diproses</a>
                        <a href="{{ route('support-tickets.index', ['status' => 'resolved']) }}" class="px-3 py-1 rounded-lg text-xs font-semibold {{ request('status') == 'resolved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">Selesai</a>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <div class="p-5 hover:bg-gray-50 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                        {{ $ticket->category }}
                                    </span>
                                    <h4 class="font-bold text-sm text-gray-900">{{ $ticket->subject }}</h4>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-1 max-w-2xl">{{ $ticket->message }}</p>
                                <span class="text-[10px] text-gray-400 block pt-1">
                                    Dikirim pada {{ $ticket->created_at->format('d M Y, H:i') }} WIB
                                </span>
                            </div>

                            <div class="flex items-center gap-3 self-end sm:self-center shrink-0">
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

                                <a href="{{ route('support-tickets.show', $ticket->id) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-brand-primary hover:text-white text-gray-700 text-xs font-bold rounded-lg transition inline-flex items-center gap-1">
                                    Detail <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-400 space-y-2">
                            <i class="fa-solid fa-headset text-4xl mb-2 block text-gray-300"></i>
                            <p class="font-bold text-sm text-gray-600">Belum Ada Tiket Kendala</p>
                            <p class="text-xs">Klik tombol "Buat Tiket Bantuan" di atas jika Anda memiliki pertanyaan atau masalah.</p>
                        </div>
                    @endforelse
                </div>

                @if($tickets->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Modal Buat Tiket Bantuan Baru -->
        <div x-show="showCreateModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition>
            <div @click.away="showCreateModal = false" class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-gray-100">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-extrabold text-base text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-brand-primary"></i> Buat Tiket Bantuan Kendala
                    </h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="{{ route('support-tickets.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kategori Kendala <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full border-gray-300 rounded-xl shadow-xs text-xs font-semibold focus:ring-brand-primary focus:border-brand-primary py-2 px-3">
                            <option value="Absensi">Absensi (PWA, GPS, Scanner, Penguncian Wi-Fi)</option>
                            <option value="Akun">Akun & Sandi (Login, Profil, Data Siswa/Guru)</option>
                            <option value="Jadwal">Jadwal & Jam Operasional</option>
                            <option value="Sistem">Kendala Sistem & Fitur Aplikasi</option>
                            <option value="Lainnya" selected>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Subjek / Judul Ringkas <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" placeholder="Cth: Tidak Bisa Clock-In Karena GPS Presisi" required class="w-full border-gray-300 rounded-xl shadow-xs text-xs font-medium focus:ring-brand-primary focus:border-brand-primary px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pesan & Detail Kendala <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="4" placeholder="Jelaskan secara singkat kendala yang Anda alami..." required class="w-full border-gray-300 rounded-xl shadow-xs text-xs focus:ring-brand-primary focus:border-brand-primary px-3 py-2"></textarea>
                    </div>

                    <div class="pt-3 border-t flex justify-end gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-brand-primary hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Tiket
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
