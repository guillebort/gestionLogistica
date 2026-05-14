<?php
// Comprobamos si la sesión ya está iniciada para no duplicar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombreUsu = $_SESSION['nombreUsuario'] ?? '';
$textoEnlace = !empty($nombreUsu) ? "👋 Hola, " . htmlspecialchars($nombreUsu) : '👤 Acceder';
?>
<!-- Importamos Poppins aquí para garantizar que toda la web comparta la tipografía -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<nav class="navbar navbar-expand-lg sticky-top navbar-main">
    <div class="container">
        <!-- Logo / Marca -->
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="../tienda/index.php">
            <span class="navbar-brand-icon">🚚</span> LogisTFG
        </a>
        
        <!-- Botón Hamburguesa para Móviles -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Enlaces -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto gap-2 mt-3 mt-lg-0 text-center text-lg-start">
                <li class="nav-item"><a class="nav-link text-dark fw-medium px-3 rounded-pill" href="../tienda/index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link text-dark fw-medium px-3 rounded-pill" href="../tienda/empresa.php">Empresa</a></li>
                <li class="nav-item"><a class="nav-link text-dark fw-medium px-3 rounded-pill" href="../tienda/productos.php">Tarifas</a></li>
                <li class="nav-item"><a class="nav-link text-dark fw-medium px-3 rounded-pill" href="../tienda/contacto.php">Contacto</a></li>
            </ul>
            
            <!-- Botones de Acción -->
            <div class="d-flex flex-column flex-lg-row align-items-center gap-3 mt-3 mt-lg-0">
                <a href="../tienda/carrito.php" class="btn btn-light rounded-pill fw-medium px-4 shadow-sm">
                    🛒 Cesta
                </a>
                <a href="../tienda/usuario.php" class="btn btn-primary rounded-pill fw-semibold px-4 shadow-sm">
                    <?= $textoEnlace ?>
                </a>
            </div>
        </div>
    </div>
</nav>