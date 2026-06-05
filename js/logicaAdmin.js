// js/admin_logica.js
document.addEventListener('DOMContentLoaded', () => {

    // 1. CONTROL DE BORRADO DE PRODUCTOS/TARIFAS
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

    // 2. CONTROL DE BORRADO DE USUARIOS/PERSONAL
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

    // Aquí podrás ir añadiendo el JS de pedidos, gráficas, etc.
});