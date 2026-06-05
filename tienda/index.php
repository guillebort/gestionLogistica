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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <div class="position-relative overflow-hidden text-white d-flex align-items-center" style="min-height: 600px;">
        
        <img src="../img/portada.jpg" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" style="z-index: 0;" alt="Flota de Transporte Logístico">
        
        <div class="position-absolute top-0 start-0 w-100 h-100 hero-overlay" style="z-index: 1;"></div>
        
        <div class="container position-relative py-5" style="z-index: 2;">
            <div class="row">
                <div class="col-lg-8 col-xl-7 text-start">
                    
                    <h1 class="display-3 fw-bold mb-4 text-white text-shadow-main" style="line-height: 1.2;">
                        Envíos rápidos para <span class="text-primary">negocios locales</span>
                    </h1>
                    
                    <p class="lead mb-5 fw-medium text-light opacity-75 text-shadow-sub" style="font-size: 1.25rem; max-width: 600px;">
                        Optimiza tus rutas, reduce costes y gestiona tus entregas en tiempo real con nuestra plataforma inteligente de paquetería urbana.
                    </p>
                    
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="../controladores/productoController.php" class="btn btn-primary btn-lg px-5 shadow-lg rounded-pill fw-bold">
                            Ver Tarifas 📦
                        </a>
                        <a href="../controladores/usuarioController.php" class="btn btn-outline-light btn-lg px-4 shadow border-2 rounded-pill fw-bold" style="background-color: rgba(255,255,255,0.05); backdrop-filter: blur(5px);">
                            Área de Clientes 👤
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="container my-5 py-5">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="fw-bold text-dark">¿Por qué elegir LogisTFG?</h2>
                <p class="text-muted">Diseñado específicamente para cubrir las necesidades logísticas de las PYMES.</p>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4 border-0 shadow-sm rounded-4 hover-lift bg-white">
                    <div class="card-body">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
                            📍
                        </div>
                        <h4 class="card-title fw-bold text-dark">Rutas Optimizadas</h4>
                        <p class="card-text text-muted mt-3">Nuestro algoritmo agrupa los envíos para que el repartidor haga menos kilómetros en menos tiempo, garantizando la entrega.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4 border-0 shadow-sm rounded-4 hover-lift bg-white">
                    <div class="card-body">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
                            ⚡
                        </div>
                        <h4 class="card-title fw-bold text-dark">Gestión Ágil</h4>
                        <p class="card-text text-muted mt-3">Contrata servicios y genera albaranes de envío en menos de 3 clics desde tu panel privado de cliente. Sin burocracia.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4 border-0 shadow-sm rounded-4 hover-lift bg-white">
                    <div class="card-body">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
                            📱
                        </div>
                        <h4 class="card-title fw-bold text-dark">Conexión Total</h4>
                        <p class="card-text text-muted mt-3">Conexión directa con la flota a través de geolocalización. Actualizaciones del estado de tus entregas en tiempo real.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>