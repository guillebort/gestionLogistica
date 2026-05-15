<?php
if (!isset($listaProductos)) {
    header("Location: ../controladores/productoController.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios y Tarifas - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row text-center mb-5">
            <div class="col-lg-6 mx-auto">
                <h2 class="display-6 fw-bold text-dark mb-3">Tarifas transparentes</h2>
                <p class="lead text-secondary">Añade al carrito el servicio logístico que mejor se adapte a tu necesidad. Sin costes ocultos.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php if (!empty($listaProductos)): ?>
                <?php foreach ($listaProductos as $p): ?>
                    <?php
                        // Estilos dinámicos estilo "Planes"
                        $esDestacado = ($p->getColorCss() === "primary");
                        $cardClass = $esDestacado ? "border-primary shadow-lg" : "border-0 shadow-sm";
                        $headerClass = $esDestacado ? "bg-primary text-white" : "bg-white text-dark border-bottom";
                        $btnClass = $esDestacado ? "btn-primary" : "btn-outline-dark";
                        $sufijo = ($p->getColorCss() === "dark") ? "/ bulto" : "/ paquete";
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 <?= $cardClass ?>" >
                            <?php if($esDestacado): ?>
                                <!-- Etiqueta superior para el plan recomendado -->
                                <div class="bg-warning text-dark text-center fw-bold py-1">MÁS POPULAR</div>
                            <?php endif; ?>
                            
                            <div class="card-header text-center py-4 <?= $headerClass ?>">
                                <h4 class="my-0 fw-bold"><?= htmlspecialchars($p->getDescripcion()) ?></h4>
                            </div>
                            <div class="card-body p-4 d-flex flex-column text-center">
                                <div class="mb-4">
                                    <span class="display-5 fw-bold text-dark"><?= number_format($p->getPrecio(), 2) ?>€</span>
                                    <span class="text-muted"> <?= $sufijo ?></span>
                                </div>
                                
                                <ul class="list-unstyled mb-5 text-start text-secondary mx-auto">
                                    <?= str_replace('<li>', '<li class="mb-2">✔️ ', $p->getCaracteristicas()) ?>
                                </ul>
                                
                                <button type="button" class="btn btn-lg w-100 <?= $btnClass ?> mt-auto fw-bold"
                                    onclick="anadirCarrito(<?= $p->getId() ?>, '<?= addslashes($p->getDescripcion()) ?>', <?= $p->getPrecio() ?>, <?= $p->getExistencias() ?>)">
                                    Añadir al carrito 
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning rounded-4 p-4 shadow-sm">No hay tarifas logísticas disponibles en este momento.</div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/logica.js"></script>
    <script src="../js/carrito.js?v=999"></script>
</body>
</html>