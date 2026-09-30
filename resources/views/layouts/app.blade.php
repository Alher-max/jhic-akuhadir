<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#b91c1c">
    <link rel="manifest" href="/manifest.json">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">

    <title>{{ $title ?? 'HadirSekolah — Modern Attendance Platform' }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='85' font-style='italic' font-weight='900' fill='%23b91c1c' font-family='sans-serif'>H</text></svg>">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/image-compressor.js') }}"></script>
</head>

<body class="font-sans antialiased" style="overscroll-behavior-y: contain;" x-data="{ offline: !navigator.onLine }"
    @online.window="offline = false" @offline.window="offline = true">

    <!-- Pull to Refresh Spinner -->
    <div x-data="{
            startY: 0,
            pullDistance: 0,
            isPulling: false,
            isLoading: false,
            handleTouchStart(e) {
                if (window.scrollY === 0) {
                    this.startY = e.touches[0].clientY;
                    this.isPulling = true;
                }
            },
            handleTouchMove(e) {
                if (!this.isPulling) return;
                const currentY = e.touches[0].clientY;
                const diff = currentY - this.startY;
                if (diff > 0) {
                    this.pullDistance = Math.min(diff, 100);
                    e.preventDefault();
                }
            },
            handleTouchEnd() {
                if (this.pullDistance > 75) {
                    this.isLoading = true;
                    window.location.reload();
                } else {
                    this.pullDistance = 0;
                    this.isPulling = false;
                }
            }
        }" @touchstart.window="handleTouchStart($event)" @touchmove.window="handleTouchMove($event)"
        @touchend.window="handleTouchEnd()"
        class="fixed top-3 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 ease-out" :style="{
            transform: `translateX(-50%) translateY(${isLoading ? '20px' : (pullDistance > 0 ? (pullDistance / 2) + 'px' : '-50px')}) scale(${Math.min(pullDistance / 75, 1)})`,
            opacity: pullDistance > 0 || isLoading ? 1 : 0
        }">
        <div class="bg-white shadow-md rounded-full p-2.5 border border-gray-100">
            <div :class="{'animate-spin': isLoading}" class="w-6 h-6 flex items-center justify-center">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!isLoading" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M19 9l-7 7-7-7"></path>
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Offline Banner -->
    <div x-show="offline" style="display: none;" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-full" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-full"
        class="fixed top-0 left-0 right-0 z-50 bg-rose-600 text-white px-4 py-3 shadow-lg flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
        </svg>
        <span class="font-bold text-sm tracking-wide">Koneksi internet terputus. Anda berada dalam mode offline.</span>
    </div>

    <div class="min-h-screen bg-gray-100 flex flex-col justify-between">
        <div class="flex-1">
            @include('layouts.navigation')

            <!-- Global Alert & Notification Banner (Centralized for all roles) -->
            @if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                    @if(session('success'))
                        <div class="mb-3 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm"
                            role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-3 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm"
                            role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="mb-3 bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm"
                            role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                <span>{{ session('warning') }}</span>
                            </div>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="mb-3 bg-sky-50 border border-sky-300 text-sky-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between gap-3 text-sm"
                            role="alert">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-sky-600"></i>
                                <span>{{ session('info') }}</span>
                            </div>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-3 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-sm text-sm"
                            role="alert">
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
            <main class="pb-32 md:pb-16">
                {{ $slot }}
            </main>
        </div>

        <x-footer />
    </div>

    @if (config('app.env') === 'local')
        {{-- Di lingkungan lokal, unregister semua Service Worker agar tidak meng-cache tampilan --}}
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function (registrations) {
                    for (let registration of registrations) {
                        registration.unregister();
                    }
                });
                // Hapus semua cache SW
                if (window.caches) {
                    caches.keys().then(function (keyList) {
                        return Promise.all(keyList.map(function (key) {
                            return caches.delete(key);
                        }));
                    });
                }
            }
        </script>
    @else
        @include('partials.pwa-prompt')
    @endif
    <x-confirm-modal />
    @auth
        @include('partials.demo-role-switcher')
    @endauth
</body>

</html>