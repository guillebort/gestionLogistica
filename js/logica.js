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
        
        // --- A. CALCULAR RUTA ÓPTIMA Y PREPARAR JORNADA ---
        btnOptimizar.addEventListener('click', () => {
            if (!map) return;
            const paradas = document.querySelectorAll('.parada-card');
            if (paradas.length === 0) {
                alert("No hay entregas pendientes para organizar.");
                return;
            }

            // 1. Obtener punto de origen (central logística) de la primera tarjeta
            const latOrigen = parseFloat(paradas[0].querySelector('.btn-simular-ruta').getAttribute('data-lato'));
            const lonOrigen = parseFloat(paradas[0].querySelector('.btn-simular-ruta').getAttribute('data-lono'));
            let posicionActual = L.latLng(latOrigen, lonOrigen);

            rutaOptimaOrdenada = [{
                latLng: posicionActual,
                idPedido: 'central'
            }];

            // 2. Extraer todos los destinos
            let pendientes = [];
            paradas.forEach(card => {
                const btn = card.querySelector('.btn-simular-ruta');
                pendientes.push({
                    idPedido: card.id,
                    cardElement: card,
                    lat: parseFloat(btn.getAttribute('data-latd')),
                    lng: parseFloat(btn.getAttribute('data-lond'))
                });
            });

            // 3. Algoritmo: Vecino Más Cercano
            while (pendientes.length > 0) {
                let indiceMasCercano = -1;
                let distanciaMin = Infinity;
                
                for (let i = 0; i < pendientes.length; i++) {
                    let destinoEval = L.latLng(pendientes[i].lat, pendientes[i].lng);
                    let dist = posicionActual.distanceTo(destinoEval); 
                    if (dist < distanciaMin) {
                        distanciaMin = dist;
                        indiceMasCercano = i;
                    }
                }
                
                posicionActual = L.latLng(pendientes[indiceMasCercano].lat, pendientes[indiceMasCercano].lng);
                rutaOptimaOrdenada.push({
                    latLng: posicionActual,
                    idPedido: pendientes[indiceMasCercano].idPedido,
                    cardElement: pendientes[indiceMasCercano].cardElement
                });
                
                pendientes.splice(indiceMasCercano, 1); 
            }

            // 4. Dibujar la ruta panorámica inicial (Overview)
            if (controlRuta != null) map.removeControl(controlRuta);
            if (cocheMarker != null) map.removeLayer(cocheMarker);

            controlRuta = L.Routing.control({
                waypoints: rutaOptimaOrdenada.map(p => p.latLng),
                routeWhileDragging: false,
                addWaypoints: false,
                fitSelectedRoutes: true,
                lineOptions: { styles: [{color: '#6c757d', opacity: 0.5, weight: 4}] }, // Gris tenue para ver el panorama
                createMarker: function(i, wp) {
                    if (i === 0) return L.marker(wp.latLng).bindPopup("<b>🟢 Central Logística</b>");
                    return L.marker(wp.latLng).bindPopup(`<b>📍 Parada ${i}</b>`);
                }
            }).addTo(map);

            window.scrollTo({ top: 0, behavior: 'smooth' });

            // 5. Reorganizar tarjetas en el DOM en el orden óptimo calculado
            const contenedorTarjetas = document.getElementById('lista-paradas');
            rutaOptimaOrdenada.forEach(paso => {
                if (paso.idPedido !== 'central') {
                    contenedorTarjetas.appendChild(paso.cardElement);
                }
            });

            // 6. Cambiar la interfaz de botones
            btnOptimizar.classList.add('d-none');
            btnSiguiente.classList.remove('d-none');
            pasoRutaActual = 0; 
            
            alert(`¡Ruta óptima calculada!\nTienes ${rutaOptimaOrdenada.length - 1} paradas. Pulsa 'Iniciar Navegación' para arrancar hacia el primer destino.`);
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

    // Gestión de entregas e incidencias
    const botonesEntregado = document.querySelectorAll('.btn-entregado');
    const botonesIncidencia = document.querySelectorAll('.btn-incidencia');

    botonesEntregado.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if(confirm("¿Confirmar entrega exitosa?")) {
                const pedidoId = e.target.getAttribute('data-pedido');
                procesarEstadoReparto(pedidoId, 3, e.target.closest('.card')); 
            }
        });
    });

    botonesIncidencia.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const motivo = prompt("Describe la incidencia (Ej: Ausente, Dirección incorrecta):");
            if (motivo) {
                const pedidoId = e.target.getAttribute('data-pedido');
                procesarEstadoReparto(pedidoId, 4, e.target.closest('.card'), motivo); 
            }
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

