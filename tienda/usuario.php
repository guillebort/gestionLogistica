<?php
    session_start();
    require_once '../modelos/AccesoBD.php';
    require_once '../modelos/Modelos.php';

    // REDIRECCIÓN MAESTRA: Si no está logueado, lo mandamos al login.
    $codigoLogueado = $_SESSION["codigo"] ?? 0;
    if ($codigoLogueado <= 0) {
        header("Location: loginUsuario.php");
        exit;
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
    
    <mi-cabecera></mi-cabecera>
    
    <?php 
        $nombreMenu = $_SESSION["nombreUsuario"] ?? null; 
        $nombreData = ($nombreMenu != null) ? $nombreMenu : "";
    ?>
    <mi-menu data-user="<?php echo htmlspecialchars($nombreData); ?>"></mi-menu>

    <main class="container my-5">
        <?php
            try {
                $mensaje = $_SESSION["mensaje"] ?? null;
                if ($mensaje != null) {
                    unset($_SESSION["mensaje"]);
        ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php      } 

                // Como ya sabemos que está logueado, cargamos sus datos directamente
                $con = AccesoBD::getInstance();
                $u = $con->obtenerUsuarioBD($codigoLogueado);
                
                if ($u == null) {
                    echo "<div class='alert alert-danger text-center'>Error: Perfil no encontrado. <a href='../controladores/logout.php'>Cerrar sesión</a></div>";
                } else {
                    $historial = $con->obtenerHistorialDetallado($codigoLogueado);
        ?>
            
            <div class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <div class="card shadow-sm border-success">
                        <div class="card-body text-center py-5">
                            <h2 class="text-success mb-4">¡Bienvenido, <?php echo htmlspecialchars($u->getNombre() != null ? $u->getNombre() : "Cliente"); ?>!</h2>
                            
                            <form action="../controladores/modificarUsuario.php" method="POST" onsubmit="return validarModificacion()" class="text-start">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Email (No modificable)</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($u->getUsuario()); ?>" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" name="telefono" value="<?php echo htmlspecialchars($u->getTelefono() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($u->getNombre() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" name="apellidos" value="<?php echo htmlspecialchars($u->getApellidos() ?? ""); ?>">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Dirección Principal</label>
                                        <input type="text" class="form-control" name="domicilio" value="<?php echo htmlspecialchars($u->getDomicilio() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Población</label>
                                        <input type="text" class="form-control" name="poblacion" value="<?php echo htmlspecialchars($u->getPoblacion() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Provincia</label>
                                        <input type="text" class="form-control" name="provincia" value="<?php echo htmlspecialchars($u->getProvincia() ?? ""); ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">C.P.</label>
                                        <input type="text" class="form-control" name="cp" value="<?php echo htmlspecialchars($u->getCp() ?? ""); ?>">
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h5 class="text-secondary mb-3">Seguridad y Contraseña</h5>
                                <p class="text-muted small"><em>* Déjalo en blanco si no quieres cambiar tu contraseña actual.</em></p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="mod_pass1" name="clave1" placeholder="Mínimo 4 caracteres">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Repetir Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="mod_pass2" name="clave2" placeholder="Confirma la contraseña">
                                    </div>
                                </div>
                                <div id="errorModPass" class="alert alert-danger d-none py-2 text-center">
                                    ⚠️ Las contraseñas no coinciden.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                                    <button type="submit" class="btn btn-success px-4">Guardar Cambios</button>
                                    <a href="productos.php" class="btn btn-primary px-4">Ir a Tarifas</a>
                                    <a href="../controladores/logout.php" class="btn btn-outline-danger px-4">Cerrar Sesión</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container my-5">
                <h3 class="mb-4 text-primary">📦 Historial de Pedidos</h3>

                <?php
                    if (empty($historial)) {
                ?>
                    <div class="alert alert-info">Aún no has realizado ningún pedido.</div>
                <?php  } else { ?>
                    
                    <div class="accordion shadow-sm" id="acordeonPedidos">
                        <?php foreach ($historial as $ped) { ?>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $ped->getId(); ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $ped->getId(); ?>">
                                    <div class="d-flex justify-content-between w-100 pe-3">
                                        <span><strong>Pedido #<?php echo $ped->getId(); ?></strong> (<?php echo $ped->getFecha(); ?>)</span>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($ped->getEstado()); ?></span>
                                        <span class="text-success fw-bold"><?php echo number_format((float)$ped->getImporteTotal(), 2, '.', ''); ?>€</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $ped->getId(); ?>" class="accordion-collapse collapse" data-bs-parent="#acordeonPedidos">
                                <div class="accordion-body bg-light">
                                    <table class="table table-sm table-bordered bg-white mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Unidades</th>
                                                <th class="text-end">Precio Unid.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ped->getDetalles() as $linea) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($linea->getProducto()->getDescripcion()); ?></td>
                                                <td class="text-center"><?php echo $linea->getCantidad(); ?></td>
                                                <td class="text-end"><?php echo number_format((float)$linea->getPrecio(), 2, '.', ''); ?>€</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php if (strcasecmp($ped->getEstado(), "Pendiente") == 0) { ?>
                                        <div class="text-end mt-3">
                                            <a href="../controladores/cancelarPedido.php?id=<?php echo $ped->getId(); ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('¿Estás seguro de que quieres cancelar este pedido?');">
                                               Cancelar Pedido
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php } ?>
                    </div>
                <?php  } ?>
            </div>

        <?php      } 
            } catch (Exception $e) {
                echo "<div class='alert alert-danger mt-5 p-4'><h4>⚠️ Error detectado</h4><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
            }
        ?>
    </main>

    <mi-pie></mi-pie>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/mis-etiquetas.js"></script>
    <script src="../js/logica.js"></script>
</body>
</html>