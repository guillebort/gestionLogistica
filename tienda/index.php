<?php
session_start();
$nombreUsuario = $_SESSION['nombreUsuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

    <?php include '../includes/menu.php'; ?>

    <!-- HERO SECTION (Estilo Startup) -->
    <div class="bg-dark text-white py-5 text-center">
        <div class="container py-5">
            <h1 class="display-4 fw-bold mb-3">Envíos rápidos para negocios locales</h1>
            <p class="lead mb-5 text-light opacity-75">Optimiza tus rutas, reduce costes y gestiona tus entregas en tiempo real con nuestra plataforma inteligente.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="productos.php" class="btn btn-primary btn-lg px-5 shadow">Ver Tarifas</a>
                <a href="usuario.php" class="btn btn-outline-light btn-lg px-4">Área de Clientes</a>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE VENTAJAS -->
    <main class="container my-5">
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <div class="display-5 mb-3">📍</div>
                        <h4 class="card-title fw-bold">Rutas Optimizadas</h4>
                        <p class="card-text text-muted">Nuestro algoritmo agrupa los envíos para que el repartidor haga menos kilómetros en menos tiempo.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <div class="display-5 mb-3">⚡</div>
                        <h4 class="card-title fw-bold">Gestión Ágil</h4>
                        <p class="card-text text-muted">Contrata servicios y genera etiquetas de envío en menos de 3 clics desde tu panel privado.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <div class="display-5 mb-3">📱</div>
                        <h4 class="card-title fw-bold">App Repartidor</h4>
                        <p class="card-text text-muted">Conexión directa con la flota. Actualizaciones de estado de entrega en tiempo real.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>