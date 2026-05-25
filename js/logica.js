/**
 * TFG - LogisTFG
 * Archivo Principal de Lógica Frontend
 */

const DB_NAME = 'LogisTFG_Offline';
const STORE_NAME = 'entregas_pendientes';

/* ==========================================
   1. EVENTOS (Esperamos a que cargue el DOM)
   ========================================== */
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. LÓGICA DE USUARIO (usuario.html) ---
    const radioAcceso = document.getElementById('radioAcceso');
    const radioRegistro = document.getElementById('radioRegistro');
    if (radioAcceso && radioRegistro) {
        radioAcceso.addEventListener('change', cambiarModoUsuario);
        radioRegistro.addEventListener('change', cambiarModoUsuario);
    }

    // --- 2. LÓGICA DE PRODUCTOS (productos.html) ---
    const botonesAñadir = document.querySelectorAll('.btn-add-carrito');
    botonesAñadir.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const servicio = e.target.getAttribute('data-servicio');
            const precio = e.target.getAttribute('data-precio');
            alert(`¡Has añadido el servicio: ${servicio} al carrito por ${precio}€!`);
        });
    });

    // --- 3. LÓGICA DE CARRITO (carrito.html) ---
    const inputsCantidad = document.querySelectorAll('.input-cantidad');
    inputsCantidad.forEach(input => {
        input.addEventListener('change', recalcularCarrito);
    });

    const botonesEliminar = document.querySelectorAll('.btn-eliminar-fila');
    botonesEliminar.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const filaId = e.target.getAttribute('data-fila');
            eliminarFilaCarrito(filaId);
        });
    });

    const btnFormalizar = document.getElementById('btnFormalizar');
    if (btnFormalizar) {
        btnFormalizar.addEventListener('click', () => {
            if (document.querySelectorAll("#cuerpo_carrito tr").length === 0) {
                alert("No tienes ningún servicio en el carrito para formalizar.");
            } else {
                window.location.href = "checkout.html";
            }
        });
    }

    // --- 4. LÓGICA MI CUENTA (miCuenta.html) ---
    const botonesCancelar = document.querySelectorAll('.btn-cancelar-pedido');
    botonesCancelar.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const pedidoId = e.target.getAttribute('data-pedido');
            cancelarPedido(pedidoId);
        });
    });

    // --- 5. LÓGICA CHECKOUT (checkout.html) ---
    const formCheckout = document.getElementById('formCheckout');
    if (formCheckout) {
        formCheckout.addEventListener('submit', () => {
            alert('¡Pago procesado con éxito! Serás redirigido a tu panel de usuario.');
        });
    }

    // --- 6. LÓGICA PARA LA PASARELA DE PAGO (Tarjetas) ---
    const selectTarjeta = document.getElementById('tarjetaGuardada');
    const seccionNueva = document.getElementById('seccionNuevaTarjeta');

    if (selectTarjeta) {
        selectTarjeta.addEventListener('change', alternarCamposTarjeta);
    }
    if (seccionNueva) {
        alternarCamposTarjeta();
    }

    const inputTarjeta = document.getElementById('numeroTarjeta');
    if (inputTarjeta) {
        inputTarjeta.addEventListener('input', function (e) {
            let input = e.target.value.replace(/\D/g, ''); 
            if (input.length > 16) input = input.substring(0, 16);
            e.target.value = input.replace(/(\d{4})(?=\d)/g, '$1 ');
        });
    }

    const inputCaducidad = document.getElementById('caducidadTarjeta');
    if (inputCaducidad) {
        inputCaducidad.addEventListener('input', function (e) {
            let input = e.target.value.replace(/\D/g, ''); 
            if (input.length > 6) input = input.substring(0, 6);
            if (input.length > 2) {
                e.target.value = input.substring(0, 2) + '/' + input.substring(2);
            } else {
                e.target.value = input;
            }
        });
    }

    // --- 7. AUTOCOMPLETADO DE DIRECCIONES (Nominatim OSM) ---
    activarAutocompletado('input_origen', 'lista_origen', 'lat_origen', 'lon_origen');
    activarAutocompletado('input_destino', 'lista_destino', 'lat_destino', 'lon_destino');
    activarAutocompletadoUnico('input_direccion', 'lista_sugerencias');

    // --- 8. LÓGICA DEL MAPA DEL REPARTIDOR UNIFICADA (Leaflet + Routing) ---
    const mapaElem = document.getElementById('mapa-repartidor');
    let map = null;
    let controlRuta = null;
    let cocheMarker = null;

    // Variables globales para la navegación paso a paso
    let rutaOptimaOrdenada = [];
    let pasoRutaActual = 0;

    if (mapaElem) {
        map = L.map('mapa-repartidor').setView([39.4699, -0.3762], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
    }

    // Ocultamos los botones de simulación individual (ahora navegamos paso a paso)
    document.querySelectorAll('.btn-simular-ruta').forEach(btn => btn.style.display = 'none');

    const btnOptimizar = document.getElementById('btn-optimizar-ruta');
    const btnSiguiente = document.getElementById('btn-siguiente-parada');

    if (btnOptimizar && btnSiguiente) {
        
        // --- A. CALCULAR RUTA ÓPTIMA CON API REAL (OpenRouteService) ---
        btnOptimizar.addEventListener('click', async () => {
            if (!map) return;
            const paradas = document.querySelectorAll('.parada-card');
            if (paradas.length === 0) {
                alert("No hay entregas pendientes para organizar.");
                return;
            }

            // 1. Preparar las coordenadas (Formato ORS: [longitud, latitud] ¡Importante el orden!)
            // Índice 0 será nuestra Central Logística
            const latOrigen = parseFloat(paradas[0].querySelector('.btn-simular-ruta').getAttribute('data-lato'));
            const lonOrigen = parseFloat(paradas[0].querySelector('.btn-simular-ruta').getAttribute('data-lono'));
            
            let localizaciones = [[lonOrigen, latOrigen]];
            let infoParadas = [{ idPedido: 'central', cardElement: null, latLng: L.latLng(latOrigen, lonOrigen) }];

            // Añadir el resto de destinos
            paradas.forEach(card => {
                const btn = card.querySelector('.btn-simular-ruta');
                const lat = parseFloat(btn.getAttribute('data-latd'));
                const lon = parseFloat(btn.getAttribute('data-lond'));
                localizaciones.push([lon, lat]);
                infoParadas.push({
                    idPedido: card.id,
                    cardElement: card,
                    latLng: L.latLng(lat, lon)
                });
            });

            // 2. Llamada a la API de OpenRouteService (Matrix)
            const apiKey = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjYzNmZkNTNkOWJjNDRjMzBiNGY1NTQ2NmY1MjE2YzM4IiwiaCI6Im11cm11cjY0In0='; // Sustituye por tu clave real
            const url = 'https://api.openrouteservice.org/v2/matrix/driving-car';

            btnOptimizar.innerHTML = '⏳ Calculando ruta real...';
            btnOptimizar.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': apiKey
                    },
                    body: JSON.stringify({
                        locations: localizaciones,
                        metrics: ["distance"] // Queremos la distancia en metros
                    })
                });

                if (!response.ok) throw new Error("Fallo en la API de enrutamiento");
                
                const data = await response.json();
                const matrizDistancias = data.distances; // Matriz NxN con distancias reales

                // 3. Algoritmo: Vecino Más Cercano (Nearest Neighbor) usando la matriz real
                let noVisitados = Array.from({length: infoParadas.length - 1}, (_, i) => i + 1); // Índices del 1 al N
                let indiceActual = 0; // Empezamos en la central (índice 0)
                
                rutaOptimaOrdenada = [infoParadas[0]];

                while (noVisitados.length > 0) {
                    let indiceMasCercano = -1;
                    let distanciaMin = Infinity;
                    let indexEnArrayNoVisitados = -1;
                    
                    for (let i = 0; i < noVisitados.length; i++) {
                        let candidato = noVisitados[i];
                        // Buscamos la distancia en la matriz devuelta por la API
                        let distReal = matrizDistancias[indiceActual][candidato]; 
                        
                        if (distReal < distanciaMin) {
                            distanciaMin = distReal;
                            indiceMasCercano = candidato;
                            indexEnArrayNoVisitados = i;
                        }
                    }
                    
                    // Saltamos al vecino más cercano
                    indiceActual = indiceMasCercano;
                    rutaOptimaOrdenada.push(infoParadas[indiceActual]);
                    noVisitados.splice(indexEnArrayNoVisitados, 1); 
                }

                // 4. Dibujar la ruta panorámica inicial (Overview)
                if (controlRuta != null) map.removeControl(controlRuta);
                if (cocheMarker != null) map.removeLayer(cocheMarker);

                controlRuta = L.Routing.control({
                    waypoints: rutaOptimaOrdenada.map(p => p.latLng),
                    routeWhileDragging: false,
                    addWaypoints: false,
                    fitSelectedRoutes: true,
                    lineOptions: { styles: [{color: '#6c757d', opacity: 0.5, weight: 4}] },
                    createMarker: function(i, wp) {
                        if (i === 0) return L.marker(wp.latLng).bindPopup("<b>🟢 Central Logística</b>");
                        return L.marker(wp.latLng).bindPopup(`<b>📍 Parada ${i}</b>`);
                    }
                }).addTo(map);

                window.scrollTo({ top: 0, behavior: 'smooth' });

                // 5. Reorganizar tarjetas en el DOM
                const contenedorTarjetas = document.getElementById('lista-paradas');
                rutaOptimaOrdenada.forEach(paso => {
                    if (paso.idPedido !== 'central') {
                        contenedorTarjetas.appendChild(paso.cardElement);
                    }
                });

                // 6. Cambiar la interfaz de botones
                btnOptimizar.classList.add('d-none');
                btnOptimizar.innerHTML = '🗺️ Calcular Ruta Óptima';
                btnOptimizar.disabled = false;
                
                btnSiguiente.classList.remove('d-none');
                pasoRutaActual = 0; 
                
                alert(`¡Ruta óptima calculada con datos de tráfico real!\nTienes ${rutaOptimaOrdenada.length - 1} paradas.`);

            } catch (error) {
                console.error(error);
                alert("Hubo un error calculando la ruta con la API. Revisa tu conexión o tu API Key.");
                btnOptimizar.innerHTML = '🗺️ Calcular Ruta Óptima';
                btnOptimizar.disabled = false;
            }
        });

        // --- B. NAVEGACIÓN PASO A PASO ---
        btnSiguiente.addEventListener('click', () => {
            if (pasoRutaActual >= rutaOptimaOrdenada.length - 1) {
                alert("¡Ruta finalizada! Has completado todos los destinos planificados.");
                btnSiguiente.classList.add('d-none');
                btnOptimizar.classList.remove('d-none');
                return;
            }

            const origen = rutaOptimaOrdenada[pasoRutaActual].latLng;
            const destino = rutaOptimaOrdenada[pasoRutaActual + 1];

            // Limpiamos el mapa general y trazamos solo el tramo actual
            if (controlRuta != null) map.removeControl(controlRuta);
            if (cocheMarker != null) map.removeLayer(cocheMarker);

            controlRuta = L.Routing.control({
                waypoints: [origen, destino.latLng],
                routeWhileDragging: false,
                addWaypoints: false,
                fitSelectedRoutes: true,
                lineOptions: { styles: [{color: '#10b981', opacity: 0.9, weight: 6}] }, // Verde intenso para el tramo activo
                createMarker: function(i, wp) {
                    if (i === 0) return L.marker(wp.latLng).bindPopup("<b>🚗 Tu ubicación</b>");
                    return L.marker(wp.latLng).bindPopup("<b>📍 Siguiente Entrega</b>");
                }
            }).addTo(map);

            // Animar el coche usando la función existente cuando se calcule el tramo
            controlRuta.on('routesfound', function(e) {
                animarCocheEnRuta(e.routes[0].coordinates, map);
            });

            // Resaltar visualmente la tarjeta del pedido activo
            document.querySelectorAll('.parada-card').forEach(c => {
                c.classList.remove('border', 'border-primary', 'border-3', 'shadow-lg');
                c.style.opacity = "0.6"; // Atenuar los demás
            });
            destino.cardElement.classList.add('border', 'border-primary', 'border-3', 'shadow-lg');
            destino.cardElement.style.opacity = "1";
            
            // Hacer scroll automático hacia la tarjeta que toca entregar
            destino.cardElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Avanzar el contador interno
            pasoRutaActual++;
            
            // Actualizar texto del botón para la siguiente vez
            btnSiguiente.innerHTML = `🚗 Ir a la siguiente parada (${pasoRutaActual}/${rutaOptimaOrdenada.length - 1})`;
        });
    }

    // Gestión de entregas
    const botonesEntregado = document.querySelectorAll('.btn-entregado');
    const botonesIncidencia = document.querySelectorAll('.btn-incidencia');

    botonesEntregado.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const pedidoId = btn.getAttribute('data-pedido');
            Swal.fire({
                title: 'Firma de Recepción',
                html: `
                    <p class="text-muted small mb-2">Firme en el recuadro inferior para confirmar la entrega.</p>
                    <div class="border rounded-3 border-primary shadow-sm" style="background: #fff; overflow: hidden;">
                        <canvas id="firmaCanvas" style="width: 100%; height: 250px; touch-action: none; display: block;"></canvas>
                    </div>
                    <button type="button" id="btnLimpiarFirma" class="btn btn-sm btn-link text-danger mt-2">Limpiar firma</button>
                `,
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmar Entrega ✔️',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    // Truco para que el canvas se ajuste perfectamente al modal de SweetAlert
                    const canvas = document.getElementById('firmaCanvas');
                    // Forzamos el tamaño real del canvas basado en el CSS
                    canvas.width = canvas.offsetWidth;
                    canvas.height = canvas.offsetHeight;
                    
                    // Iniciamos la librería
                    window.signaturePad = new SignaturePad(canvas, {
                        penColor: "rgb(15, 23, 42)" 
                    });

                    document.getElementById('btnLimpiarFirma').addEventListener('click', () => {
                        window.signaturePad.clear();
                    });
                },
                preConfirm: () => {
                    if (window.signaturePad.isEmpty()) {
                        Swal.showValidationMessage('⚠️ Es obligatorio firmar para entregar el paquete');
                        return false;
                    }
                    return window.signaturePad.toDataURL(); // Extraemos la firma en Base64
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const firma = result.value;
                    
                    
                    // Enviamos el estado 3 (Entregado) y la firma al backend
                    procesarEstadoReparto(pedidoId, 3, btn.closest('.card'), '', firma); 
                }
            });
        });
    });

    // Gestión de Incidencias (Sustituto moderno del prompt)
    botonesIncidencia.forEach(btn => {
        btn.addEventListener('click', (e) => {
            Swal.fire({
                title: 'Reportar Incidencia',
                input: 'text',
                inputLabel: 'Describe la incidencia (Ej: Ausente, Dirección incorrecta):',
                inputPlaceholder: 'Escribe el motivo aquí...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Reportar ❌',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return '¡Necesitas escribir un motivo!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const pedidoId = e.target.getAttribute('data-pedido');
                    procesarEstadoReparto(pedidoId, 4, e.target.closest('.card'), result.value); 
                    Swal.fire('Reportado', 'La incidencia ha sido registrada.', 'info');
                }
            });
        });
    });

    // Iniciar IndexedDB para modo offline
    initDB();

    /* ==========================================
       9. LÓGICA DE VALIDACIÓN REGISTRO USUARIO
       ========================================== */
    const pass2Registro = document.getElementById('pass2');
    if (pass2Registro) {
        pass2Registro.addEventListener('input', function() {
            let pass1 = document.getElementById('pass1').value;
            let pass2 = this.value;
            let error = document.getElementById('errorPass');
            if (pass2.length > 0 && pass1 !== pass2) {
                this.classList.add('is-invalid');
                error.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                if(pass2.length > 0) this.classList.add('is-valid');
                error.style.display = 'none';
            }
        });
    }

    /* ==========================================
       10. LÓGICA DE SINCRONIZACIÓN DE TARJETAS
       ========================================== */
    const radioTarjetas = document.querySelectorAll('.tarjeta-radio');
    const selectReal = document.getElementById('tarjetaGuardada');
    if (radioTarjetas.length > 0 && selectReal) {
        radioTarjetas.forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('opt_' + this.value).selected = true;
                selectReal.dispatchEvent(new Event('change'));
            });
        });
    }

    /* ==========================================
       11. INICIALIZACIÓN MAPA LOGÍSTICO (ADMIN)
       ========================================== */
    const mapaAdminElem = document.getElementById('mapa-logistico');
    if (mapaAdminElem) {
        // Inicializar mapa centrado en España/Valencia
        const mapAdmin = L.map('mapa-logistico').setView([39.4699, -0.3762], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapAdmin);

        // Extraer los datos inyectados en el atributo HTML
        const pedidosRaw = mapaAdminElem.getAttribute('data-pedidos');
        if (pedidosRaw) {
            const pedidosAdmin = JSON.parse(pedidosRaw);
            const limites = [];

            const iconoPedido = L.divIcon({
                html: '<div style="background-color: #0d6efd; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); font-weight: bold; font-size: 10px;">📦</div>',
                className: '',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            pedidosAdmin.forEach(function(ped) {
                if (ped.latitud && ped.longitud && ped.latitud != "0.0") {
                    const marker = L.marker([ped.latitud, ped.longitud], {icon: iconoPedido}).addTo(mapAdmin);
                    marker.bindPopup("<div class='text-center'><b>Pedido #" + ped.id + "</b><br><span class='text-muted'>" + ped.cliente + "</span><br><small>" + ped.destino + "</small></div>");
                    limites.push([ped.latitud, ped.longitud]);
                }
            });

            if (limites.length > 0) {
                mapAdmin.fitBounds(limites, {padding: [30, 30]});
            }
        }
    }

    // --- Lógica para Asignar Repartidor (Admin) vía API REST ---
    const formsAsignacion = document.querySelectorAll('form[action="../controladores/asignarRepartidor.php"]');
    
    formsAsignacion.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitamos que la página recargue
            
            const formData = new FormData(this);
            const selectRepartidor = this.querySelector('select');
            
            if (selectRepartidor.value === "") {
                alert("Debes seleccionar un repartidor primero.");
                return;
            }

            fetch('../controladores/asignarRepartidor.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData // Fetch formatea automáticamente los FormData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || "Error de red");
                return data;
            })
            .then(data => {
                if (data.status === "success") {
                    // Ocultamos la tarjeta del pedido asignado con una animación suave
                    const tarjetaPedido = this.closest('.bg-light');
                    tarjetaPedido.style.transition = "opacity 0.5s";
                    tarjetaPedido.style.opacity = "0";
                    setTimeout(() => tarjetaPedido.remove(), 500);
                    
                    // Opcional: Actualizar el contador de pendientes
                    let badgePendientes = document.querySelector('.card-header .badge');
                    if (badgePendientes) {
                        let actual = parseInt(badgePendientes.innerText);
                        badgePendientes.innerText = (actual - 1) + " Pdte.";
                    }
                    
                    // Mostrar notificación de éxito
                    console.log(data.message);
                }
            })
            .catch(error => {
                alert("Error al asignar: " + error.message);
            });
        });
    });

    /* ==========================================
       LÓGICA DE AUTENTICACIÓN Y REGISTRO AJAX (SPA UX)
       ========================================== */
    const formsAuth = document.querySelectorAll('form[action="../controladores/login.php"], form[action="../controladores/loginAdminController.php"], form[action="../controladores/registro.php"]');
    
    formsAuth.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Detenemos la recarga tradicional
            
            // Si es el formulario de registro, validamos contraseñas aquí mismo antes de llamar a la API
            const inputPass1 = this.querySelector('input[name="clave"]');
            const inputPass2 = this.querySelector('input[name="clave2"]');
            if (inputPass1 && inputPass2 && inputPass1.value !== inputPass2.value) {
                mostrarErrorAuth(this, "⚠️ Las contraseñas no coinciden.");
                return;
            }

            const btnSubmit = this.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            
            // 1. Mostrar estado de carga (Spinner)
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';
            
            // 2. Ocultar errores previos
            let errorDiv = this.parentElement.querySelector('.alert-ajax-error');
            if (errorDiv) errorDiv.classList.add('d-none');

            // 3. Enviar datos al endpoint REST
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || "Error al conectar con la base de datos");
                return data;
            })
            .then(data => {
                if (data.status === "success") {
                    btnSubmit.classList.remove('btn-primary', 'btn-dark');
                    btnSubmit.classList.add('btn-success');
                    btnSubmit.innerHTML = '¡Completado! 🚀';
                    
                    setTimeout(() => {
                        window.location.href = data.data.redirect;
                    }, 500);
                }
            })
            .catch(error => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                mostrarErrorAuth(this, error.message);
            });
        });
    });

    // Función auxiliar para mostrar el error y hacer la animación
    function mostrarErrorAuth(formulario, mensaje) {
        let errorDiv = formulario.parentElement.querySelector('.alert-ajax-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger text-center rounded-4 shadow-sm mb-4 border-0 d-none alert-ajax-error fw-medium';
            formulario.parentElement.insertBefore(errorDiv, formulario);
        }
        errorDiv.innerText = "⚠️ " + mensaje;
        errorDiv.classList.remove('d-none');
        
        // Efecto sacudida (Shake)
        formulario.classList.add('animate__animated', 'animate__shakeX');
        setTimeout(() => formulario.classList.remove('animate__animated', 'animate__shakeX'), 1000);
    }

    /* ==========================================
       LÓGICA PARA GUARDAR RUTA (PASO 1 CHECKOUT)
       ========================================== */
    const formRuta = document.querySelector('form[action="../controladores/guardarRuta.php"]');
    
    if (formRuta) {
        formRuta.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitamos la recarga
            
            const btnSubmit = this.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            
            // Verificamos que las coordenadas no sean 0.0 (es decir, que hayan usado el autocompletado de OpenStreetMap)
            const latO = document.getElementById('lat_origen').value;
            const latD = document.getElementById('lat_destino').value;
            
            if (latO === "0.0" || latD === "0.0") {
                alert("⚠️ Por favor, selecciona una dirección válida de la lista de sugerencias para calcular las coordenadas GPS exactas.");
                return;
            }

            // Mostramos el Spinner
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Calculando ruta...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || "Error al procesar la ruta");
                return data;
            })
            .then(data => {
                if (data.status === "success") {
                    btnSubmit.classList.replace('btn-primary', 'btn-success');
                    btnSubmit.innerHTML = '¡Ruta Confirmada! ➔';
                    
                    // Transición suave hacia la pasarela de pago
                    setTimeout(() => {
                        window.location.href = data.data.redirect;
                    }, 400);
                }
            })
            .catch(error => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                alert("Error: " + error.message);
            });
        });
    }

}); // FIN DEL DOMContentLoaded


