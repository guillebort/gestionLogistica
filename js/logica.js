// js/logica.js

// Esperamos a que toda la página HTML cargue antes de ejecutar nada
document.addEventListener('DOMContentLoaded', () => {

    // 1. LÓGICA DE USUARIO (usuario.html)
    const radioAcceso = document.getElementById('radioAcceso');
    const radioRegistro = document.getElementById('radioRegistro');
    if (radioAcceso && radioRegistro) {
        radioAcceso.addEventListener('change', cambiarModoUsuario);
        radioRegistro.addEventListener('change', cambiarModoUsuario);
    }

    // 2. LÓGICA DE PRODUCTOS (productos.html)
    const botonesAñadir = document.querySelectorAll('.btn-add-carrito');
    botonesAñadir.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const servicio = e.target.getAttribute('data-servicio');
            const precio = e.target.getAttribute('data-precio');
            alert(`¡Has añadido el servicio: ${servicio} al carrito por ${precio}€!`);
        });
    });

    // 3. LÓGICA DE CARRITO (carrito.html)
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

    // 4. LÓGICA MI CUENTA (miCuenta.html)
    const botonesCancelar = document.querySelectorAll('.btn-cancelar-pedido');
    botonesCancelar.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const pedidoId = e.target.getAttribute('data-pedido');
            cancelarPedido(pedidoId);
        });
    });

    // 5. LÓGICA CHECKOUT (checkout.html)
    const formCheckout = document.getElementById('formCheckout');
    if (formCheckout) {
        formCheckout.addEventListener('submit', () => {
            alert('¡Pago procesado con éxito! Serás redirigido a tu panel de usuario.');
        });
    }

    // 6. LÓGICA DEL REPARTIDOR (repartidor.html)
    const botonesEntregado = document.querySelectorAll('.btn-entregado');
    const botonesIncidencia = document.querySelectorAll('.btn-incidencia');
    let entregasCompletadas = 0;

    botonesEntregado.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const paradaId = e.target.getAttribute('data-parada');
            marcarParada(paradaId, 'entregado');
        });
    });

    botonesIncidencia.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const paradaId = e.target.getAttribute('data-parada');
            marcarParada(paradaId, 'incidencia');
        });
    });
});


/* ==========================================
   FUNCIONES AUXILIARES
   ========================================== */

