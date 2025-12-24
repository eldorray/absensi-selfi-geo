const CACHE_NAME = 'absensi-selfie-geo-v1';

// Assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/attendance',
    '/offline'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing Service Worker...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Caching static assets');
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating Service Worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - serve from cache, fetch from network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }

    // For CSS, JS, and other static assets - Cache First strategy
    if (request.destination === 'style' || 
        request.destination === 'script' || 
        request.destination === 'font' ||
        request.destination === 'image' ||
        url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version and update cache in background
                    event.waitUntil(
                        fetch(request).then((response) => {
                            if (response.ok) {
                                caches.open(CACHE_NAME).then((cache) => {
                                    cache.put(request, response.clone());
                                });
                            }
                        }).catch(() => {})
                    );
                    return cachedResponse;
                }

                // Not in cache, fetch and cache
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                }).catch(() => {
                    // Return offline fallback for images
                    if (request.destination === 'image') {
                        return new Response(
                            '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="#f3f4f6" width="100" height="100"/><text x="50" y="50" text-anchor="middle" dy="0.35em" fill="#9ca3af">Offline</text></svg>',
                            { headers: { 'Content-Type': 'image/svg+xml' } }
                        );
                    }
                });
            })
        );
        return;
    }

    // For HTML pages - Network First strategy with offline fallback
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache successful responses
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Try to return cached version
                    return caches.match(request).then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Return offline page
                        return caches.match('/offline');
                    });
                })
        );
        return;
    }

    // Default - Network First
    event.respondWith(
        fetch(request)
            .then((response) => {
                if (response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Listen for messages from the main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