/* ==========================================
   2. FUNCIONES GLOBALES / AUXILIARES
   ========================================== */

function cambiarModoUsuario() {
    const radioRegistro = document.getElementById('radioRegistro');
    const camposRegistro = document.getElementById('campos_registro');
    const btnSubmit = document.getElementById('btn_submit');
    const inputsExtra = camposRegistro.querySelectorAll('input');
    const formulario = btnSubmit.closest('form');

    if (radioRegistro.checked) {
        camposRegistro.classList.remove('d-none');
        btnSubmit.textContent = 'Registrarse';
        inputsExtra.forEach(input => input.setAttribute('required', 'true'));
        if (formulario) formulario.action = '../controladores/registro.php';
    } else {
        camposRegistro.classList.add('d-none');
        btnSubmit.textContent = 'Entrar';
        inputsExtra.forEach(input => input.removeAttribute('required'));
        if (formulario) formulario.action = '../controladores/login.php';
    }
}

function recalcularCarrito() {
    let filas = document.querySelectorAll("#cuerpo_carrito tr");
    let totalGeneral = 0;
    filas.forEach(fila => {
        let id = fila.id.split('_')[1];
        let precioElem = document.getElementById('precio_' + id);
        if(!precioElem) return; 
        
        let precio = parseFloat(precioElem.innerText);
        let cantidad = parseInt(document.getElementById('cant_' + id).value);
        let subtotal = precio * cantidad;
        
        document.getElementById('sub_' + id).innerText = subtotal.toFixed(2);
        totalGeneral += subtotal;
    });
    let totalElem = document.getElementById('total_carrito');
    if(totalElem) totalElem.innerText = totalGeneral.toFixed(2);
}

