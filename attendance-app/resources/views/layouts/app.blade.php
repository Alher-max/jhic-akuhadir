<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="theme-color" content="#4f46e5">
        <link rel="manifest" href="/manifest.json">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'HadirSekolah — Modern Attendance Platform' }}</title>
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='85' font-style='italic' font-weight='900' fill='%23b91c1c' font-family='sans-serif'>H</text></svg>">
        <link rel="icon" type="image/png" href="{{ asset('hadiryuklogo1-4.png') }}?v={{ time() }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" x-data="{ offline: !navigator.onLine }" @online.window="offline = false" @offline.window="offline = true">
        
        <!-- Offline Banner -->
        <div x-show="offline" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-full"
             class="fixed top-0 left-0 right-0 z-50 bg-rose-600 text-white px-4 py-3 shadow-lg flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span class="font-bold text-sm tracking-wide">Koneksi internet terputus. Anda berada dalam mode offline.</span>
        </div>

        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Global Alert & Notification Banner (Centralized for all roles) -->
            @if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    @if(session('success'))
                        <div class="mb-3 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm" role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-3 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm" role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="mb-3 bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm" role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                <span>{{ session('warning') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="mb-3 bg-sky-50 border border-sky-300 text-sky-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm" role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-sky-600"></i>
                                <span>{{ session('info') }}</span>
                            </div>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-3 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-sm text-sm" role="alert">
                            <div class="flex items-center gap-2 font-semibold mb-1">
                                <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                                <span>Terdapat kesalahan input:</span>
                            </div>
                            <ul class="list-disc list-inside pl-5 space-y-0.5 text-xs text-rose-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @if (config('app.env') === 'local')
        {{-- Di lingkungan lokal, unregister semua Service Worker agar tidak meng-cache tampilan --}}
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    for (let registration of registrations) {
                        registration.unregister();
                    }
                });
                // Hapus semua cache SW
                if (window.caches) {
                    caches.keys().then(function(keyList) {
                        return Promise.all(keyList.map(function(key) {
                            return caches.delete(key);
                        }));
                    });
                }
            }
        </script>
        @else
        {{-- Di production, daftarkan Service Worker seperti biasa --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(registration => {
                            console.log('ServiceWorker registration successful with scope: ', registration.scope);
                        })
                        .catch(err => {
                            console.log('ServiceWorker registration failed: ', err);
                        });
                });
            }
        </script>
        @endif
    </body>
</html>
