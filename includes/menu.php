<?php
// Comprobamos si la sesión ya está iniciada para no duplicar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombreUsu = $_SESSION['nombreUsuario'] ?? '';
$textoEnlace = !empty($nombreUsu) ? "👋 Hola, " . htmlspecialchars($nombreUsu) : '👤 Usuario';
?>
<header class="bg-primary text-white text-center py-4">
    <h1>🚚 LogisTFG - Gestión de Repartos</h1>
</header>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="../vistas/index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="../vistas/empresa.php">Empresa</a></li>
                <li class="nav-item"><a class="nav-link" href="../vistas/productos.php">Productos/Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="../vistas/contacto.php">Contacto</a></li>
                <li class="nav-item"><a class="nav-link" href="../vistas/carrito.php">🛒 Carrito</a></li>
                <li class="nav-item"><a class="nav-link" href="../vistas/usuario.php"><?= $textoEnlace ?></a></li>
            </ul>
        </div>
    </div>
</nav>