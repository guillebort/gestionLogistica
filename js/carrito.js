let carrito = [];

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

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

function anadirCarrito(codigo, descripcion, precio, existencias) {
    cargarCarrito();
    let item = carrito.find(p => p.codigo == codigo);
    
    if (item) {
        if (item.cantidad < existencias) {
            item.cantidad++;
            Toast.fire({ icon: 'success', title: `+1 unidad de ${descripcion}` });
        } else {
            Toast.fire({ icon: 'warning', title: 'Stock máximo alcanzado' });
        }
    } else {
        if (existencias > 0) {
            carrito.push({ codigo, descripcion, precio, existencias, cantidad: 1 });
            Toast.fire({ icon: 'success', title: `🛒 Añadido: ${descripcion}` });
        }
    }
    localStorage.setItem("mi-carrito", JSON.stringify(carrito));
}

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
    Swal.fire({
        title: '¿Eliminar este servicio?',
        text: "Se quitará de tu cesta",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            carrito.splice(index, 1);
            guardarYRenderizar();
            Toast.fire({ icon: 'info', title: 'Servicio eliminado' });
        }
    });
}

function vaciarCarrito() {
    Swal.fire({
        title: '¿Vaciar toda la cesta?',
        text: "Perderás todos los servicios seleccionados",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, vaciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            carrito = [];
            guardarYRenderizar();
            Toast.fire({ icon: 'success', title: 'Cesta vaciada' });
        }
    });
}