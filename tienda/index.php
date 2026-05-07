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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

    <?php include '../includes/menu.php'; ?>

    <main class="container mt-5">
        <div class="row text-center mb-4">
            <h2>Bienvenido a LogisTFG</h2>
            <p class="lead">Optimiza las rutas de tu pequeño negocio y gestiona tus pedidos de forma profesional.</p>
        </div>

        <div id="carruselInicio" class="carousel slide mb-5" data-bs-ride="carousel">
            <div class="carousel-inner shadow rounded">
                <div class="carousel-item active">
                    <img src="../img/furgoneta.jpg" class="d-block w-100 img-carrusel" alt="Furgoneta de reparto logística">
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="card-title">Gestión de Rutas</h3>
                        <p class="card-text">Consulta y optimiza los trayectos de tus repartidores.</p>
                        <a href="empresa.php" class="btn btn-primary">Saber más</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="card-title">Portal de Clientes</h3>
                        <p class="card-text">Área privada para realizar nuevas reservas de envío.</p>
                        <a href="usuario.php" class="btn btn-outline-primary">Acceso Usuario</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>