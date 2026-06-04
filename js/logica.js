/**
 * Archivo Principal de Lógica Frontend
 */

const DB_NAME = 'LogisTFG_Offline';
const STORE_NAME = 'entregas_pendientes';

let map = null;
let controlRuta = null;
let cocheMarker = null;

/*EVENTOS (Esperamos a que cargue el DOM)*/
document.addEventListener('DOMContentLoaded', () => {

    // LÓGICA DE USUARIO
    const radioAcceso = document.getElementById('radioAcceso');
    const radioRegistro = document.getElementById('radioRegistro');
    if (radioAcceso && radioRegistro) {
        radioAcceso.addEventListener('change', cambiarModoUsuario);
        radioRegistro.addEventListener('change', cambiarModoUsuario);
    }

    // LÓGICA MI CUENTa
    const botonesCancelar = document.querySelectorAll('.btn-cancelar-pedido');
    botonesCancelar.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const pedidoId = e.target.getAttribute('data-pedido');
            cancelarPedido(pedidoId);
        });
    });

    //  LÓGICA CHECKOUT
    const formCheckout = document.getElementById('formCheckout');
    if (formCheckout) {
        formCheckout.addEventListener('submit', () => {
            alert('¡Pago procesado con éxito! Serás redirigido a tu panel de usuario.');
        });
    }

    // LÓGICA PARA LA PASARELA DE PAGO 
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

    //AUTOCOMPLETADO DE DIRECCIONES (Nominatim OSM) 
    activarAutocompletado('input_origen', 'lista_origen', 'lat_origen', 'lon_origen');
    activarAutocompletado('input_destino', 'lista_destino', 'lat_destino', 'lon_destino');
    activarAutocompletadoUnico('input_direccion', 'lista_sugerencias');

    // LÓGICA DEL MAPA DEL REPARTIDOR (Rutas individuales 2 Fases) 
    const mapaElem = document.getElementById('mapa-repartidor');
    
    // COORDENADAS FIJAS DE LA EMPRESA (Av. de la Universidad, Burjassot)
    const latEmpresa = 39.5126;
    const lonEmpresa = -0.4244;
    
    // El coche arranca físicamente aparcado en la central
    let ubicacionActualCoche = L.latLng(latEmpresa, lonEmpresa);

    if (mapaElem) {
        map = L.map('mapa-repartidor').setView([latEmpresa, lonEmpresa], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([latEmpresa, lonEmpresa]).addTo(map).bindPopup("<b>🏢 Sede Central (Burjassot)</b>").openPopup();
    }

    // CONECTAMOS LOS BOTONES DE LOS PEDIDOS (.btn-simular-ruta)
    const botonesRuta = document.querySelectorAll('.btn-simular-ruta');

    botonesRuta.forEach(boton => {
        boton.style.display = 'inline-block';

        boton.addEventListener('click', function() {
            if (!map) return;

            console.log("▶️ INICIANDO RUTA...");

            botonesRuta.forEach(b => b.disabled = true); // Bloquear botones temporalmente

            // Capturamos datos blindados
            const latOrigen = parseFloat(boton.getAttribute('data-lato'));
            const lonOrigen = parseFloat(boton.getAttribute('data-lono'));
            const latDestino = parseFloat(boton.getAttribute('data-latd'));
            const lonDestino = parseFloat(boton.getAttribute('data-lond'));
            const cliente = boton.getAttribute('data-cliente') || "el cliente";
            
            const tarjetaPedido = boton.closest('.parada-card');
            const idPedido = tarjetaPedido ? tarjetaPedido.id.replace('pedido-', '') : null;

            if (!idPedido) {
                alert("Error crítico: No se encontró el ID del pedido.");
                return;
            }

            const puntoRecogida = L.latLng(latOrigen, lonOrigen);
            const puntoEntrega = L.latLng(latDestino, lonDestino);

            if(mapaElem) mapaElem.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // --- FASE 1: EN RUTA HACIA LA RECOGIDA ---
            console.log("📍 FASE 1: Yendo a origen...");
            trazarTramo(
                ubicacionActualCoche, 
                puntoRecogida, 
                `🚚 Yendo a por el paquete de <b>${cliente}</b>...`, 
                `📦 Paquete recogido de <b>${cliente}</b>`, 
                () => {
                    console.log("✅ FASE 1 COMPLETADA. Paquete recogido.");
                    
                    // Actualizamos BD al estado 2 y enviamos el correo
                    if (typeof procesarEstadoReparto === 'function') {
                        console.log("📧 Disparando actualización de estado 2 y envío de email...");
                        procesarEstadoReparto(idPedido, 2, tarjetaPedido);
                    }

                    // Pausa automática estricta de 2.5 segundos
                    setTimeout(() => {
                        console.log("📍 FASE 2: Arrancando hacia destino...");
                        
                        // --- FASE 2: EN RUTA HACIA LA ENTREGA EN DESTINO ---
                        trazarTramo(
                            puntoRecogida, 
                            puntoEntrega, 
                            `🚚 Llevando el paquete a <b>${cliente}</b>...`, 
                            `✅ Llegada al destino de ${cliente}`, 
                            () => {
                                console.log("✅ FASE 2 COMPLETADA. Llegamos al destino.");
                                
                                ubicacionActualCoche = puntoEntrega;
                                
                                botonesRuta.forEach(b => {
                                    if (!b.classList.contains('btn-success')) b.disabled = false;
                                });

                                boton.classList.replace('btn-primary', 'btn-success');
                                boton.innerHTML = "✅ Envíos Completados";
                                boton.disabled = true;

                                if (typeof Swal !== 'undefined') {
                                    Swal.fire('Destino Alcanzado', 'Procede a la entrega y solicita la firma en la tarjeta.', 'success');
                                } else {
                                    alert("Destino alcanzado. Procede a pedir la firma.");
                                }
                            }
                        );
                    }, 2500); 
                }
            );
        });
    });
       
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

    /*LÓGICA DE VALIDACIÓN REGISTRO USUARIO*/
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

    /*LÓGICA DE SINCRONIZACIÓN DE TARJETAS*/
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

    /*11. INICIALIZACIÓN MAPA LOGÍSTICO (ADMIN)*/
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

    //  Lógica para Asignar Repartidor (Admin) vía API REST 
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
                    
                    // actualizar el contador de pendientes
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

    /* LÓGICA DE AUTENTICACIÓN Y REGISTRO AJAX (SPA UX)*/
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

    /* LÓGICA PARA GUARDAR RUTA (CHECKOUT)*/
    const formRuta = document.querySelector('form[action="../controladores/guardarRuta.php"]');
    
    if (formRuta) {
        formRuta.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitamos la recarga
            
            const btnSubmit = this.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            
            // Verificamos que las coordenadas no sean 0.0 (que hayan usado el autocompletado de OpenStreetMap)
            const latO = document.getElementById('lat_origen').value;
            const latD = document.getElementById('lat_destino').value;
            
            if (latO === "0.0" || latD === "0.0") {
                alert("⚠️ Por favor, selecciona una dirección válida de la lista de sugerencias para calcular las coordenadas GPS exactas.");
                return;
            }

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


/*FUNCIONES GLOBALES / AUXILIARES*/

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

function limpiarCarritoLocal(event) {
    if (event) event.preventDefault();
    localStorage.removeItem("mi-carrito");
    sessionStorage.clear();
    window.location.href = '../controladores/logout.php';
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
                                    // 1. Rellenar calle
                                    inputDireccion.value = place.display_name.split(',')[0];
                                    
                                    const addr = place.address;
                                    const ciudad = addr.city || addr.town || addr.village || addr.municipality || "";
                                    const provincia = addr.province || addr.state || addr.county || ""; // NUEVO
                                    const codigoPostal = addr.postcode || ""; 
                                    
                                    // 2. Rellenar campos extra si existen en el DOM
                                    if(document.getElementById('input_poblacion')) document.getElementById('input_poblacion').value = ciudad;
                                    if(document.getElementById('input_provincia')) document.getElementById('input_provincia').value = provincia; // NUEVO
                                    if(document.getElementById('input_cp')) document.getElementById('input_cp').value = codigoPostal;
                                    
                                    // 3. Rellenar coordenadas (si se necesitan)
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
            'Accept': 'application/json'
        },
        body: payload
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || "Fallo de red o servidor");
        return data;
    })
    .then(data => {
        if(data.status === "success") {
            // Si el estado es 3 (Entregado) o 4 (Incidencia), DESAPARECEMOS LA TARJETA
            if (nuevoEstado == 3 || nuevoEstado == 4) {
                cardElement.style.transition = "opacity 0.5s, transform 0.5s";
                cardElement.style.opacity = "0";
                cardElement.style.transform = "translateX(100%)";
                setTimeout(() => cardElement.remove(), 500);
            } 
            // Si el estado es 2 (Reenviamos el 2 para notificar al recoger), CAMBIAMOS LA CHAPA
            else if (nuevoEstado == 2) {
                let badge = cardElement.querySelector('.badge');
                if (badge) {
                    badge.className = "badge bg-info text-dark fw-bold rounded-pill px-3";
                    badge.innerText = "Yendo al Destino";
                }
            }
        }
    })
    .catch(error => {
        guardarEnIndexedDB(payload);
    });
}