function cancelarPedido(idFila) {
    if(confirm("¿Estás seguro de que deseas cancelar este pedido?")) {
        let fila = document.getElementById(idFila);
        if(fila) {
            fila.querySelector('.badge').className = "badge bg-danger";
            fila.querySelector('.badge').innerText = "Cancelado";
            fila.querySelector('.btn-cancelar-pedido').disabled = true;
            fila.querySelector('.btn-cancelar-pedido').innerText = "Anulado";
            alert("Pedido cancelado correctamente.");
        }
    }
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
    let ventana = window.open('', 'PRINT', 'height=600,width=800');
    ventana.document.write('<!DOCTYPE html><html lang="es"><head><title>Albarán Pedido #' + idPedido + '</title>');
    ventana.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    ventana.document.write('</head><body style="padding: 40px;">');
    ventana.document.write('<div class="d-flex justify-content-between align-items-center mb-4">');
    ventana.document.write('<h1>📦 Albarán de Entrega</h1>');
    ventana.document.write('<h3>LogisTFG</h3>');
    ventana.document.write('</div><hr>');
    ventana.document.write('<p><strong>Pedido ID:</strong> #' + idPedido + '</p>');
    ventana.document.write('<p><strong>Cliente Receptor:</strong> ' + cliente + '</p>');
    ventana.document.write('<p><strong>Fecha de Emisión:</strong> ' + new Date().toLocaleDateString() + '</p>');
    ventana.document.write('<br><div class="alert alert-secondary border-dark text-dark">Documento de control logístico interno. El receptor acredita que el bulto ha llegado en perfectas condiciones.</div>');
    ventana.document.write('<br><br><br><br><p class="text-center"><strong>Firma del Cliente o Sello:</strong> <br><br><br>_________________________</p>');
    ventana.document.write('</body></html>');
    ventana.document.close(); 
    ventana.focus(); 
    setTimeout(function() {
        ventana.print();
        ventana.close();
    }, 500); 
}

function procesarEstadoReparto(idPedido, nuevoEstado, cardElement, motivo = '') {
    const payload = `idPedido=${idPedido}&estado=${nuevoEstado}&motivo=${encodeURIComponent(motivo)}`;

    fetch('../controladores/actualizarEstadoReparto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
    })
    .then(response => {
        if (!response.ok) throw new Error("Fallo de red");
        return response.text();
    })
    .then(data => {
        if(data.trim() === "OK") {
            cardElement.style.transition = "opacity 0.5s, transform 0.5s";
            cardElement.style.opacity = "0";
            cardElement.style.transform = "translateX(100%)";
            setTimeout(() => cardElement.remove(), 500);
        } else {
            alert("Error al actualizar la base de datos MySQL.");
        }
    })
    .catch(error => {
        console.warn("Sin conexión. Guardando entrega en modo offline.");
        guardarEnIndexedDB(payload);
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then(function(swRegistration) {
                return swRegistration.sync.register('sync-entregas');
            });
        }
        cardElement.style.opacity = "0.5";
        alert("Sin conexión. La entrega se sincronizará automáticamente al recuperar la señal.");
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