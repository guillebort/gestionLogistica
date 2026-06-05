<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">

    <?php include '../includes/menuAdmin.php'; ?>

    <div class="container-fluid my-5 px-4 px-lg-5">
        <div class="row">
            <main class="col-12">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                    <h1 class="h3 fw-bold text-dark">📦 Gestión del Catálogo de Tarifas</h1>
                </div>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
                        <?= $mensaje ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mb-3 text-secondary">Añadir Tarifa/Servicio</h5>
                                
                                <form action="../controladoresAdmin/productosController.php" method="POST">
                                    <input type="hidden" name="accion" value="guardar_producto">
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Nombre del Servicio</label>
                                        <input type="text" name="nombre" class="form-control bg-light border-0 py-2" placeholder="Ej: Envío Premium 24h" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Precio de Entrega (€)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="precio" class="form-control bg-light border-0 py-2" placeholder="0.00" required>
                                            <span class="input-group-text bg-light border-0">€</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted">Características (Una por línea)</label>
                                        <textarea class="form-control bg-light border-0" name="caracteristicas" rows="4" placeholder="Entrega antes de las 14:00&#10;Seguro a todo riesgo incluído&#10;Seguimiento en tiempo real" required></textarea>
                                        <div class="form-text text-muted small">Pulsa Intro para añadir una nueva característica.</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-pill">
                                        💾 Guardar en Catálogo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-secondary small fw-bold text-uppercase">
                                            <tr>
                                                <th class="ps-4 border-0">ID</th>
                                                <th class="border-0">Servicio</th>
                                                <th class="border-0">Especificaciones Técnicas</th>
                                                <th class="border-0">Precio</th>
                                                <th class="text-end pe-4 border-0">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($listaProductos)): ?>
                                                <?php foreach ($listaProductos as $p): 
                                                    $id = is_array($p) ? $p['id'] : $p->getId();
                                                    $nombre = is_array($p) ? $p['nombre'] : $p->getDescripcion();
                                                    $precio = is_array($p) ? $p['precio'] : $p->getPrecio();
                                                    $caracts = is_array($p) ? $p['caracteristicas'] : $p->getCaracteristicas();
                                                ?>
                                                <tr class="border-bottom border-secondary border-opacity-10">
                                                    <td class="ps-4 fw-bold text-muted">#<?= $id ?></td>
                                                    <td><span class="fw-bold text-dark"><?= htmlspecialchars($nombre) ?></span></td>
                                                    <td>
                                                        <ul class="mb-0 text-muted small ps-3">
                                                            <?= $caracts ?: '<li>Sin especificaciones</li>' ?>
                                                        </ul>
                                                    </td>
                                                    <td><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle fw-bold px-2 py-2 fs-6 rounded-pill"><?= number_format($precio, 2, ',', '.') ?> €</span></td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold btn-editar-producto" 
                                                                    data-id="<?= $id ?>" 
                                                                    data-nombre="<?= htmlspecialchars($nombre) ?>" 
                                                                    data-precio="<?= $precio ?>" 
                                                                    data-caracteristicas="<?= htmlspecialchars($caracts) ?>">
                                                               Editar
                                                            </button>
                                                            <a href="../controladoresAdmin/productosController.php?eliminar=<?= $id ?>" 
                                                               class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-eliminar-producto">
                                                               Eliminar
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-5">
                                                        <div class="fs-1 opacity-50 mb-2">📦</div>
                                                        No hay servicios de reparto configurados en el catálogo.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">📝 Modificar Servicio / Tarifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../controladoresAdmin/productosController.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="accion" value="actualizar_producto">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre del Servicio</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control bg-light border-0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Precio (€)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="precio" id="edit_precio" class="form-control bg-light border-0" required>
                                <span class="input-group-text bg-light border-0">€</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Características (Una por línea)</label>
                            <textarea class="form-control bg-light border-0" name="caracteristicas" id="edit_caracteristicas" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/logicaAdmin.js"></script>
</body>
</html>