function cambiarModoUsuario() {
    const radioRegistro = document.getElementById('radioRegistro');
    const camposRegistro = document.getElementById('campos_registro');
    const btnSubmit = document.getElementById('btn_submit');
    const inputsExtra = camposRegistro.querySelectorAll('input');
    
    // 🚀 INYECCIÓN 1: Atrapamos el formulario al que pertenece el botón
    const formulario = btnSubmit.closest('form');

    if (radioRegistro.checked) {
        camposRegistro.classList.remove('d-none');
        btnSubmit.textContent = 'Registrarse';
        inputsExtra.forEach(input => input.setAttribute('required', 'true'));
        
        // 🚀 INYECCIÓN 2: Desviamos los datos hacia el Servlet de Registro
        if (formulario) formulario.action = 'registro.html';
        
    } else {
        camposRegistro.classList.add('d-none');
        btnSubmit.textContent = 'Entrar';
        inputsExtra.forEach(input => input.removeAttribute('required'));
        
        // 🚀 INYECCIÓN 3: Desviamos los datos de vuelta al Servlet de Login
        if (formulario) formulario.action = 'login.html';
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
    
    // Si el carrito se vacía
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

function marcarParada(idParada, estado) {
    const parada = document.getElementById(idParada);
    if (!parada) return;

    if (estado === 'entregado') {
        parada.classList.remove('list-group-item-action');
        parada.classList.add('list-group-item-success', 'text-muted');
        parada.innerHTML = `<div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 fw-bold text-decoration-line-through">` + parada.querySelector('h5').innerText + `</h5>
                                <span class="badge bg-success align-self-start">✔️ Completado</span>
                            </div>`;
        
        // Actualizar contador
        let contadorElem = document.getElementById('contador-entregas');
        if (contadorElem) {
            let textoActual = contadorElem.innerText;
            let entregados = parseInt(textoActual.split(' / ')[0]) + 1;
            let total = textoActual.split(' / ')[1];
            contadorElem.innerText = `${entregados} / ${total}`;
        }
    } else if (estado === 'incidencia') {
        let motivo = prompt("Describe el motivo de la incidencia (ej. Ausente, Dirección incorrecta):");
        if (motivo) {
            parada.classList.add('list-group-item-danger');
            parada.querySelector('.badge').className = "badge bg-danger text-white align-self-start";
            parada.querySelector('.badge').innerText = "❌ Incidencia";
            alert("Incidencia reportada a la central: " + motivo);
        }
    }
}

// Validar que las contraseñas de modificación coinciden
function validarModificacion() {
    var inputPass1 = document.getElementById("mod_pass1");
    var inputPass2 = document.getElementById("mod_pass2");
    
    var p1 = inputPass1.value;
    var p2 = inputPass2.value;
    
    if (p1 !== "" || p2 !== "") {
        if (p1 !== p2) {
            document.getElementById("errorModPass").classList.remove("d-none");
            inputPass1.value = "";
            inputPass2.value = "";

            inputPass1.focus();
            
            return false; // Detiene el envío
        }
    }
    document.getElementById("errorModPass").classList.add("d-none");
    return true; // Permite el envío
}

function limpiarCarritoLocal() {
        event.preventDefault();

        try {
            // 2. Bomba nuclear: borramos TODO lo que haya guardado tu JS
            localStorage.clear(); 
            sessionStorage.clear();
            console.log("Memoria del navegador fulminada.");
        } catch (error) {
            console.error("Fallo al borrar: ", error);
        }

        // 3. Una vez limpio, le damos permiso para viajar al Servlet de Java
        window.location.href = 'logout.html';
}

// ==============================================================
// 🛒 LÓGICA PARA LA PASARELA DE PAGO (Tarjetas y Autocompletado)
// ==============================================================

// 1. Función para mostrar/ocultar y AUTOCOMPLETAR las tarjetas
function alternarCamposTarjeta() {
    const select = document.getElementById('tarjetaGuardada');
    const seccion = document.getElementById('seccionNuevaTarjeta');
    
    // Inputs del formulario
    const inputNum = document.getElementById('numeroTarjeta');
    const inputTit = document.getElementById('titularTarjeta');
    const inputCad = document.getElementById('caducidadTarjeta');

    // Si no estamos en la página de pago, cortamos la ejecución aquí
    if (!seccion) return;

    if (!select || select.value === "NUEVA") {
        // MODO MANUAL: Mostrar formulario y limpiar campos
        seccion.style.display = "block";
        inputNum.value = "";
        inputTit.value = "";
        inputCad.value = "";
        
        // Volvemos a hacer obligatorios los campos
        const inputs = seccion.querySelectorAll('input');
        inputs.forEach(i => { if(i.type !== 'checkbox') i.required = true; });
        
    } else {
        // MODO AUTOMÁTICO: Leer los atributos 'data-' ocultos en el HTML
        const opcionElegida = select.options[select.selectedIndex];
        
        inputNum.value = opcionElegida.getAttribute('data-numero');
        inputTit.value = opcionElegida.getAttribute('data-titular');
        inputCad.value = opcionElegida.getAttribute('data-caducidad');
        
        // Ocultar formulario visualmente (los datos se enviarán igual)
        seccion.style.display = "none";
        
        // Quitamos la obligatoriedad porque ya están rellenos (aunque no se vean)
        const inputs = seccion.querySelectorAll('input');
        inputs.forEach(i => i.required = false);
    }
}


// ==============================================================
// 🚀 INICIALIZACIÓN DE EVENTOS (Cuando la página termina de cargar)
// ==============================================================
document.addEventListener("DOMContentLoaded", function() {

    // --- A. GESTIÓN DEL DESPLEGABLE DE TARJETAS ---
    const selectTarjeta = document.getElementById('tarjetaGuardada');
    const seccionNueva = document.getElementById('seccionNuevaTarjeta');

    // Escuchar cuando el usuario cambie de opción en el desplegable
    if (selectTarjeta) {
        selectTarjeta.addEventListener('change', alternarCamposTarjeta);
    }

    // Comprobar el estado inicial al entrar a la página
    if (seccionNueva) {
        alternarCamposTarjeta();
    }


    // --- B. MAGIA 1: FORMATEO DEL NÚMERO DE TARJETA ---
    const inputTarjeta = document.getElementById('numeroTarjeta');
    if (inputTarjeta) {
        inputTarjeta.addEventListener('input', function (e) {
            // 1. Borramos todo lo que no sean números
            let input = e.target.value.replace(/\D/g, ''); 
            
            // 2. Lo cortamos a 16 números como máximo
            if (input.length > 16) {
                input = input.substring(0, 16);
            }
            
            // 3. Le añadimos un espacio cada 4 números
            let formateado = input.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = formateado;
        });
    }


    // --- C. MAGIA 2: FORMATEO DE LA CADUCIDAD (MM/AAAA) ---
    const inputCaducidad = document.getElementById('caducidadTarjeta');
    if (inputCaducidad) {
        inputCaducidad.addEventListener('input', function (e) {
            // 1. Borramos todo lo que no sean números
            let input = e.target.value.replace(/\D/g, ''); 
            
            // 2. Lo cortamos a 6 números máximo (2 mes, 4 año)
            if (input.length > 6) {
                input = input.substring(0, 6);
            }
            
            // 3. Si ya han escrito el mes, le colamos la barra '/'
            if (input.length > 2) {
                e.target.value = input.substring(0, 2) + '/' + input.substring(2);
            } else {
                e.target.value = input;
            }
        });
    }

});
document.addEventListener("DOMContentLoaded", function() {
    const inputDireccion = document.getElementById('input_direccion');
    const listaSugerencias = document.getElementById('lista_sugerencias');
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
                // CAMBIO A NOMINATIM: Más estable y sin bloqueos raros
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
                                
                                // Nombre más descriptivo
                                li.innerHTML = `<strong>${place.display_name.split(',')[0]}</strong> <small class="text-muted d-block">${place.display_name}</small>`;
                                
                                li.onclick = function() {
                                    // 1. Ponemos el nombre de la calle
                                    inputDireccion.value = place.display_name.split(',')[0];
                                    
                                    // 2. Extraemos datos de la dirección de Nominatim
                                    const addr = place.address;
                                    const ciudad = addr.city || addr.town || addr.village || addr.municipality || "";
                                    const codigoPostal = addr.postcode || ""; // Aquí pillamos el CP

                                    // 3. Rellenamos los inputs del formulario
                                    if(document.getElementById('input_poblacion')) document.getElementById('input_poblacion').value = ciudad;
                                    if(document.getElementById('input_cp')) document.getElementById('input_cp').value = codigoPostal;
                                    
                                    // 4. Guardamos las coordenadas (Crucial para que Java no explote)
                                    if(document.getElementById('lat_input')) document.getElementById('lat_input').value = place.lat;
                                    if(document.getElementById('lon_input')) document.getElementById('lon_input').value = place.lon;
                                    
                                    console.log("Datos cargados: ", ciudad, codigoPostal, place.lat, place.lon);

                                    listaSugerencias.style.display = 'none';
};
                                listaSugerencias.appendChild(li);
                            });
                        }
                    })
                    .catch(err => console.error("Fallo en red OSM:", err));
            }, 500); // Un poco más de margen
        });
    }
});

document.addEventListener("DOMContentLoaded", function() {
    
    // Función maestra: Le pasas los IDs de los cajones y ella se encarga de todo
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

    // Activamos la magia para el Origen y para el Destino
    activarAutocompletado('input_origen', 'lista_origen', 'lat_origen', 'lon_origen');
    activarAutocompletado('input_destino', 'lista_destino', 'lat_destino', 'lon_destino');
});