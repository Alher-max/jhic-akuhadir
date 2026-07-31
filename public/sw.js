const CACHE_NAME = 'hadiryuk-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/favicon.ico',
    '/images/logo.png',
    '/images/icon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
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
                    return caches.match('/');
                })
        );
        return;
    }

    // Fallback for everything else
    event.respondWith(fetch(event.request).catch(() => caches.match('/')));
});

// Push Notification Handler
self.addEventListener('push', (event) => {
    let data = { title: 'Pemberitahuan Baru', body: 'Klik untuk melihat detail.', url: '/dashboard' };
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: '/images/logo.png',
        badge: '/images/icon.png',
        data: {
            url: data.url || '/dashboard'
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            const url = event.notification.data.url;
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
