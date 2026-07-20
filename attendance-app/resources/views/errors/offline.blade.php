<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>HadirYuk - Anda Offline</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS (via CDN for safety in offline mode if bundled CSS is somehow inaccessible, but SW will cache the bundled one anyway. Better use bundled) -->
        <style>
            /* Minimal fallback styles just in case CSS doesn't load */
            body { font-family: 'Figtree', sans-serif; background-color: #f9fafb; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; color: #111827; }
            .container { text-align: center; padding: 2rem; }
            .icon { width: 80px; height: 80px; color: #9ca3af; margin: 0 auto 1.5rem auto; }
            h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.025em; }
            p { color: #6b7280; font-size: 0.95rem; margin-bottom: 2rem; max-width: 300px; margin-left: auto; margin-right: auto; line-height: 1.5; }
            button { background-color: #4f46e5; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2); }
            button:active { transform: scale(0.95); }
        </style>
    </head>
    <body>
        <div class="container">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
            </svg>
            <h1>Ups! Koneksi Anda Terputus</h1>
            <p>Sepertinya Anda sedang offline. Silakan periksa koneksi internet Anda atau tunggu hingga sinyal kembali stabil.</p>
            <button onclick="window.location.reload()">Coba Hubungkan Kembali</button>
        </div>
    </body>
</html>
