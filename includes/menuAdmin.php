<?php
// Comprobamos si la sesión ya está iniciada para no duplicar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Cogemos el nombre del admin de la sesión
$nombreAdmin = $_SESSION['nombreAdmin'] ?? 'Administrador';
?>
<nav class="navbar navbar-expand-lg sticky-top" style="background: #1e293b; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
    <div class="container-fluid px-4">
        <!-- Logo Admin -->
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="index.php">
            <span class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; font-size: 1rem;">⚙️</span> 
            LogisTFG <span class="fw-light ms-1">Admin</span>
        </a>
        
        <!-- Menú Móvil -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2 ms-lg-4 text-center text-lg-start mt-3 mt-lg-0">
                <li class="nav-item"><a href="index.php" class="nav-link text-light rounded-pill px-3">Dashboard</a></li>
                <li class="nav-item"><a href="productos.php" class="nav-link text-light rounded-pill px-3">Catálogo</a></li>
                <li class="nav-item"><a href="usuarios.php" class="nav-link text-light rounded-pill px-3">Usuarios</a></li>
                <li class="nav-item"><a href="historialPedidos.php" class="nav-link text-light rounded-pill px-3">Envíos</a></li>
                <li class="nav-item"><a href="mensajes.php" class="nav-link text-light rounded-pill px-3">Mensajes</a></li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-center gap-3 mt-3 mt-lg-0">
                <a href="../tienda/index.php" class="btn btn-outline-light btn-sm rounded-pill px-4 fw-medium">Ver Tienda</a>
                
                <div class="vr bg-light mx-1 d-none d-lg-block" style="width: 1px; opacity: 0.3;"></div>
                
                <div class="text-white small fw-medium d-none d-lg-block">
                    <?= htmlspecialchars($nombreAdmin) ?>
                </div>
                
                <a href="../controladores/logout.php" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold shadow-sm">Salir</a>
            </div>
        </div>
    </div>
</nav>