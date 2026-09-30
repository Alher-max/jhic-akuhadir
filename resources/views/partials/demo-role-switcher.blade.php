@php
    $demoAccount = config('demo.accounts')[auth()->user()->email] ?? null;
@endphp

@if ($demoAccount && auth()->user()->role === $demoAccount['role'])
    <nav aria-label="Beralih peran demo"
        class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 w-max max-w-[95vw]">
        <div class="max-w-[95vw] overflow-x-auto scrollbar-none snap-x flex items-center gap-1.5 p-1.5 bg-gray-900/95 md:bg-white/95 backdrop-blur shadow-2xl rounded-full border border-gray-700 md:border-slate-200">
            <span class="hidden shrink-0 px-2 text-xs font-bold uppercase tracking-wide text-slate-500 md:block">Demo</span>
            @foreach (config('demo.accounts') as $email => $account)
                <form method="POST" action="{{ route('demo.login') }}" class="shrink-0">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit"
                        @class([
                            'snap-start whitespace-nowrap rounded-full md:rounded-xl px-2.5 py-1 md:px-3 md:py-2 text-[11px] md:text-xs font-bold transition-colors',
                            'bg-indigo-500 text-white ring-2 ring-white/30 md:bg-slate-900 md:ring-0' => auth()->user()->email === $email,
                            'text-slate-200 hover:bg-white/10 md:text-slate-600 md:hover:bg-slate-100' => auth()->user()->email !== $email,
                        ])>
                        {{ $account['switcher_label'] }}
                    </button>
                </form>
            @endforeach
            <span class="mx-1 h-6 w-px shrink-0 bg-gray-600 md:bg-slate-200"></span>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit"
                    class="snap-start inline-flex items-center gap-1 whitespace-nowrap rounded-full md:rounded-xl px-2.5 py-1 md:px-3 md:py-2 text-[11px] md:text-xs font-bold text-rose-300 transition-colors hover:bg-white/10 md:text-rose-700 md:hover:bg-rose-50"
                    aria-label="Keluar Demo">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    <span class="md:hidden">Keluar</span>
                    <span class="hidden md:inline">Keluar Demo</span>
                </button>
            </form>
        </div>
    </nav>
@endif

<style>
    .scrollbar-none {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }
</style>
