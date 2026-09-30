@php
    $demoAccount = config('demo.accounts')[auth()->user()->email] ?? null;
@endphp

@if ($demoAccount && auth()->user()->role === $demoAccount['role'])
    <nav aria-label="Beralih peran demo"
        class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 max-w-[calc(100%-1.5rem)] w-max">
        <div class="flex items-center gap-1.5 overflow-x-auto rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-xl backdrop-blur-md">
            <span class="hidden shrink-0 px-2 text-xs font-bold uppercase tracking-wide text-slate-500 sm:block">Demo</span>
            @foreach (config('demo.accounts') as $email => $account)
                <form method="POST" action="{{ route('demo.login') }}" class="shrink-0">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit"
                        @class([
                            'whitespace-nowrap rounded-xl px-3 py-2 text-xs font-bold transition-colors',
                            'bg-slate-900 text-white' => auth()->user()->email === $email,
                            'text-slate-600 hover:bg-slate-100' => auth()->user()->email !== $email,
                        ])>
                        {{ $account['switcher_label'] }}
                    </button>
                </form>
            @endforeach
            <span class="mx-1 h-6 w-px shrink-0 bg-slate-200"></span>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit"
                    class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-bold text-rose-700 transition-colors hover:bg-rose-50">
                    Keluar Demo
                </button>
            </form>
        </div>
    </nav>
@endif
