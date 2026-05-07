<?php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';
session_start();
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
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                
                <?php 
                   $mensaje = $_SESSION["mensaje"] ?? null;
                   if ($mensaje != null) { 
                       unset($_SESSION["mensaje"]); 
                ?>
                    <div class="alert alert-danger text-center shadow-sm mb-4"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php } ?>

                <div class="card shadow border-0">
                    <div class="card-header bg-success text-white text-center py-3">
                        <h4 class="mb-0">💳 Formalizar Reserva Logística</h4>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                            <strong>Total a pagar:</strong>
                            <span class="fs-3 fw-bold"><?php echo number_format((float)$total, 2, '.', ''); ?> €</span>
                        </div>

                        <form action="../controladores/finalizarPedido.php" method="POST" id="formPago">
                            
                            <h5 class="mb-3">Servicios Contratados</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-borderless">
                                    <?php foreach ($listaCarrito as $p) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p->getDescripcion()); ?> <span class="text-muted">x<?php echo $p->getCantidad(); ?></span></td>
                                            <td class="text-end"><?php echo number_format((float)($p->getPrecio() * $p->getCantidad()), 2, '.', ''); ?>€</td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Método de Pago</h5>
                            
                            <?php if (!empty($misTarjetas)) { ?>
                                <div class="mb-4 p-3 border rounded bg-light border-success">
                                    <label class="form-label fw-bold text-success">Usar una de mis tarjetas:</label>
                                    <select class="form-select border-success" name="tarjetaGuardada" id="tarjetaGuardada">
                                        <option value="NUEVA">➕ Añadir nueva tarjeta...</option>
                                        <?php foreach ($misTarjetas as $t) { ?>
                                            <option value="<?php echo $t->getId(); ?>" 
                                                    data-numero="<?php echo htmlspecialchars($t->getNumero()); ?>" 
                                                    data-titular="<?php echo htmlspecialchars($t->getTitular()); ?>" 
                                                    data-caducidad="<?php echo htmlspecialchars($t->getCaducidad()); ?>">
                                                <?php echo htmlspecialchars($t->getNumeroOculto()); ?> (<?php echo htmlspecialchars($t->getTitular()); ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>

                            <div id="seccionNuevaTarjeta">
                                <div class="row g-3">
                                    <div class="col-12 mb-2">
                                        <label class="form-label">Titular de la tarjeta</label>
                                        <input type="text" class="form-control" name="titularTarjeta" id="titularTarjeta" placeholder="Nombre completo">
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label class="form-label">Número de tarjeta</label>
                                        <input type="text" class="form-control" name="numeroTarjeta" id="numeroTarjeta" placeholder="0000 0000 0000 0000" maxlength="19">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Fecha de Caducidad</label>
                                        <input type="text" class="form-control" name="caducidadTarjeta" id="caducidadTarjeta" placeholder="MM/AAAA" maxlength="7">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" placeholder="123" maxlength="3">
                                    </div>
                                </div>

                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="guardarTarjetaCheck" value="SI" id="guardarCheck">
                                    <label class="form-check-label text-success" for="guardarCheck">
                                        <strong>Guardar tarjeta para futuros pedidos</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-5">
                                <a href="datosEnvio.php" class="text-muted text-decoration-none">← Volver a ruta</a>
                                <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                    Pagar y Confirmar Pedido 🔒
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                
                <p class="text-center text-muted mt-4 small">
                    Pago seguro procesado por LogisTFG. Tus datos están cifrados.
                </p>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    
    <script src="../js/logica.js?v=<?php echo time(); ?>"></script>
</body>
</html>