function eliminarFilaCarrito(idFila) {
    let fila = document.getElementById(idFila);
    if(fila) {
        fila.parentNode.removeChild(fila);
        recalcularCarrito();
    }
    if (document.querySelectorAll("#cuerpo_carrito tr").length === 0) {
        document.getElementById('cuerpo_carrito').innerHTML = "<tr><td colspan='5' class='text-center text-muted'>Tu carrito está vacío.</td></tr>";
        document.getElementById('total_carrito').innerText = "0.00";
    }
}

// --- Lógica para Cancelar un Pedido (Cliente) vía API REST ---
function cancelarPedido(idFila) {
    Swal.fire({
        title: '¿Cancelar envío?',
        text: "Se liberará el stock al momento. Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar pedido',
        cancelButtonText: 'Volver'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

            fetch('../controladores/cancelarPedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id_pedido: idFila,
                    csrf_token: csrfToken
                })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || "Error al conectar con el servidor");
                return data;
            })
            .then(data => {
                if (data.status === "success") {
                    let boton = document.querySelector(`#collapse${idFila} button[type="submit"]`);
                    let badge = document.querySelector(`#heading${idFila} .badge`);
                    
                    if (badge) {
                        badge.className = "badge rounded-pill bg-danger me-3";
                        badge.innerText = "Cancelado";
                    }
                    if (boton) {
                        boton.disabled = true;
                        boton.innerText = "Anulado";
                        boton.classList.replace('btn-outline-danger', 'btn-secondary');
                    }
                    Swal.fire('Cancelado', data.message, 'success');
                }
            })
            .catch(error => {
                Swal.fire('Error', error.message, 'error');
            });
        }
    });
}

