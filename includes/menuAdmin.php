<?php
// Comprobamos si la sesión ya está iniciada para no duplicar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Cogemos el nombre del admin de la sesión (se guarda en loginAdminController.php)
$nombreAdmin = $_SESSION['nombreAdmin'] ?? 'Administrador';
?>
<nav class="navbar navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <!-- Título / Logo -->
        <span class="navbar-brand mb-0 h1">⚙️ LogisTFG - Panel de Administración</span>
        
        <!-- Botones de acción derecha y navegación -->
        <div class="d-flex gap-2 align-items-center">
            <!-- Navegación interna del Admin -->
            <a href="index.php" class="btn btn-outline-light btn-sm d-none d-md-block">Panel</a>
            <a href="productos.php" class="btn btn-outline-light btn-sm d-none d-md-block">Catálogo</a>
            <a href="usuarios.php" class="btn btn-outline-light btn-sm d-none d-md-block">Usuarios</a>
            <a href="historialPedidos.php" class="btn btn-outline-light btn-sm d-none d-md-block">Historial</a>
            
            <!-- Separador visual -->
            <div class="vr bg-light mx-2 d-none d-md-block" style="width: 2px; opacity: 0.5;"></div>
            
            <!-- Volver a tienda -->
            <a href="../tienda/index.php" class="btn btn-outline-info btn-sm">Tienda</a>
            
            <!-- Info del usuario y Botón Salir -->
            <span class="navbar-text text-white ms-2 me-2 d-none d-lg-block">
                <?= htmlspecialchars($nombreAdmin) ?>
            </span>
            <a href="../controladores/logout.php" class="btn btn-danger btn-sm">Salir</a>
        </div>
    </div>
</nav>