const CACHE_NAME = 'logistfg-repartidor-v2'; // Cambiamos versión para limpiar la caché antigua
const ASSETS_TO_CACHE = [
    './offline.html', // <-- Metemos la página offline en lugar del .php
    '../css/estilo.css',
    '../js/logica.js',
    './manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
];

// 1. INSTALACIÓN
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// 2. ACTIVACIÓN (Limpieza de cachés viejas)
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

// 3. FETCH: Estrategia para PWA con backend PHP
self.addEventListener('fetch', (event) => {
    // Si la petición es de navegación (pidiendo un archivo HTML o PHP entero)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                // Si la red falla (offline), devolvemos la página offline genérica
                return caches.match('./offline.html');
            })
        );
    } else {
        // Para el resto de cosas (CSS, JS, imágenes), intentamos red primero, y si no, tiramos de caché
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        // Guardamos copia de los assets estáticos
                        if (event.request.url.includes('.css') || event.request.url.includes('.js')) {
                            cache.put(event.request, networkResponse.clone());
                        }
                        return networkResponse;
                    });
                })
                .catch(() => {
                    return caches.match(event.request);
                })
        );
    }
});

// 4. BACKGROUND SYNC (Lo tenías muy bien planteado)
self.addEventListener('sync', function(event) {
    if (event.tag === 'sync-entregas') {
        console.log("Internet recuperado: Sincronizando entregas pendientes...");
        event.waitUntil(sincronizarEntregasPendientes());
    }
});

function sincronizarEntregasPendientes() {
    return new Promise((resolve, reject) => {
        let request = indexedDB.open('LogisTFG_Offline', 1);
        request.onsuccess = (e) => {
            let db = e.target.result;
            if (!db.objectStoreNames.contains('entregas_pendientes')) return resolve();
            
            let tx = db.transaction('entregas_pendientes', 'readwrite');
            let store = tx.objectStore('entregas_pendientes');
            let cursorReq = store.openCursor();

            cursorReq.onsuccess = (event) => {
                let cursor = event.target.result;
                if (cursor) {
                    let payload = cursor.value;
                    let key = cursor.key;

                    // Enviar al servidor MySQL a través de PHP
                    fetch('../controladores/actualizarEstadoReparto.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: payload
                    }).then(response => response.text())
                      .then(data => {
                          if (data.trim() === "OK") {
                              // Borrar de IndexedDB si el servidor lo procesó bien
                              db.transaction('entregas_pendientes', 'readwrite').objectStore('entregas_pendientes').delete(key);
                          }
                      });
                    cursor.continue();
                } else {
                    resolve();
                }
            };
        };
    });
}