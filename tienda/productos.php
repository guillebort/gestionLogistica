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

        <div class="row justify-content-center g-4">
            <?php if (!empty($listaProductos)): ?>
                <?php foreach ($listaProductos as $p): ?>
                    <?php
                        $esDestacado = ($p->getColorCss() === "primary");
                        $cardClass = $esDestacado ? "border-primary shadow-lg" : "border-0 shadow-sm";
                        $sufijo = ($p->getColorCss() === "dark") ? "/ bulto" : "/ paquete";
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 rounded-4 hover-lift <?= $cardClass ?>" style="<?= $esDestacado ? 'border: 2px solid var(--color-primary);' : '' ?>">
                            <?php if($esDestacado): ?>
                                <div class="bg-primary text-white text-center fw-bold py-2 rounded-top-3 text-uppercase tracking-wider small">MÁS POPULAR</div>
                            <?php endif; ?>
                            
                            <div class="card-body p-4 p-xl-5 d-flex flex-column">
                                <h4 class="fw-bold text-dark mb-3 text-center"><?= htmlspecialchars($p->getDescripcion()) ?></h4>
                                <div class="text-center mb-4 pb-3 border-bottom">
                                    <span class="display-4 fw-bold text-dark"><?= number_format($p->getPrecio(), 2) ?>€</span>
                                    <span class="text-muted fw-medium"><?= $sufijo ?></span>
                                </div>
                                
                                <ul class="list-unstyled mb-5 text-secondary flex-grow-1">
                                    <?= str_replace('<li>', '<li class="mb-3 d-flex align-items-start gap-2"><span class="text-primary">✔️</span> ', $p->getCaracteristicas()) ?>
                                </ul>
                                
                                <button type="button" class="btn btn-lg w-100 rounded-pill fw-bold <?= $esDestacado ? 'btn-primary' : 'btn-outline-dark' ?>"
                                    onclick="anadirCarrito(<?= $p->getId() ?>, '<?= addslashes($p->getDescripcion()) ?>', <?= $p->getPrecio() ?>, <?= $p->getExistencias() ?>)">
                                    Añadir al carrito 🛒
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning rounded-4 p-4 shadow-sm fw-bold">No hay tarifas logísticas disponibles en este momento.</div>
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