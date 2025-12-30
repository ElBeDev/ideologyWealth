const CACHE_NAME = '1life-financial-v6'; // Updated: Never cache API calls
const urlsToCache = [
  '/',
  '/index.html',
  '/offline.html',
  '/manifest.json'
  // Los demás archivos se cachearán dinámicamente cuando se soliciten
];

// Install Service Worker
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching essential files');
        // Cache archivos esenciales uno por uno para manejar errores individualmente
        return Promise.allSettled(
          urlsToCache.map(url => 
            cache.add(url).catch(err => {
              console.warn(`Service Worker: Failed to cache ${url}`, err);
              return null;
            })
          )
        );
      })
      .then(() => {
        console.log('Service Worker: Essential files cached');
      })
      .catch((err) => {
        console.error('Service Worker: Cache open failed', err);
      })
  );
  // Activa el nuevo service worker inmediatamente
  self.skipWaiting();
});

// Activate Service Worker
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
  return self.clients.claim();
});

// Fetch Event - Network First, falling back to cache
self.addEventListener('fetch', (event) => {
  // Skip cross-origin requests and chrome extensions
  if (!event.request.url.startsWith(self.location.origin) || 
      event.request.url.includes('chrome-extension')) {
    return;
  }

  // Skip requests to external domains
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  // NEVER cache API requests - always go to network
  if (event.request.url.includes('/api/')) {
    event.respondWith(fetch(event.request));
    return;
  }

  // Skip POST, PUT, DELETE requests (only cache GET requests)
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Check if we received a valid response
        if (!response || response.status !== 200 || response.type === 'error') {
          return response;
        }

        // Clone the response before caching
        const responseToCache = response.clone();
        
        // Only cache basic and cors responses
        if (response.type === 'basic' || response.type === 'cors') {
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          }).catch((err) => {
            console.log('Service Worker: Cache put failed', err);
          });
        }

        return response;
      })
      .catch((error) => {
        console.log('Service Worker: Fetch failed, trying cache...', error);
        
        // Network failed, try cache
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          
          // Return offline page for navigation requests
          if (event.request.mode === 'navigate') {
            return caches.match('/offline.html').then((offlinePage) => {
              return offlinePage || new Response('Offline', {
                status: 503,
                statusText: 'Service Unavailable'
              });
            });
          }
          
          // For other requests, return a basic error response
          return new Response('Network error', {
            status: 408,
            statusText: 'Request Timeout'
          });
        });
      })
  );
});

// Background Sync
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-forms') {
    event.waitUntil(syncForms());
  }
});

async function syncForms() {
  // Implement form sync logic here
  console.log('Service Worker: Syncing forms...');
}

// Push Notifications
self.addEventListener('push', (event) => {
  const options = {
    body: event.data ? event.data.text() : 'New notification from 1life Financial',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-72x72.png',
    vibrate: [200, 100, 200],
    tag: '1life-notification',
    requireInteraction: false,
    actions: [
      {
        action: 'open',
        title: 'Open App',
        icon: '/icons/icon-96x96.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/icons/icon-96x96.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('1life Financial', options)
  );
});

// Notification Click
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'open') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});
