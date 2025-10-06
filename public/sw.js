const CACHE_NAME = 'smart-irrigation-v1.0.1';
const RUNTIME_CACHE = 'smart-irrigation-runtime';

// Files to cache immediately (only local files to avoid installation failures)
const PRECACHE_URLS = [
  '/',
  '/icon.svg',
  '/AgrinexLogo.jpg',
  '/manifest.json',
  '/offline.html'
];

// API endpoints to cache with network-first strategy
const API_ENDPOINTS = [
  '/api/devices',
  '/api/weather-summary',
  '/api/forecast',
  '/api/tank',
  '/api/irrigation-plan',
  '/api/water-usage',
  '/api/water-usage-daily'
];

// Install event - cache essential files
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Precaching app shell');
        // Use addAll with error handling
        return cache.addAll(PRECACHE_URLS).catch((err) => {
          console.error('[Service Worker] Precache failed:', err);
          // Still proceed even if some files fail
          return Promise.resolve();
        });
      })
      .then(() => {
        console.log('[Service Worker] Skip waiting...');
        return self.skipWaiting();
      })
      .catch((err) => {
        console.error('[Service Worker] Install failed:', err);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((cacheName) => {
              return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
            })
            .map((cacheName) => {
              console.log('[Service Worker] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            })
        );
      })
      .then(() => {
        console.log('[Service Worker] Claiming clients...');
        return self.clients.claim();
      })
      .catch((err) => {
        console.error('[Service Worker] Activation failed:', err);
      })
  );
});

// Fetch event - network first for API, cache first for assets
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests that aren't in our CDN list
  if (url.origin !== location.origin && !PRECACHE_URLS.includes(request.url)) {
    return;
  }

  // API requests - Network first, fallback to cache
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      networkFirst(request)
    );
    return;
  }

  // Static assets - Cache first, fallback to network
  event.respondWith(
    cacheFirst(request)
  );
});

// Network First Strategy (for API calls)
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.log('[Service Worker] Network request failed, trying cache:', request.url);
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    // Return offline page or error response
    return new Response(JSON.stringify({ 
      error: 'Offline', 
      message: 'No network connection and no cached data available' 
    }), {
      headers: { 'Content-Type': 'application/json' },
      status: 503
    });
  }
}

// Cache First Strategy (for static assets)
async function cacheFirst(request) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.log('[Service Worker] Fetch failed for:', request.url);
    // Return a fallback response
    return new Response('Network error occurred', {
      status: 408,
      headers: { 'Content-Type': 'text/plain' }
    });
  }
}

// Background Sync - for failed API requests
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Background sync:', event.tag);
  if (event.tag === 'sync-data') {
    event.waitUntil(syncData());
  }
});

async function syncData() {
  console.log('[Service Worker] Syncing data in background...');
  // Implement your sync logic here
  // For example, retry failed API requests
}

// Push Notification
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'Smart Irrigation System Update',
    icon: '/AgrinexLogo.jpg',
    badge: '/AgrinexLogo.jpg',
    vibrate: [200, 100, 200],
    tag: 'irrigation-notification',
    requireInteraction: false,
    actions: [
      { action: 'open', title: 'Open Dashboard', icon: '/AgrinexLogo.jpg' },
      { action: 'close', title: 'Close', icon: '/AgrinexLogo.jpg' }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('Smart Irrigation', options)
  );
});

// Notification Click
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked:', event.action);
  
  event.notification.close();

  if (event.action === 'open') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Message event - for cache updates
self.addEventListener('message', (event) => {
  console.log('[Service Worker] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => caches.delete(cacheName))
        );
      })
    );
  }
});

console.log('[Service Worker] Loaded successfully');
