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

self.addEventListener('sync', function(event) {
    if (event.tag === 'sync-entregas') {
        console.log("Internet recuperado: Sincronizando entregas pendientes...");
        event.waitUntil(sincronizarEntregasPendientes());
    }
});

function sincronizarEntregasPendientes() {
    // Aquí leerías los datos guardados en IndexedDB
    // y harías un fetch() por cada entrega pendiente hacia actualizarEstadoReparto.php
    // Si el fetch es exitoso (200 OK), lo borras de IndexedDB.
    return Promise.resolve(); // Placeholder
}

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-entregas') {
        console.log("Internet recuperado: Sincronizando entregas con el servidor PHP...");
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