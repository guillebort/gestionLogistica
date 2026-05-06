<?php
session_start();
require_once '../modelos/AccesoBD.php'; // Llamamos al modelo

// Instanciamos la conexión y pedimos los productos
$bd = AccesoBD::getInstance();
$lista = $bd->obtenerProductosBD();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios y Tarifas - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <!-- Inyectamos la cabecera y el menú con PHP -->
    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 text-primary">Nuestras Tarifas de Envío</h2>
                <p class="lead text-secondary">Añade al carrito el servicio que necesites.</p>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($lista)): ?>
                <?php foreach ($lista as $p): ?>
                    <?php
                        $claseBoton = "btn-outline-primary"; 
                        $sufijo = "/ paquete";
                        $claseBorde = "shadow-sm"; 
                        
                        if ($p->getColorCss() === "primary") {
                            $claseBoton = "btn-primary";
                            $claseBorde = "shadow border-primary";
                        } elseif ($p->getColorCss() === "dark") {
                            $claseBoton = "btn-outline-dark";
                            $sufijo = "/ bulto";
                        }
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 <?= $claseBorde ?>">
                            <div class="card-header bg-<?= htmlspecialchars($p->getColorCss()) ?> text-white text-center">
                                <h4 class="my-0 font-weight-normal"><?= htmlspecialchars($p->getDescripcion()) ?></h4>
                            </div>
                            <div class="card-body d-flex flex-column text-center">
                                <h1 class="card-title pricing-card-title"><?= number_format($p->getPrecio(), 2) ?>€ <small class="text-muted"><?= $sufijo ?></small></h1>
                                
                                <ul class="list-unstyled mt-3 mb-4 text-start">
                                    <?= $p->getCaracteristicas() // Cuidado, si tiene HTML en la BD no lo escapamos aquí ?>
                                </ul>
                                
                                <button type="button" class="btn btn-lg btn-block <?= $claseBoton ?> mt-auto" 
                                    onclick="anadirCarrito(<?= $p->getId() ?>, '<?= addslashes($p->getDescripcion()) ?>', <?= $p->getPrecio() ?>, <?= $p->getExistencias() ?>)">
                                    Añadir al carrito 🛒
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning">No hay tarifas disponibles.</div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Inyectamos el pie de página con PHP -->
    <?php include '../includes/pie.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/logica.js"></script>
    <script src="../js/carrito.js?v=999"></script>
</body>
</html>