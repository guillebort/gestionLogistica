<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombreAdmin = $_SESSION['nombreAdmin'] ?? 'Administrador';
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg sticky-top navbar-admin">
    <div class="container-fluid px-4 py-2">
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2 m-0" href="../controladoresAdmin/indexController.php">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 1.1rem;">⚙️</div>
            <span class="fs-5 tracking-tight">LogisTFG</span> 
            <span class="badge bg-white text-primary bg-opacity-10 border border-light border-opacity-25 rounded-pill fw-medium ms-1" style="font-size: 0.7rem; letter-spacing: 1px;">ADMIN</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
            <span class="navbar-toggler-icon" style="filter: invert(1) opacity(0.7);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2 ms-lg-5 text-center text-lg-start mt-3 mt-lg-0 fw-medium">
                <li class="nav-item">
                    <a href="../controladoresAdmin/indexController.php" class="nav-link nav-admin-link <?= ($paginaActual == 'index.php' || $paginaActual == 'indexController.php') ? 'active' : '' ?>">📊 Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="../controladoresAdmin/productosController.php" class="nav-link nav-admin-link <?= ($paginaActual == 'productos.php' || $paginaActual == 'productosController.php') ? 'active' : '' ?>">🏷️ Catálogo</a>
                </li>
                <li class="nav-item">
                    <a href="../controladoresAdmin/usuariosController.php" class="nav-link nav-admin-link <?= ($paginaActual == 'usuarios.php' || $paginaActual == 'usuariosController.php') ? 'active' : '' ?>">👥 Usuarios</a>
                </li>
                <li class="nav-item">
                    <a href="../controladoresAdmin/historialPedidosController.php" class="nav-link nav-admin-link <?= ($paginaActual == 'historialPedidos.php' || $paginaActual == 'historialPedidosController.php') ? 'active' : '' ?>">📦 Envíos</a>
                </li>
                <li class="nav-item">
                    <a href="../controladoresAdmin/mensajesController.php" class="nav-link nav-admin-link <?= ($paginaActual == 'mensajes.php' || $paginaActual == 'mensajesController.php') ? 'active' : '' ?>">📩 Mensajes</a>
                </li>
            </ul>
            <div class="d-flex flex-column flex-lg-row align-items-center gap-3 mt-3 mt-lg-0">
                <a href="../tienda/index.php" class="btn btn-outline-light btn-sm rounded-pill px-4 fw-medium border-secondary text-white-50 hover-white" style="transition: all 0.3s ease;">
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