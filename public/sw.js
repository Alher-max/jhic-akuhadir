const CACHE_NAME = 'hadiryuk-v1';
const STATIC_ASSETS = [
    '/',
    '/offline',
    '/manifest.json',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Service Worker: Caching Static Assets');
                return cache.addAll(STATIC_ASSETS);
            })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('Service Worker: Clearing Old Cache');
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    const requestUrl = new URL(event.request.url);

    // Filter requests
    if (event.request.method !== 'GET') return;

    // Is it a static asset? (js, css, images) -> Cache-First
    if (requestUrl.pathname.match(/\.(css|js|png|jpg|jpeg|svg|gif|woff2?|ttf|eot)$/)) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;
                return fetch(event.request).then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                });
            })
        );
        return;
    }

    // Is it a navigation request? (HTML Pages) -> Network-First with Offline Fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match('/offline');
                })
        );
        return;
    }

    // Fallback for everything else
    event.respondWith(fetch(event.request).catch(() => caches.match('/offline')));
});