function validarModificacion() {
    var inputPass1 = document.getElementById("mod_pass1");
    var inputPass2 = document.getElementById("mod_pass2");
    if (inputPass1.value !== "" || inputPass2.value !== "") {
        if (inputPass1.value !== inputPass2.value) {
            document.getElementById("errorModPass").classList.remove("d-none");
            inputPass1.value = "";
            inputPass2.value = "";
            inputPass1.focus();
            return false; 
        }
    }
    document.getElementById("errorModPass").classList.add("d-none");
    return true; 
}

function limpiarCarritoLocal(event) {
    if (event) event.preventDefault();
    localStorage.removeItem("mi-carrito");
    sessionStorage.clear();
    window.location.href = '../controladores/logout.php';
}

function verificarPasswords() {
    var inputPass1 = document.getElementById("pass1");
    var inputPass2 = document.getElementById("pass2");
    if (inputPass1 && inputPass2) {
        if (inputPass1.value !== inputPass2.value) {
            document.getElementById("errorPass").style.display = "block";
            return false; 
        }
    }
    return true; 
}

function alternarCamposTarjeta() {
    const select = document.getElementById('tarjetaGuardada');
    const seccion = document.getElementById('seccionNuevaTarjeta');
    const inputNum = document.getElementById('numeroTarjeta');
    const inputTit = document.getElementById('titularTarjeta');
    const inputCad = document.getElementById('caducidadTarjeta');

    if (!seccion) return;

    if (!select || select.value === "NUEVA") {
        seccion.style.display = "block";
        inputNum.value = "";
        inputTit.value = "";
        inputCad.value = "";
        const inputs = seccion.querySelectorAll('input');
        inputs.forEach(i => { if(i.type !== 'checkbox') i.required = true; });
    } else {
        const opcionElegida = select.options[select.selectedIndex];
        inputNum.value = opcionElegida.getAttribute('data-numero');
        inputTit.value = opcionElegida.getAttribute('data-titular');
        inputCad.value = opcionElegida.getAttribute('data-caducidad');
        seccion.style.display = "none";
        const inputs = seccion.querySelectorAll('input');
        inputs.forEach(i => i.required = false);
    }
}

