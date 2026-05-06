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
                <li class="nav-item"><a class="nav-link" href="../tienda/index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="../tienda/empresa.php">Empresa</a></li>
                <li class="nav-item"><a class="nav-link" href="../tienda/productos.php">Productos/Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="../tienda/contacto.php">Contacto</a></li>
                <li class="nav-item"><a class="nav-link" href="../tienda/carrito.php">🛒 Carrito</a></li>
                <li class="nav-item"><a class="nav-link" href="../tienda/usuario.php"><?= $textoEnlace ?></a></li>
            </ul>
        </div>
    </div>
</nav>