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

<!-- PWA Install Banner (Bottom Floating) -->
<div id="pwa-install-banner" style="display: none;" class="fixed bottom-20 md:bottom-20 left-4 right-4 md:max-w-md md:mx-auto z-50 bg-white border border-brand-border p-4 rounded-2xl shadow-2xl flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="HadirYuk" class="w-10 h-10 rounded-xl object-contain shrink-0 bg-brand-primary/10 p-1">
        <div>
            <p class="text-xs font-bold text-gray-900">Install Aplikasi HadirYuk</p>
            <p class="text-[11px] text-gray-500">Akses presensi lebih cepat & praktis!</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button id="pwa-install-close" aria-label="Tutup banner instalasi" class="text-gray-400 hover:text-gray-600 text-xs px-2 py-1.5 font-medium">
            <span class="md:hidden" aria-hidden="true">×</span>
            <span class="hidden md:inline">Nanti</span>
        </button>
        <button id="pwa-install-btn" class="bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold px-3 py-2 rounded-xl transition shadow-sm whitespace-nowrap">
            Install
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