function activarAutocompletado(idInput, idLista, idLat, idLon) {
    const inputElement = document.getElementById(idInput);
    const listaElement = document.getElementById(idLista);
    let temporizador;

    if (!inputElement) return;

    inputElement.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length < 3) {
            listaElement.style.display = 'none';
            return;
        }

        clearTimeout(temporizador);
        temporizador = setTimeout(() => {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5&countrycodes=es`;
            fetch(url, { headers: { "Accept-Language": "es" } })
                .then(response => response.json())
                .then(data => {
                    listaElement.innerHTML = '';
                    if (data && data.length > 0) {
                        listaElement.style.display = 'block';
                        data.forEach(place => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action cursor-pointer';
                            li.innerHTML = `<strong>${place.display_name.split(',')[0]}</strong> <small class="text-muted d-block">${place.display_name}</small>`;
                            
                            li.onclick = function() {
                                inputElement.value = place.display_name.split(',')[0];
                                document.getElementById(idLat).value = place.lat;
                                document.getElementById(idLon).value = place.lon;
                                listaElement.style.display = 'none';
                            };
                            listaElement.appendChild(li);
                        });
                    }
                })
                .catch(err => console.error("Fallo OSM:", err));
        }, 500);
    });

    document.addEventListener('click', function(e) {
        if (!inputElement.contains(e.target) && !listaElement.contains(e.target)) {
            listaElement.style.display = 'none';
        }
    });
}

function activarAutocompletadoUnico(idInput, idLista) {
    const inputDireccion = document.getElementById(idInput);
    const listaSugerencias = document.getElementById(idLista);
    let temporizador;

    if (inputDireccion) {
        inputDireccion.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 3) {
                listaSugerencias.style.display = 'none';
                return;
            }

            clearTimeout(temporizador);
            temporizador = setTimeout(() => {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5&countrycodes=es`;
                fetch(url, { headers: { "Accept-Language": "es" } })
                    .then(response => response.json())
                    .then(data => {
                        listaSugerencias.innerHTML = '';
                        if (data && data.length > 0) {
                            listaSugerencias.style.display = 'block';
                            data.forEach(place => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.style.cursor = 'pointer';
                                li.innerHTML = `<strong>${place.display_name.split(',')[0]}</strong> <small class="text-muted d-block">${place.display_name}</small>`;
                                
                                li.onclick = function() {
                                    inputDireccion.value = place.display_name.split(',')[0];
                                    const addr = place.address;
                                    const ciudad = addr.city || addr.town || addr.village || addr.municipality || "";
                                    const codigoPostal = addr.postcode || ""; 
                                    if(document.getElementById('input_poblacion')) document.getElementById('input_poblacion').value = ciudad;
                                    if(document.getElementById('input_cp')) document.getElementById('input_cp').value = codigoPostal;
                                    if(document.getElementById('lat_input')) document.getElementById('lat_input').value = place.lat;
                                    if(document.getElementById('lon_input')) document.getElementById('lon_input').value = place.lon;
                                    listaSugerencias.style.display = 'none';
                                };
                                listaSugerencias.appendChild(li);
                            });
                        }
                    })
                    .catch(err => console.error("Fallo en red OSM:", err));
            }, 500);
        });
    }
}

