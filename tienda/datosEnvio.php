<?php
if (!isset($u)) {
    header("Location: ../controladores/datosEnvioController.php");
    exit;
}
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
                
                <!-- Indicador de progreso (Stepper) modernizado -->
                <div class="d-flex justify-content-between mb-5 position-relative">
                    <div class="progress position-absolute top-50 start-0 w-100 translate-middle-y stepper-line">
                    <div class="progress-bar bg-primary stepper-progress" role="progressbar"></div>
                </div>
                <div class="position-relative z-index-1 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow fw-bold stepper-circle">1</div>
                    <div class="position-relative z-index-1 bg-white text-muted border border-2 rounded-circle d-flex align-items-center justify-content-center fw-bold">2</div>
                </div>

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 text-center">
                        <h4 class="mb-0 fw-bold text-dark">📍 Define la Ruta del Envío</h4>
                        <p class="text-secondary small mt-1">Valida tus datos y busca las direcciones para calcular coordenadas.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="../controladores/guardarRuta.php" method="POST">
                            
                            <!-- Sección Contacto (Tus campos name originales) -->
                            <div class="bg-light p-4 rounded-4 mb-5 border-0 shadow-sm">
                                <h6 class="text-primary mb-3 fw-bold text-uppercase">Datos del remitente</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control rounded-pill shadow-none border-0" name="nombre" value="<?= htmlspecialchars($u->getNombre()) ?>" placeholder="Nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control rounded-pill shadow-none border-0" name="apellidos" value="<?= htmlspecialchars($u->getApellidos()) ?>" placeholder="Apellidos" required>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="email" class="form-control rounded-pill bg-white text-muted border-0 shadow-none" value="<?= htmlspecialchars($u->getUsuario()) ?>" readonly>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="tel" class="form-control rounded-pill shadow-none border-0" name="telefono" value="<?= htmlspecialchars($u->getTelefono()) ?>" placeholder="Teléfono" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección Ruta Logística (Fundamental para JS Autocomplete) -->
                            <div class="position-relative ms-3 border-start border-3 border-primary ps-4 py-2 mb-4">
                                <!-- Origen -->
                                <div class="mb-5 position-relative">
                                    <span class="position-absolute translate-middle bg-white border border-primary border-3 rounded-circle"></span>
                                    <label class="form-label fw-bold text-primary mb-1">Recogida (Origen)</label>
                                    
                                    <!-- INPUTS ORIGINALES MANTENIDOS PARA JS -->
                                    <input type="text" class="form-control form-control-lg rounded-4 shadow-sm" name="direccionOrigen" id="input_origen" 
                                           placeholder="Escribe la dirección de recogida..." required autocomplete="off">
                                    
                                    <ul id="lista_origen" class="list-group position-absolute w-100 shadow-lg bg-white border border-secondary mt-1" style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;"></ul>
                                    
                                    <!-- Campos ocultos de coordenadas -->
                                    <input type="hidden" name="latOrigen" id="lat_origen" value="0.0">
                                    <input type="hidden" name="lonOrigen" id="lon_origen" value="0.0">
                                </div>

                                <!-- Destino -->
                                <div class="position-relative mt-4">
                                    <span class="position-absolute translate-middle bg-primary rounded-circle shadow-sm"></span>
                                    <label class="form-label fw-bold text-dark mb-1">Entrega (Destino)</label>
                                    
                                    <!-- INPUTS ORIGINALES MANTENIDOS PARA JS -->
                                    <input type="text" class="form-control form-control-lg rounded-4 shadow-sm border-0 bg-light" 
                                           name="direccionDestino" id="input_destino" 
                                           placeholder="Escribe la dirección de entrega..." required autocomplete="off">
                                           
                                    <ul id="lista_destino" class="list-group position-absolute w-100 shadow-lg bg-white border border-secondary mt-1" style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;"></ul>
                                    
                                    <!-- Campos ocultos de coordenadas -->
                                    <input type="hidden" name="latDestino" id="lat_destino" value="0.0">
                                    <input type="hidden" name="lonDestino" id="lon_destino" value="0.0">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-lg shadow rounded-pill fw-bold">
                                    Guardar Ruta y Pagar ➔
                                </button>
                                <a href="carrito.php" class="text-center text-secondary text-decoration-none mt-2 small fw-medium">← Volver al carrito</a>
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