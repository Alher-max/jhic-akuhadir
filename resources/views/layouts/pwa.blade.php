<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HadirSekolah') }} - Mobile</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='85' font-style='italic' font-weight='900' fill='%23b91c1c' font-family='sans-serif'>H</text></svg>">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Scripts & Tailwind CSS -->
    <script src="{{ asset('js/image-compressor.js') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-bg': '#FBF9F9',
                        'brand-surface': '#FFFFFF',
                        'brand-border': '#EAE2E3',
                        'brand-primary': '#B81D24',
                        'brand-text-main': '#1A1516',
                        'brand-text-muted': '#6B5E60'
                    }
                }
            }
        }
    </script>

    <!-- PWA Optimized Meta Tags -->
    <meta name="theme-color" content="#B81D24">
    <meta name="apple-mobile-web-app-capable" content="yes">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-brand-bg text-brand-text-main h-screen flex flex-col font-sans">
    
    <!-- Top Navigation for PWA -->
    <header class="bg-brand-primary text-white shadow-sm px-4 py-3 flex justify-between items-center sticky top-0 z-50">
        <div class="font-bold text-lg">HadirSekolah PWA</div>
        <div>
            <!-- User Menu Trigger -->
            <button class="rounded-full bg-gray-100 p-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </button>
        </div>
    </header>

    <main class="p-4">
        {{ $slot }}
    </main>

    <!-- Bottom Navigation Bar (PWA standard) -->
    <nav class="fixed bottom-0 w-full bg-white border-t border-gray-200 flex justify-around items-center h-16 z-50">
        <a href="#" class="flex flex-col items-center text-indigo-600">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span class="text-xs">Leave</span>
        </a>
    </nav>

    @livewireScripts
</body>
</html>
