// repartidor/sw.js
const CACHE_NAME = 'logistfg-repartidor-v3';
const ASSETS_APP_SHELL = [
    './offline.html',
    '../css/estilo.css',
    '../js/logica.js',
    './manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    'https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js'
];

// 1. INSTALACIÓN (Caché del App Shell)
self.addEventListener('install', (event) => {
    self.skipWaiting(); // Fuerza la instalación inmediata
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Precaching App Shell');
            return cache.addAll(ASSETS_APP_SHELL);
        })
    );
});

// 2. ACTIVACIÓN (Limpieza de cachés antiguas)
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('[Service Worker] Borrando caché antigua:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    self.clients.claim(); // Toma el control de las pestañas abiertas inmediatamente
});

// 3. FETCH (Estrategias de Red)
self.addEventListener('fetch', (event) => {
    // A. Peticiones de Navegación (HTML/PHP): Network First con Fallback a offline.html
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                console.warn('[Service Worker] Sin red. Sirviendo página offline.');
                return caches.match('./offline.html');
            })
        );
    } 
    // B. Peticiones de API (POST a PHP): No las cacheamos en Cache Storage, van por Background Sync
    else if (event.request.method === 'POST') {
        return; // Dejamos que el navegador intente el POST normalmente (si falla, el catch del Frontend usa IndexedDB)
    }
    // C. Peticiones de Estáticos (CSS, JS, Imágenes): Cache First, fallback a Red
    else {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse; // Devolvemos desde caché
                }
                // Si no está en caché, lo pedimos a la red y lo guardamos
                return fetch(event.request).then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        // Solo cacheamos peticiones GET válidas
                        if (event.request.method === 'GET' && networkResponse.status === 200) {
                            cache.put(event.request, networkResponse.clone());
                        }
                        return networkResponse;
                    });
                });
            }).catch(() => {
                // Silenciamos errores de fetch estáticos en modo offline
                return new Response(""); 
            })
        );
    }
});

// 4. BACKGROUND SYNC (Sincronización en segundo plano)
self.addEventListener('sync', function(event) {
    if (event.tag === 'sync-entregas') {
        console.log("[Service Worker] 🌐 Conexión recuperada. Iniciando Sync en segundo plano...");
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
                    let payload = cursor.value; // Datos guardados por el Frontend (idPedido, estado, etc.)
                    let key = cursor.key;

                    // Reintentar el envío al backend
                    fetch('../controladores/actualizarEstadoReparto.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: payload
                    }).then(response => response.text())
                      .then(data => {
                          if (data.trim() === "OK") {
                              console.log(`[Service Worker] Entrega ${key} sincronizada con éxito.`);
                              // Eliminamos el registro de IndexedDB porque ya está en MySQL
                              db.transaction('entregas_pendientes', 'readwrite').objectStore('entregas_pendientes').delete(key);
                          }
                      }).catch(err => console.error("[Service Worker] Fallo en la sincronización:", err));
                    
                    cursor.continue(); // Pasar al siguiente elemento en la cola offline
                } else {
                    resolve(); // Fin de la cola
                }
            };
        };
        request.onerror = () => reject();
    });
}