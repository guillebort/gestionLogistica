<div class="container-fluid">
    <div class="row">
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2 fw-bold text-dark">📦 Gestión del Catálogo de Tarifas</h1>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                    <?= $mensaje ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3 text-secondary">Añadir Tarifa/Servicio</h5>
                            
                            <form action="productos.php" method="POST">
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
                                
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                    💾 Guardar en Catálogo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-secondary small fw-bold">
                                        <tr>
                                            <th class="ps-4" style="width: 8%">ID</th>
                                            <th style="width: 25%">Servicio</th>
                                            <th style="width: 40%">Especificaciones Técnicas</th>
                                            <th style="width: 15%">Precio</th>
                                            <th class="text-end pe-4" style="width: 12%">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($listaProductos)): ?>
                                            <?php foreach ($listaProductos as $p): 
                                                // DETECCIÓN AUTOMÁTICA: Soporta tanto si el modelo devuelve Array u Objeto
                                                $id = is_array($p) ? $p['id'] : $p->getId();
                                                $nombre = is_array($p) ? $p['nombre'] : $p->getNombre();
                                                $precio = is_array($p) ? $p['precio'] : $p->getPrecio();
                                                $caracts = is_array($p) ? $p['caracteristicas'] : $p->getCaracteristicas();
                                            ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-muted">#<?= $id ?></td>
                                                <td><span class="fw-bold text-dark"><?= htmlspecialchars($nombre) ?></span></td>
                                                <td>
                                                    <ul class="mb-0 text-muted small ps-3">
                                                        <?= $caracts ?: '<li>Sin especificaciones</li>' ?>
                                                    </ul>
                                                </td>
                                                <td><span class="badge bg-success-subtle text-success fw-bold px-2 py-1 fs-6"><?= number_format($precio, 2, ',', '.') ?> €</span></td>
                                                <td class="text-end pe-4">
                                                    <a href="productos.php?eliminar=<?= $id ?>" 
                                                       class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-eliminar-producto">
                                                       Eliminar
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="bi bi-box-seam fs-2 d-block mb-2 text-black-50"></i>
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

<script src="../js/logicaAdmin.js"></script>

