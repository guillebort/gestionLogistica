document.addEventListener('DOMContentLoaded', () => {

    // 1. INICIALIZACIÓN DINÁMICA DEL MAPA LOGÍSTICO (LEAFLET)
    const mapaContenedor = document.getElementById('mapa-logistico');
    if (mapaContenedor) {
        const mapAdmin = L.map('mapa-logistico').setView([39.4699, -0.3762], 12); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap colaboradores'
        }).addTo(mapAdmin);

        const pedidosRaw = mapaContenedor.getAttribute('data-pedidos');
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

    // 2. CONTROLADOR DEL MODAL DE EDICIÓN DE PRODUCTOS (CRUD - UPDATE)
    const botonesEditar = document.querySelectorAll('.btn-editar-producto');
    if (botonesEditar.length > 0) {
        const modalElemento = document.getElementById('modalEditarProducto');
        const modalBootstrap = new bootstrap.Modal(modalElemento);

        botonesEditar.forEach(boton => {
            boton.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const precio = this.getAttribute('data-precio');
                
                let caracteristicas = this.getAttribute('data-caracteristicas');
                caracteristicas = caracteristicas.replace(/<\/?li>/g, '').replace(/^\s*[\r\n]/gm, '');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_nombre').value = nombre;
                document.getElementById('edit_precio').value = precio;
                document.getElementById('edit_caracteristicas').value = caracteristicas;

                modalBootstrap.show();
            });
        });
    }

    // 3. SEPARACIÓN JS DE IMPRESIÓN DE ALBARANES
    const botonesImprimir = document.querySelectorAll('.btn-imprimir-albaran');
    botonesImprimir.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const idPedido = this.getAttribute('data-id');
            if (idPedido) {
                window.open('../controladores/descargarAlbaran.php?id=' + idPedido, '_blank');
            }
        });
    });

    // 4. CONTROL DE BORRADO DE PRODUCTOS/TARIFAS
    const btnEliminarProducto = document.querySelectorAll('.btn-eliminar-producto');
    btnEliminarProducto.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            Swal.fire({
                title: '¿Retirar servicio?',
                text: "Desaparecerá del catálogo inmediatamente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, retirar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // 5. CONTROL DE BORRADO DE USUARIOS/PERSONAL
    const formEliminarUsuario = document.querySelectorAll('.form-eliminar-usuario');
    formEliminarUsuario.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Eliminar cuenta permanentemente?',
                text: "Si el usuario tiene pedidos registrados, el sistema bloqueará el borrado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    // Vinculamos la lógica de la API de OpenStreetMap al input del panel Admin
    if (typeof activarAutocompletadoUnico === 'function') {
        activarAutocompletadoUnico('input_direccion_admin', 'lista_sugerencias_admin');
    }

});