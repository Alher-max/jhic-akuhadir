<!-- PWA Update Toast (Top) -->
<div id="pwa-update-banner" style="display: none;" class="fixed top-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-50 bg-slate-900 text-white p-4 rounded-2xl shadow-2xl border border-slate-700 flex items-center justify-between gap-3 animate-bounce">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-amber-400 text-2xl">system_update</span>
        <div>
            <p class="text-xs font-bold">Pembaruan Tersedia!</p>
            <p class="text-[11px] text-slate-300">Pembaruan aplikasi HadirYuk tersedia.</p>
        </div>
    </div>
    <button id="pwa-update-btn" class="bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold px-3 py-2 rounded-xl transition shadow-sm whitespace-nowrap">
        Update Sekarang
    </button>
</div>

<!-- PWA Install Banner (Sticky Top Bar) -->
<div id="pwa-install-banner" style="display: none;" class="sticky top-0 z-40 bg-gradient-to-r from-rose-50 to-orange-50 border-b border-rose-200 px-4 py-2 flex items-center justify-between gap-3 text-xs text-gray-800 shadow-sm">
    <div class="flex min-w-0 items-center gap-2">
        <img src="{{ asset('images/logo.png') }}" alt="" class="w-7 h-7 rounded-lg object-contain shrink-0 bg-white p-0.5">
        <p class="font-medium leading-snug">Pasang HadirYuk di layar utama untuk akses instan &amp; offline</p>
    </div>
    <div class="flex shrink-0 items-center gap-1.5">
        <button id="pwa-install-btn" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1 rounded-md text-xs font-semibold transition-colors whitespace-nowrap">
            Install
        </button>
        <button id="pwa-install-close" aria-label="Tutup banner instalasi" class="text-gray-500 hover:text-gray-800 text-base leading-none p-1.5 rounded-md transition-colors">
            <span aria-hidden="true">✕</span>
        </button>
    </div>
</div>

<script>
    let deferredPrompt = null;
    let newWorker = null;

    // 1. Handling PWA Install Banner
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        const installBanner = document.getElementById('pwa-install-banner');
        if (installBanner && !sessionStorage.getItem('pwa_install_dismissed')) {
            installBanner.style.display = 'flex';
        }
    });

    document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
        const installBanner = document.getElementById('pwa-install-banner');
        if (installBanner) installBanner.style.display = 'none';
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`PWA Install Choice: ${outcome}`);
            deferredPrompt = null;
        }
    });

    document.getElementById('pwa-install-close')?.addEventListener('click', () => {
        const installBanner = document.getElementById('pwa-install-banner');
        if (installBanner) installBanner.style.display = 'none';
        sessionStorage.setItem('pwa_install_dismissed', '1');
    });

    // 2. Service Worker Registration & Auto-Update
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => {
                    console.log('SW Registered with scope:', reg.scope);

                    // Check if there is already a waiting worker
                    if (reg.waiting) {
                        showUpdateBanner(reg.waiting);
                    }

                    // Check for updates on register
                    reg.onupdatefound = () => {
                        const installingWorker = reg.installing;
                        if (installingWorker) {
                            installingWorker.onstatechange = () => {
                                if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    showUpdateBanner(installingWorker);
                                }
                            };
                        }
                    };
                })
                .catch(err => console.log('SW Reg error:', err));

            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });
        });
    }

    function showUpdateBanner(worker) {
        newWorker = worker;
        const updateBanner = document.getElementById('pwa-update-banner');
        if (updateBanner) updateBanner.style.display = 'flex';
    }

    document.getElementById('pwa-update-btn')?.addEventListener('click', () => {
        if (newWorker) {
            newWorker.postMessage({ type: 'SKIP_WAITING' });
        } else {
            window.location.reload();
        }
    });
</script>
