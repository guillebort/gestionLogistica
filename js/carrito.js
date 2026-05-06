// Variable global
let carrito = [];

// Cargar al iniciar
function cargarCarrito() {
    let guardado = localStorage.getItem("mi-carrito");
    carrito = guardado ? JSON.parse(guardado) : [];
}

function guardarYRenderizar() {
    localStorage.setItem("mi-carrito", JSON.stringify(carrito));
    if (document.getElementById("cuerpo-tabla")) {
        renderizarCarrito();
    }
}

// FUNCIÓN PARA AÑADIR (Mejorada)
function anadirCarrito(codigo, descripcion, precio, existencias) {
    cargarCarrito();
    
    let item = carrito.find(p => p.codigo == codigo);
    
    if (item) {
        if (item.cantidad < existencias) {
            item.cantidad++;
            alert("✅ +1 unidad de " + descripcion);
        } else {
            alert("⚠️ Stock máximo alcanzado");
        }
    } else {
        if (existencias > 0) {
            carrito.push({
                codigo: codigo,
                descripcion: descripcion,
                precio: precio,
                existencias: existencias,
                cantidad: 1
            });
            alert("🛒 Añadido: " + descripcion);
        }
    }
    localStorage.setItem("mi-carrito", JSON.stringify(carrito));
    // IMPORTANTE: No hacemos reload ni nada para que no desaparezcan los productos de la vista
}

// FUNCIÓN PARA PINTAR LA TABLA (Con botones +/- y eliminar)
function renderizarCarrito() {
    cargarCarrito();
    let cuerpo = document.getElementById("cuerpo-tabla");
    let totalTxt = document.getElementById("total-pedido");
    let total = 0;

    if (!cuerpo) return; // Si no estamos en la página del carrito, no hacemos nada

    if (carrito.length === 0) {
        document.getElementById("tabla-contenedor").classList.add("d-none");
        document.getElementById("carrito-vacio").classList.remove("d-none");
        return;
    }

    document.getElementById("tabla-contenedor").classList.remove("d-none");
    document.getElementById("carrito-vacio").classList.add("d-none");
    
    cuerpo.innerHTML = "";

    carrito.forEach((prod, index) => {
        let subtotal = prod.precio * prod.cantidad;
        total += subtotal;

        cuerpo.innerHTML += `
            <tr>
                <td>
                    <div class="fw-bold">${prod.descripcion}</div>
                    <small class="text-muted">Ref: ${prod.codigo}</small>
                </td>
                <td class="text-center" style="width: 150px;">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, -1)">-</button>
                        <input type="text" class="form-control text-center" value="${prod.cantidad}" readonly>
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end">${prod.precio.toFixed(2)}€</td>
                <td class="text-end fw-bold">${subtotal.toFixed(2)}€</td>
                <td class="text-center">
                    <button class="btn btn-danger btn-sm" onclick="eliminarItem(${index})">
                        Eliminar 🗑️
                    </button>
                </td>
            </tr>
        `;
    });

    totalTxt.innerText = total.toFixed(2) + "€";
}

function cambiarCantidad(index, delta) {
    let prod = carrito[index];
    let nuevaCant = prod.cantidad + delta;

    if (nuevaCant > 0 && nuevaCant <= prod.existencias) {
        prod.cantidad = nuevaCant;
    } else if (nuevaCant > prod.existencias) {
        alert("⚠️ No hay más stock disponible");
    }
    guardarYRenderizar();
}

function eliminarItem(index) {
    if(confirm("¿Eliminar este servicio?")) {
        carrito.splice(index, 1);
        guardarYRenderizar();
    }
}

function vaciarCarrito() {
    if(confirm("¿Vaciar toda la cesta?")) {
        carrito = [];
        guardarYRenderizar();
    }
}