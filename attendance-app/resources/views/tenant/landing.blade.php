<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} - HadirYuk Attendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased selection:bg-brand-primary selection:text-white flex flex-col min-h-screen">
    
    <!-- Top Branding Banner -->
    @if($tenant->banner_path)
    <div class="h-64 w-full bg-cover bg-center relative" style="background-image: url('{{ Storage::url($tenant->banner_path) }}')">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>
    @else
    <div class="h-64 w-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center relative">
        <svg class="w-24 h-24 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
    </div>
    @endif

    <!-- Content Card -->
    <div class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 -mt-24 relative z-10 pb-12">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            
            <div class="p-8 sm:p-12 text-center flex flex-col items-center">
                <!-- Logo -->
                @if($tenant->logo_path)
                    <img src="{{ Storage::url($tenant->logo_path) }}" alt="{{ $tenant->name }} Logo" class="w-32 h-32 rounded-full border-4 border-white shadow-lg -mt-24 mb-6 object-cover bg-white relative z-20">
                @else
                    <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg -mt-24 mb-6 bg-red-100 text-red-600 flex items-center justify-center text-4xl font-black uppercase relative z-20">
                        {{ substr($tenant->name, 0, 2) }}
                    </div>
                @endif
                
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">{{ $tenant->name }}</h1>
                <p class="text-brand-primary font-semibold mb-6 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Portal Resmi Kehadiran
                </p>
                
                @if($tenant->description)
                    <p class="text-gray-600 max-w-2xl text-lg mb-10 leading-relaxed">
                        {{ $tenant->description }}
                    </p>
                @else
                    <p class="text-gray-600 max-w-2xl text-lg mb-10 leading-relaxed">
                        Selamat datang di portal kehadiran digital resmi untuk <strong>{{ $tenant->name }}</strong>. Silakan masuk atau daftar menggunakan tautan di bawah ini.
                    </p>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-lg mx-auto">
                    <!-- Login Button -->
                    <a href="{{ route('login') }}" class="group relative flex flex-col items-center justify-center gap-2 px-6 py-4 bg-white border-2 border-gray-200 hover:border-red-600 hover:bg-red-50 text-gray-800 hover:text-red-700 rounded-xl font-bold transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        <span>Masuk Sistem</span>
                        <span class="text-xs font-normal text-gray-500 group-hover:text-red-600/70">Guru & Staf</span>
                    </a>
                    
                    <!-- Register Button -->
                    <a href="{{ route('register', ['role_type' => 'manager', 'tenant_code' => $tenant->code]) }}" class="group relative flex flex-col items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span>Daftar Baru</span>
                        <span class="text-xs font-normal text-white/70">Anggota Institusi</span>
                    </a>
                </div>
            </div>
            
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ $tenant->name }}
                </p>
                <p class="text-xs text-gray-400 flex items-center gap-1">
                    Powered by <strong class="text-red-600">HadirYuk</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
