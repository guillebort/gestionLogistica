const CACHE_NAME = 'logistfg-repartidor-v1';
const ASSETS_TO_CACHE = [
    './repartidor.php',
    '../css/estilo.css',
    '../js/logica.js',
    './manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
];

// 1. INSTALACIÓN: Guardamos los archivos estáticos en la caché
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Archivos en caché correctamente');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// 2. ACTIVACIÓN: Limpiamos cachés antiguas si cambiamos la versión
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

// 3. FETCH (Estrategia Network First): Intentamos ir a internet, si falla, tiramos de caché
self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // Si hay conexión, guardamos una copia fresca en caché
                return caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, networkResponse.clone());
                    return networkResponse;
                });
            })
            .catch(() => {
                // Si no hay conexión (modo offline), devolvemos lo que haya en la caché
                return caches.match(event.request);
            })
    );
});