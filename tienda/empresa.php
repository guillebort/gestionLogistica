<?php
session_start();
$nombreMenu = $_SESSION["nombreUsuario"] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestra Empresa - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row mb-5 pt-4">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold text-dark">Sobre LogisTFG</h2>
                <p class="lead text-secondary">Más de 10 años conectando negocios locales con sus clientes.</p>
            </div>
        </div>

        <div class="row mb-5 align-items-center g-5">
            <div class="col-md-6">
                <h3 class="fw-bold mb-4">Nuestra Historia y Misión</h3>
                <p class="text-muted fs-5">Nacimos en 2016 como un pequeño proyecto universitario con un objetivo claro: ofrecer a las PYMES una solución logística a la altura de las grandes multinacionales, pero con un trato cercano y tarifas adaptadas.</p>
                <p class="text-muted fs-5">Nuestra misión es optimizar las rutas de reparto urbanas e interurbanas, reduciendo la huella de carbono y garantizando entregas rápidas y seguras.</p>
            </div>
            <div class="col-md-6">
                <!-- IMAGEN PROFESIONAL EN VEZ DEL RECUADRO GRIS -->
                <img src="../img/empresa.avif" class="img-fluid rounded-4 shadow-lg w-100 object-fit-cover" style="height: 400px;" alt="Instalaciones del Centro Logístico">            
            </div>
        </div>

        <div class="row text-center mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white rounded-4 p-3">
                    <div class="card-body">
                        <div class="fs-1 mb-3">🎯</div>
                        <h4 class="card-title fw-bold text-dark">Control de Calidad</h4>
                        <p class="card-text text-muted mt-3">Nuestra flota cuenta con seguimiento GPS en tiempo real. Auditamos el 100% de las rutas para asegurar que se cumplen los tiempos estimados.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white rounded-4 p-3">
                    <div class="card-body">
                        <div class="fs-1 mb-3">🏢</div>
                        <h4 class="card-title fw-bold text-dark">Organización</h4>
                        <p class="card-text text-muted mt-3">Disponemos de una red de 5 nodos logísticos estratégicos. Nuestro equipo se divide en gestión de tráfico, atención al cliente y operaciones de almacén.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white rounded-4 p-3">
                    <div class="card-body">
                        <div class="fs-1 mb-3">🌱</div>
                        <h4 class="card-title fw-bold text-dark">Sostenibilidad</h4>
                        <p class="card-text text-muted mt-3">El 40% de nuestros vehículos de última milla son 100% eléctricos, ayudando a crear ciudades con un aire más limpio.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>