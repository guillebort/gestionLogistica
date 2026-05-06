<?php
session_start();
require_once '../modelos/AccesoBD.php';

// 1. Seguridad: Verificar sesión y carrito
$idUsuario = $_SESSION['codigo'] ?? null;
if ($idUsuario == null || !isset($_SESSION['carritoJSON'])) {
    header("Location: productos.php");
    exit;
}

// 2. Obtener datos del cliente para el autocompletado
$con = AccesoBD::getInstance();
$u = $con->obtenerUsuarioBD($idUsuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos de Envío y Ruta - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">
    
    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="d-flex justify-content-between mb-4">
                    <div class="text-primary fw-bold">Paso 1: Datos y Ruta</div>
                    <div class="text-muted">Paso 2: Pago Seguro</div>
                </div>

                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white py-3 text-center">
                        <h4 class="mb-0">📋 Información del Envío</h4>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="../controladores/guardarRuta.php" method="POST">
                            
                            <h5 class="text-secondary mb-3">Información de Contacto</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($u->getNombre()) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Apellidos</label>
                                    <input type="text" class="form-control" name="apellidos" value="<?= htmlspecialchars($u->getApellidos()) ?>" required>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label small fw-bold">Correo Electrónico</label>
                                    <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($u->getUsuario()) ?>" required disabled>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Teléfono</label>
                                    <input type="tel" class="form-control" name="telefono" value="<?= htmlspecialchars($u->getTelefono()) ?>" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="text-primary mb-3">📍 Dirección de Recogida (Origen)</h5>
                            <div class="mb-4 position-relative">
                                <input type="text" class="form-control form-control-lg border-primary" 
                                       name="direccionOrigen" id="input_origen" 
                                       placeholder="Calle, número, ciudad..." required autocomplete="off">
                                <ul id="lista_origen" class="list-group position-absolute w-100 shadow" 
                                    style="z-index: 1050; display: none; top: 100%;"></ul>
                                
                                <input type="hidden" name="latOrigen" id="lat_origen" value="0.0">
                                <input type="hidden" name="lonOrigen" id="lon_origen" value="0.0">
                            </div>

                            <h5 class="text-success mb-3">🏁 Dirección de Entrega (Destino)</h5>
                            <div class="mb-4 position-relative">
                                <input type="text" class="form-control form-control-lg border-success" 
                                       name="direccionDestino" id="input_destino" 
                                       placeholder="Calle, número, ciudad..." required autocomplete="off">
                                <ul id="lista_destino" class="list-group position-absolute w-100 shadow" 
                                    style="z-index: 1050; display: none; top: 100%;"></ul>
                                
                                <input type="hidden" name="latDestino" id="lat_destino" value="0.0">
                                <input type="hidden" name="lonDestino" id="lon_destino" value="0.0">
                            </div>

                            <div class="d-grid gap-2 mt-5">
                                <button type="submit" class="btn btn-dark btn-lg shadow-sm">
                                    Confirmar Ruta y Continuar al Pago ➔
                                </button>
                                <a href="carrito.php" class="btn btn-link text-muted">Volver al carrito</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    
    <script src="../js/logica.js?v=<?= time() ?>"></script>
</body>
</html>