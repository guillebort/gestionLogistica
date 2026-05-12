<?php
require_once '../modelos/AccesoBD.php';
session_start();
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
    <title>Ruta de Envío - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">
    
    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                
                <!-- Indicador de progreso -->
                <div class="d-flex justify-content-between mb-4 position-relative">
                    <div class="progress position-absolute top-50 start-0 w-100 translate-middle-y" style="height: 3px; z-index: 0;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 50%;"></div>
                    </div>
                    <div class="position-relative z-index-1 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px;">1</div>
                    <div class="position-relative z-index-1 bg-white text-muted border rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 16px;">
                    <div class="card-header bg-dark text-white py-3 text-center border-0" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <h4 class="mb-0 fw-bold">📍 Define la Ruta del Envío</h4>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="../controladores/guardarRuta.php" method="POST">
                            
                            <!-- Sección Contacto -->
                            <div class="bg-light p-4 rounded-4 mb-4 border">
                                <h6 class="text-secondary mb-3 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Datos del remitente</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control bg-white" name="nombre" value="<?= htmlspecialchars($u->getNombre()) ?>" placeholder="Nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control bg-white" name="apellidos" value="<?= htmlspecialchars($u->getApellidos()) ?>" placeholder="Apellidos" required>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="email" class="form-control text-muted" value="<?= htmlspecialchars($u->getUsuario()) ?>" readonly>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="tel" class="form-control bg-white" name="telefono" value="<?= htmlspecialchars($u->getTelefono()) ?>" placeholder="Teléfono" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección Ruta -->
                            <div class="position-relative ms-3 border-start border-3 border-primary ps-4 py-2 mb-4">
                                <!-- Origen -->
                                <div class="mb-4 position-relative">
                                    <span class="position-absolute translate-middle bg-white border border-primary border-3 rounded-circle" style="width: 16px; height: 16px; left: -26px; top: 20px;"></span>
                                    <label class="form-label fw-bold text-primary">Recogida (Origen)</label>
                                    <input type="text" class="form-control form-control-lg shadow-sm" style="border-color: #cfe2ff;"
                                           name="direccionOrigen" id="input_origen" 
                                           placeholder="Escribe la dirección de recogida..." required autocomplete="off">
                                    <ul id="lista_origen" class="list-group position-absolute w-100 shadow-lg" 
                                        style="z-index: 1050; display: none; top: 100%; border-radius: 8px; overflow: hidden;"></ul>
                                    
                                    <input type="hidden" name="latOrigen" id="lat_origen" value="0.0">
                                    <input type="hidden" name="lonOrigen" id="lon_origen" value="0.0">
                                </div>

                                <!-- Destino -->
                                <div class="position-relative mt-4">
                                    <span class="position-absolute translate-middle bg-primary rounded-circle" style="width: 16px; height: 16px; left: -26px; top: 20px;"></span>
                                    <label class="form-label fw-bold text-dark">Entrega (Destino)</label>
                                    <input type="text" class="form-control form-control-lg shadow-sm" 
                                           name="direccionDestino" id="input_destino" 
                                           placeholder="Escribe la dirección de entrega..." required autocomplete="off">
                                    <ul id="lista_destino" class="list-group position-absolute w-100 shadow-lg" 
                                        style="z-index: 1050; display: none; top: 100%; border-radius: 8px; overflow: hidden;"></ul>
                                    
                                    <input type="hidden" name="latDestino" id="lat_destino" value="0.0">
                                    <input type="hidden" name="lonDestino" id="lon_destino" value="0.0">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-5">
                                <button type="submit" class="btn btn-dark btn-lg shadow rounded-pill fw-bold">
                                    Guardar Ruta y Pagar ➔
                                </button>
                                <a href="carrito.php" class="text-center text-muted text-decoration-none mt-2 small">Volver al carrito</a>
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