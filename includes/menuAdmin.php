<?php
// Comprobamos si la sesión ya está iniciada para no duplicar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Cogemos el nombre del admin de la sesión
$nombreAdmin = $_SESSION['nombreAdmin'] ?? 'Administrador';

// Obtenemos el nombre del archivo actual para marcar el enlace "Activo"
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* Efectos hover sutiles para los enlaces del menú admin */
    .nav-admin-link {
        color: rgba(255, 255, 255, 0.7) !important;
        transition: all 0.2s ease-in-out;
    }
    .nav-admin-link:hover, .nav-admin-link.active {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

<nav class="navbar navbar-expand-lg sticky-top" style="background-color: #0f172a; border-bottom: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);">
    <div class="container-fluid px-4 py-1">
        
        <!-- Logo Admin -->
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2 m-0" href="index.php">
            <span class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 1.1rem;">⚙️</span> 
            <span class="fs-5">LogisTFG</span> 
            <span class="badge bg-white bg-opacity-10 text-white-50 border border-secondary border-opacity-25 rounded-pill fw-medium ms-1" style="font-size: 0.7rem; letter-spacing: 1px;">ADMIN</span>
        </a>
        
        <!-- Menú Móvil -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
            <span class="navbar-toggler-icon" style="filter: invert(1) opacity(0.7);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navAdmin">
            <!-- Enlaces de navegación centrales -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ms-lg-5 text-center text-lg-start mt-3 mt-lg-0 fw-medium">
                <li class="nav-item">
                    <a href="index.php" class="nav-link nav-admin-link rounded-pill px-3 <?= ($paginaActual == 'index.php') ? 'active' : '' ?>">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="productos.php" class="nav-link nav-admin-link rounded-pill px-3 <?= ($paginaActual == 'productos.php') ? 'active' : '' ?>">Catálogo</a>
                </li>
                <li class="nav-item">
                    <a href="usuarios.php" class="nav-link nav-admin-link rounded-pill px-3 <?= ($paginaActual == 'usuarios.php') ? 'active' : '' ?>">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a href="historialPedidos.php" class="nav-link nav-admin-link rounded-pill px-3 <?= ($paginaActual == 'historialPedidos.php') ? 'active' : '' ?>">Envíos</a>
                </li>
                <li class="nav-item">
                    <a href="mensajes.php" class="nav-link nav-admin-link rounded-pill px-3 <?= ($paginaActual == 'mensajes.php') ? 'active' : '' ?>">Mensajes</a>
                </li>
            </ul>

            <!-- Botones y perfil lateral derecho -->
            <div class="d-flex flex-column flex-lg-row align-items-center gap-3 mt-3 mt-lg-0">
                
                <a href="../tienda/index.php" class="btn btn-outline-light btn-sm rounded-pill px-4 fw-medium border-secondary text-white-50 hover-white">
                    🌐 Ver Tienda Pública
                </a>
                
                <div class="vr bg-secondary mx-1 d-none d-lg-block" style="width: 1px; opacity: 0.5;"></div>
                
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.85rem;">
                        <?= strtoupper(substr($nombreAdmin, 0, 1)) ?>
                    </div>
                    <div class="text-white small fw-semibold d-none d-xl-block pe-2">
                        <?= htmlspecialchars($nombreAdmin) ?>
                    </div>
                </div>
                
                <a href="../controladores/logout.php" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                    Salir 🚪
                </a>
            </div>
        </div>
    </div>
</nav>