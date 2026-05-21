<?php

if (!isset($u) && !isset($historial)) {
    header("Location: ../controladores/usuarioController.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área de Usuario - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">
    
    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <?php
            try {
                $mensaje = $_SESSION["mensaje"] ?? null;
                if ($mensaje != null) {
                    unset($_SESSION["mensaje"]);
        ?>
            <div class="alert alert-info text-center rounded-4 shadow-sm mb-4"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php      } 

                // Cargamos datos
                $con = AccesoBD::getInstance();
                $u = $con->obtenerUsuarioBD($codigoLogueado);
                
                if ($u == null) {
                    echo "<div class='alert alert-danger text-center rounded-4'>Error: Perfil no encontrado. <a href='../controladores/logout.php'>Cerrar sesión</a></div>";
                } else {
                    $historial = $con->obtenerHistorialDetallado($codigoLogueado);
        ?>
            
            <div class="row justify-content-center mb-5">
                <!-- FORMULARIO DE PERFIL ORIGINAL, NUEVO DISEÑO -->
                <div class="col-lg-10">
                    <div class="card shadow-lg border-0">
                        <div class="card-body py-5 px-4 px-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="text-dark fw-bold mb-0">¡Bienvenido, <?php echo htmlspecialchars($u->getNombre() != null ? $u->getNombre() : "Cliente"); ?>!</h3>
                                <a href="productos.php" class="btn btn-primary rounded-pill shadow-sm d-none d-md-block">Nuevo Envío 📦</a>
                            </div>
                            
                            <!-- Formulario Original de Modificación -->
                            <form action="../controladores/modificarUsuario.php" method="POST" class="text-start mt-4">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-semibold">Email (No modificable)</label>
                                        <input type="text" class="form-control rounded-3 bg-light border-0" value="<?php echo htmlspecialchars($u->getUsuario()); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Teléfono</label>
                                        <input type="tel" class="form-control rounded-3" name="telefono" value="<?php echo htmlspecialchars($u->getTelefono() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Nombre</label>
                                        <input type="text" class="form-control rounded-3" name="nombre" value="<?php echo htmlspecialchars($u->getNombre() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Apellidos</label>
                                        <input type="text" class="form-control rounded-3" name="apellidos" value="<?php echo htmlspecialchars($u->getApellidos() ?? ""); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Dirección Principal</label>
                                        <input type="text" class="form-control rounded-3" name="domicilio" value="<?php echo htmlspecialchars($u->getDomicilio() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-semibold">Población</label>
                                        <input type="text" class="form-control rounded-3" name="poblacion" value="<?php echo htmlspecialchars($u->getPoblacion() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Provincia</label>
                                        <input type="text" class="form-control rounded-3" name="provincia" value="<?php echo htmlspecialchars($u->getProvincia() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">C.P.</label>
                                        <input type="text" class="form-control rounded-3" name="cp" value="<?php echo htmlspecialchars($u->getCp() ?? ""); ?>">
                                    </div>
                                </div>

                                <div class="bg-light p-4 rounded-4 mt-4 mb-4">
                                    <h6 class="text-secondary mb-1 fw-bold">Seguridad y Contraseña</h6>
                                    <p class="text-muted small mb-3"><em>* Déjalo en blanco si no quieres cambiar tu contraseña actual.</em></p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="password" class="form-control rounded-3 border-0 shadow-sm" id="mod_pass1" name="clave1" placeholder="Nueva Contraseña">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" class="form-control rounded-3 border-0 shadow-sm" id="mod_pass2" name="clave2" placeholder="Repetir Contraseña">
                                        </div>
                                    </div>
                                    <div id="errorModPass" class="text-danger small mt-2 d-none fw-medium">⚠️ Las contraseñas no coinciden.</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="#" onclick="limpiarCarritoLocal(event)" class="text-danger text-decoration-none fw-semibold">Cerrar Sesión</a>
                                    <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm fw-bold">Guardar Perfil</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN HISTORIAL ORIGINAL, REDISEÑADA -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h4 class="mb-4 text-dark fw-bold">📦 Historial de Pedidos</h4>

                    <?php if (empty($historial)) { ?>
                        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                            <span>🛣️</span>
                            <p class="text-muted mt-3 mb-0">Aún no has solicitado ningún reparto.</p>
                        </div>
                    <?php  } else { ?>
                        
                        <div class="accordion shadow-sm" id="acordeonPedidos">
                            <?php foreach ($historial as $ped) { 
                                // Color del badge dinámico
                                $estado = $ped->getEstado();
                                $badgeClass = (strcasecmp($estado, 'Entregado') == 0) ? 'bg-success' : ((strcasecmp($estado, 'En Ruta') == 0 || strcasecmp($estado, 'Enviado') == 0) ? 'bg-primary' : 'bg-warning text-dark');
                            ?>
                            
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header" id="heading<?php echo $ped->getId(); ?>">
                                    <button class="accordion-button collapsed bg-white text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $ped->getId(); ?>">
                                        <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                            <div>
                                                <span class="fw-bold">Pedido #<?php echo $ped->getId(); ?></span>
                                                <small class="text-muted ms-2 d-none d-md-inline">(<?php echo $ped->getFecha(); ?>)</small>
                                            </div>
                                            <div>
                                                <span class="badge rounded-pill <?php echo $badgeClass; ?> me-3"><?php echo htmlspecialchars($estado); ?></span>
                                                <span class="text-success fw-bold"><?php echo number_format((float)$ped->getImporteTotal(), 2, '.', ''); ?>€</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $ped->getId(); ?>" class="accordion-collapse collapse" data-bs-parent="#acordeonPedidos">
                                    <div class="accordion-body bg-light p-4">
                                        <table class="table table-borderless bg-white rounded-3 shadow-sm overflow-hidden mb-0">
                                            <thead class="table-light text-secondary small text-uppercase">
                                                <tr>
                                                    <th class="ps-3">Servicio</th>
                                                    <th class="text-center">Unidades</th>
                                                    <th class="text-end pe-3">Precio Unid.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Bucle original de los detalles -->
                                                <?php foreach ($ped->getDetalles() as $linea) { ?>
                                                <tr class="border-bottom">
                                                    <td class="ps-3 fw-medium"><?php echo htmlspecialchars($linea->getProducto()->getDescripcion()); ?></td>
                                                    <td class="text-center text-muted"><?php echo $linea->getCantidad(); ?></td>
                                                    <td class="text-end pe-3"><?php echo number_format((float)$linea->getPrecio(), 2, '.', ''); ?>€</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        
                                        <!-- Lógica original de Cancelar Pedido -->
                                        <?php if (strcasecmp($ped->getEstado(), "Pendiente") == 0) { ?>
                                            <div class="text-end mt-3">
                                                <form action="../controladores/cancelarPedido.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres cancelar este pedido? Se liberará el stock.');">
                                                    <!-- Token de seguridad -->
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <!-- ID del pedido a cancelar -->
                                                    <input type="hidden" name="id_pedido" value="<?php echo $ped->getId(); ?>">
                                                    
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                        Cancelar Envío ❌
                                                    </button>
                                                </form>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php } ?>
                        </div>
                    <?php  } ?>
                </div>
            </div>

        <?php      
            } // fin del else de perfil no encontrado
            } catch (Exception $e) {
                echo "<div class='alert alert-danger rounded-4 mt-5 p-4 shadow-sm'><h4>⚠️ Error detectado</h4><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
            }
        ?>
    </main>

    <?php include '../includes/pie.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/logica.js"></script>
</body>
</html>