// --- FUNCIONES LOGÍSTICAS DE ENRUTAMIENTO Y ANIMACIÓN ---

function trazarTramo(origen, destino, msjSalida, msjLlegada, callbackFinal) {
    if (controlRuta !== null) {
        map.removeControl(controlRuta);
        controlRuta = null;
    }
    if (cocheMarker !== null) {
        map.removeLayer(cocheMarker);
        cocheMarker = null;
    }

    controlRuta = L.Routing.control({
        waypoints: [origen, destino],
        router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
        lineOptions: { styles: [{ color: '#0d6efd', opacity: 0.8, weight: 6 }] },
        createMarker: function() { return null; },
        fitSelectedRoutes: true,
        show: false
    }).addTo(map);

    // Bandera de seguridad para evitar múltiples ejecuciones
    let animacionLanzada = false;

    controlRuta.on('routesfound', function(e) {
        if (!animacionLanzada) {
            animacionLanzada = true;
            animarCocheEnRuta(e.routes[0].coordinates, map, msjSalida, msjLlegada, callbackFinal);
        }
    });

    controlRuta.on('routingerror', function(e) {
        console.error("Error en OSRM (API Rutas): ", e);
        alert("La API de rutas ha fallado. Revisa tu conexión a internet.");
    });
}

function animarCocheEnRuta(coordenadas, mapaInstance, msjSalida, msjLlegada, callbackFinal) {
    if (!coordenadas || coordenadas.length === 0) return;

    const dyInicial = coordenadas[1] ? coordenadas[1].lat - coordenadas[0].lat : 0;
    const dxInicial = coordenadas[1] ? coordenadas[1].lng - coordenadas[0].lng : 0;
    let anguloAnterior = (Math.atan2(dxInicial, dyInicial) * (180 / Math.PI)) - 90;
    
    const iconoCocheRealista = L.divIcon({
        html: `<div id="coche-animado" style="font-size: 26px; transition: transform 0.1s linear; transform: rotate(${anguloAnterior}deg);">🚗</div>`,
        className: 'icono-coche-custom',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    cocheMarker = L.marker([coordenadas[0].lat, coordenadas[0].lng], {icon: iconoCocheRealista}).addTo(mapaInstance);
    cocheMarker.bindPopup(msjSalida).openPopup();
    
    let i = 0;
    function moverCoche() {
        if (i < coordenadas.length - 1) {
            const puntoActual = coordenadas[i];
            const puntoSiguiente = coordenadas[i + 1];

            if (cocheMarker) cocheMarker.setLatLng([puntoSiguiente.lat, puntoSiguiente.lng]);

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
            
            // Velocidad rápida
            let tiempoEspera = distanciaMetros * 3; 
            if (tiempoEspera < 10) tiempoEspera = 10; 
            if (tiempoEspera > 200) tiempoEspera = 200; 

            i++; 
            setTimeout(moverCoche, tiempoEspera);
        } else {
            if (cocheMarker) cocheMarker.bindPopup(msjLlegada).openPopup();
            
            // Llamamos a la siguiente fase
            if(callbackFinal) {
                callbackFinal();
            }
        }
    }
    
    setTimeout(moverCoche, 2000); 
}
/*INDEXEDDB (OFFLINE)*/

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

/* REGISTRO DEL SERVICE WORKER (PWA)*/
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

// AUTO CIERRE DE SESIÓN POR INACTIVIDAD
function controlInactividad() {
    let tiempo;
    const tiempoLimite = 5 * 60 * 1000; // 5 minutos en milisegundos

    // Eventos que indican que el usuario sigue activo
    window.onload = resetearTiempo;
    document.onmousemove = resetearTiempo;
    document.onkeypress = resetearTiempo;
    document.ontouchstart = resetearTiempo;
    document.onclick = resetearTiempo;

    function expirarSesion() {
        alert("⏱️ Tu sesión ha expirado por inactividad por motivos de seguridad.");
        window.location.href = '../controladores/logout.php';
    }

    function resetearTiempo() {
        clearTimeout(tiempo);
        tiempo = setTimeout(expirarSesion, tiempoLimite);
    }
}

// Iniciar el control solo si hay indicios de estar logueado 
document.addEventListener('DOMContentLoaded', () => {
    // Si la URL actual no es el login, activamos el temporizador
    if (!window.location.href.includes("login")) {
        controlInactividad();
    }
});