function imprimirAlbaran(idPedido, cliente) {
    // Redirigir al controlador que genera el PDF oficial
    window.open('../controladores/descargarAlbaran.php?id=' + idPedido, '_blank');
}

function procesarEstadoReparto(idPedido, nuevoEstado, cardElement, motivo = '', firma='') {
    const payload = `idPedido=${idPedido}&estado=${nuevoEstado}&motivo=${encodeURIComponent(motivo)}&firma=${encodeURIComponent(firma)}`;

    fetch('../controladores/actualizarEstadoReparto.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json' // Indicamos que esperamos JSON
        },
        body: payload
    })
    .then(async response => {
        // Parseamos la respuesta a JSON independientemente del código de estado
        const data = await response.json();
        
        // Si el código HTTP no es 2xx, lanzamos el error con el mensaje del backend
        if (!response.ok) {
            throw new Error(data.message || "Fallo de red o servidor");
        }
        return data; // Si todo va bien, pasamos los datos al siguiente then
    })
    .then(data => {
        // Ahora comprobamos nuestro estándar de status "success"
        if(data.status === "success") {
            cardElement.style.transition = "opacity 0.5s, transform 0.5s";
            cardElement.style.opacity = "0";
            cardElement.style.transform = "translateX(100%)";
            setTimeout(() => cardElement.remove(), 500);
            console.log(data.message); // Opcional: mostrar en consola el mensaje del server
        }
    })
    .catch(error => {
        console.warn("Problema detectado o sin conexión: ", error.message);
        
        // Lógica de PWA Offline (IndexedDB)
        guardarEnIndexedDB(payload);
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then(function(swRegistration) {
                return swRegistration.sync.register('sync-entregas');
            });
        }
        cardElement.style.opacity = "0.5";
        alert(`La entrega se ha guardado en local y se sincronizará luego.\nMotivo: ${error.message}`);
    });
}

