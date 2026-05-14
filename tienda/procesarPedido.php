<?php
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Recuperamos los datos de la sesión
$listaCarrito = $_SESSION["carritoJSON"] ?? [];
$total = $_SESSION["totalPedido"] ?? 0.0;

if (empty($listaCarrito)) {
    header("Location: carrito.php");
    exit;
}

// Conseguir las tarjetas del cliente logueado
$codigoLogueado = $_SESSION["codigo"] ?? 0;
$misTarjetas = [];
if ($codigoLogueado > 0) {
    $con = AccesoBD::getInstance();
    $misTarjetas = $con->obtenerTarjetasUsuario($codigoLogueado);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pasarela de Pago - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <?php 
            $mensaje = $_SESSION["mensaje"] ?? null;
            if ($mensaje != null) { 
                unset($_SESSION["mensaje"]); 
        ?>
            <div class="alert alert-danger text-center shadow-sm mb-4 rounded-4 border-0"><?= htmlspecialchars($mensaje); ?></div>
        <?php } ?>

        <div class="row text-center mb-4">
            <h2 class="fw-bold text-dark">Finalizar Reserva Logística</h2>
            <p class="text-muted">Elige tu método de pago y confirma los servicios contratados.</p>
        </div>

        <form action="../controladores/finalizarPedido.php" method="POST" id="formPago">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="row g-4 flex-lg-row-reverse">
                
                <!-- COLUMNA DERECHA: RESUMEN STICKY -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-4 position-sticky">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Resumen del Pedido</h5>
                            
                            <ul class="list-group list-group-flush bg-transparent mb-3">
                                <?php foreach ($listaCarrito as $p) { ?>
                                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between lh-sm border-secondary border-opacity-25">
                                        <div>
                                            <h6 class="my-0 fw-medium"><?= htmlspecialchars($p->getDescripcion()); ?></h6>
                                            <small class="text-muted">Cantidad: <?= $p->getCantidad(); ?></small>
                                        </div>
                                        <span class="text-dark fw-medium"><?= number_format((float)($p->getPrecio() * $p->getCantidad()), 2, '.', ''); ?>€</span>
                                    </li>
                                <?php } ?>
                            </ul>

                            <div class="d-flex justify-content-between border-top border-secondary border-opacity-25 pt-3 mt-3">
                                <span class="fs-5 fw-bold text-dark">Total</span>
                                <strong class="fs-3 text-success"><?= number_format((float)$total, 2, '.', ''); ?> €</strong>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 mt-4 rounded-pill shadow fw-bold">
                                Confirmar y Pagar 🔒
                            </button>
                            <p class="text-center text-muted mt-3 small mb-0">Tus datos están protegidos y cifrados.</p>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA IZQUIERDA: PAGO -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="fw-bold mb-4 border-bottom pb-2">Método de Pago</h5>

                            <!-- SELECT VISUALMENTE OCULTO (Para no romper tu logica.js original) -->
                            <select class="d-none" name="tarjetaGuardada" id="tarjetaGuardada">
                                <option value="NUEVA" id="opt_NUEVA" selected>Nueva</option>
                                <?php foreach ($misTarjetas as $t) { ?>
                                    <option value="<?= $t->getId(); ?>" id="opt_<?= $t->getId(); ?>"
                                            data-numero="<?= htmlspecialchars($t->getNumero()); ?>" 
                                            data-titular="<?= htmlspecialchars($t->getTitular()); ?>" 
                                            data-caducidad="<?= htmlspecialchars($t->getCaducidad()); ?>">
                                    </option>
                                <?php } ?>
                            </select>

                            <?php if (!empty($misTarjetas)) { ?>
                                <p class="text-muted small fw-bold text-uppercase mb-2">Tus tarjetas guardadas</p>
                                <div class="row g-3 mb-4">
                                    <?php foreach ($misTarjetas as $t) { ?>
                                        <div class="col-md-6">
                                            <input type="radio" class="btn-check tarjeta-radio" name="vista_tarjeta" id="visual_<?= $t->getId(); ?>" value="<?= $t->getId(); ?>" autocomplete="off">
                                            <label class="btn btn-outline-secondary p-3 text-start w-100 rounded-3 tarjeta-label border-2" for="visual_<?= $t->getId(); ?>">
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-bold text-dark mb-1">💳 <?= htmlspecialchars($t->getNumeroOculto()); ?></span>
                                                </div>
                                                <small class="text-muted d-block"><?= htmlspecialchars($t->getTitular()); ?></small>
                                                <small class="text-muted d-block">Caduca: <?= htmlspecialchars($t->getCaducidad()); ?></small>
                                            </label>
                                        </div>
                                    <?php } ?>
                                    
                                    <!-- Botón Nueva Tarjeta -->
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check tarjeta-radio" name="vista_tarjeta" id="visual_NUEVA" value="NUEVA" autocomplete="off" checked>
                                        <label class="btn btn-outline-secondary p-3 text-center w-100 rounded-3 tarjeta-label border-2 h-100 d-flex flex-column justify-content-center" for="visual_NUEVA">
                                            <span class="fs-4 mb-1">➕</span>
                                            <span class="fw-bold">Añadir otra tarjeta</span>
                                        </label>
                                    </div>
                                </div>
                            <?php } ?>

                            <!-- FORMULARIO TARJETA -->
                            <div id="seccionNuevaTarjeta" class="bg-light p-4 rounded-4 border-0 mt-3">
                                <h6 class="fw-bold text-dark mb-3">Detalles de la tarjeta</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control bg-white" name="titularTarjeta" id="titularTarjeta" placeholder="Titular" required>
                                            <label for="titularTarjeta">Nombre del titular</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control bg-white" name="numeroTarjeta" id="numeroTarjeta" placeholder="Número" maxlength="19" required>
                                            <label for="numeroTarjeta">Número de tarjeta</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control bg-white" name="caducidadTarjeta" id="caducidadTarjeta" placeholder="MM/AAAA" maxlength="7" required>
                                            <label for="caducidadTarjeta">Caducidad (MM/AAAA)</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control bg-white" placeholder="CVV" maxlength="3" required>
                                            <label>CVV</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="guardarTarjetaCheck" value="SI" id="guardarCheck">
                                    <label class="form-check-label text-muted fw-medium" for="guardarCheck">
                                        Guardar esta tarjeta para mis futuras rutas
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                    <a href="datosEnvio.php" class="btn btn-link text-muted text-decoration-none fw-medium ps-0">← Volver a modificar dirección</a>
                </div>
            </div>
        </form>
    </main>

    <?php include '../includes/pie.php'; ?>
    
    <script src="../js/logica.js?v=<?= time(); ?>"></script>
    <script>
        // Sincroniza los radio buttons visuales (nuevos) con el select original (invisible)
        // para que logica.js procese todo automáticamente sin romperse.
        const radioTarjetas = document.querySelectorAll('.tarjeta-radio');
        const selectReal = document.getElementById('tarjetaGuardada');

        radioTarjetas.forEach(radio => {
            radio.addEventListener('change', function() {
                // Selecciona el option adecuado en el select oculto
                document.getElementById('opt_' + this.value).selected = true;
                // Dispara el evento 'change' del select real para que actúe logica.js
                selectReal.dispatchEvent(new Event('change'));
            });
        });
    </script>
</body>
</html>