function animarCocheEnRuta(coordenadas, mapaInstance) {
    const dyInicial = coordenadas[1].lat - coordenadas[0].lat;
    const dxInicial = coordenadas[1].lng - coordenadas[0].lng;
    let anguloAnterior = (Math.atan2(dxInicial, dyInicial) * (180 / Math.PI)) - 90;
    
    const iconoCocheRealista = L.divIcon({
        html: `<div id="coche-animado" style="font-size: 24px; transition: transform 0.1s linear; transform: rotate(${anguloAnterior}deg);">🚗</div>`,
        className: 'icono-coche-custom',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    // Nota: aquí asumimos que cocheMarker está definido globalmente arriba
    window.cocheMarker = L.marker([coordenadas[0].lat, coordenadas[0].lng], {icon: iconoCocheRealista}).addTo(mapaInstance);
    
    let i = 0;
    function moverCoche() {
        if (i < coordenadas.length - 1) {
            const puntoActual = coordenadas[i];
            const puntoSiguiente = coordenadas[i + 1];

            window.cocheMarker.setLatLng([puntoSiguiente.lat, puntoSiguiente.lng]);

            const dy = puntoSiguiente.lat - puntoActual.lat;
            const dx = puntoSiguiente.lng - puntoActual.lng;
            
            if (Math.abs(dx) > 0.00005 || Math.abs(dy) > 0.00005) {
                let anguloNuevo = (Math.atan2(dx, dy) * (180 / Math.PI)) - 90; 
                let diferencia = anguloNuevo - anguloAnterior;
                if (diferencia > 180) diferencia -= 360;
                else if (diferencia < -180) diferencia += 360;
                
                let anguloFinal = anguloAnterior + diferencia;
                anguloAnterior = anguloFinal; 

                const cocheDOM = document.getElementById('coche-animado');
                if (cocheDOM) cocheDOM.style.transform = `rotate(${anguloFinal}deg)`;
            }

            const latLngActual = L.latLng(puntoActual.lat, puntoActual.lng);
            const latLngSiguiente = L.latLng(puntoSiguiente.lat, puntoSiguiente.lng);
            const distanciaMetros = latLngActual.distanceTo(latLngSiguiente);
            
            let tiempoEspera = distanciaMetros * 15; 
            if (tiempoEspera < 30) tiempoEspera = 30; 
            if (tiempoEspera > 800) tiempoEspera = 800;

            i++; 
            setTimeout(moverCoche, tiempoEspera);
        } else {
            window.cocheMarker.bindPopup("<b>📍 ¡Paquete entregado!</b><br>El repartidor ha llegado a su destino.").openPopup();
        }
    }
    setTimeout(moverCoche, 1500); 
}

/* ==========================================
   3. INDEXEDDB (OFFLINE)
   ========================================== */

function initDB() {
    return new Promise((resolve, reject) => {
        let request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = (e) => {
            let db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { autoIncrement: true });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function guardarEnIndexedDB(payload) {
    initDB().then(db => {
        let tx = db.transaction(STORE_NAME, 'readwrite');
        tx.objectStore(STORE_NAME).add(payload);
        console.log("Acción guardada en local (IndexedDB) para futura sincronización.");
    });
}

/* ==========================================
   4. REGISTRO DEL SERVICE WORKER (PWA)
   ========================================== */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js')
            .then(registration => {
                console.log('✅ ServiceWorker registrado con éxito con el scope: ', registration.scope);
            })
            .catch(err => {
                console.log('❌ El registro del ServiceWorker ha fallado: ', err);
            });
    });
}

// --- AUTO CIERRE DE SESIÓN POR INACTIVIDAD ---
function controlInactividad() {
    let tiempo;
    const tiempoLimite = 15 * 60 * 1000; // 15 minutos en milisegundos

    // Eventos que indican que el usuario sigue activo
    window.onload = resetearTiempo;
    document.onmousemove = resetearTiempo;
    document.onkeypress = resetearTiempo;
    document.ontouchstart = resetearTiempo; // Importante para la vista del repartidor
    document.onclick = resetearTiempo;

    function expirarSesion() {
        alert("⏱️ Tu sesión ha expirado por inactividad por motivos de seguridad.");
        // Reutilizamos tu controlador de logout existente
        window.location.href = '../controladores/logout.php';
    }

    function resetearTiempo() {
        clearTimeout(tiempo);
        tiempo = setTimeout(expirarSesion, tiempoLimite);
    }
}

// Iniciar el control solo si hay indicios de estar logueado 
// (Por ejemplo, si el botón de "Cerrar Sesión" existe en el DOM)
document.addEventListener('DOMContentLoaded', () => {
    // Si la URL actual no es el login, activamos el temporizador
    if (!window.location.href.includes("login")) {
        controlInactividad();
    }
});