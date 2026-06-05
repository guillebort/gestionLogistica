<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Envíos - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container-fluid px-4 px-lg-5 my-5">
        
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">📦 Historial de Envíos</h3>
                <p class="text-muted mb-0 small">Auditoría global de rutas, filtrado de paquetes y emisión de albaranes.</p>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                Resultados: <?= count($pedidosFiltrados) ?> pedidos
            </span>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-white rounded-4">
            <div class="card-body p-4">
                <form action="../controladoresAdmin/historialPedidosController.php" method="GET" class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">👤 Cliente</label>
                        <select name="idUsuario" class="form-select bg-light border-0">
                            <option value="">Todos los clientes...</option>
                            <?php foreach($usuarios as $u) { 
                                $columnaRol = isset($u['id_rol']) ? $u['id_rol'] : (isset($u['rol']) ? $u['rol'] : null);
                                if ($columnaRol != 0 && strtolower((string)$columnaRol) != 'cliente') continue; 
                            ?>
                                <option value="<?= $u['id'] ?>" <?= (isset($_GET['idUsuario']) && $_GET['idUsuario'] == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">🏷️ Servicio</label>
                        <select name="idProducto" class="form-select bg-light border-0">
                            <option value="">Cualquier servicio...</option>
                            <?php foreach($productos as $p) { ?>
                                <option value="<?= $p->getId() ?>" <?= (isset($_GET['idProducto']) && $_GET['idProducto'] == $p->getId()) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->getDescripcion()) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">📅 Búsqueda por Fecha</label>
                        <div class="input-group">
                            <select name="operadorFecha" class="form-select bg-light border-0" style="max-width: 130px;">
                                <option value="=" <?= (isset($_GET['operadorFecha']) && $_GET['operadorFecha'] == '=') ? 'selected' : '' ?>>Exacto</option>
                                <option value=">=" <?= (isset($_GET['operadorFecha']) && $_GET['operadorFecha'] == '>=') ? 'selected' : '' ?>>Desde</option>
                                <option value="<=" <?= (isset($_GET['operadorFecha']) && $_GET['operadorFecha'] == '<=') ? 'selected' : '' ?>>Hasta</option>
                            </select>
                            <input type="date" name="fecha" class="form-control bg-light border-0" value="<?= isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '' ?>">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm">🔍 Filtrar</button>
                        <a href="../controladoresAdmin/historialPedidosController.php" class="btn btn-light rounded-pill border" title="Limpiar todo">✖️</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 overflow-hidden rounded-4">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0"># REF</th>
                            <th class="py-3 border-0">Fecha Emisión</th>
                            <th class="py-3 border-0">Cliente Origen</th>
                            <th class="py-3 border-0 text-center">Estado Operativo</th>
                            <th class="py-3 border-0 text-end">Importe Facturado</th>
                            <th class="pe-4 py-3 border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidosFiltrados)) { 
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'>
                                    <div style='font-size: 2.5rem; opacity: 0.5;'>📭</div>
                                    <p class='mt-2 fw-medium'>No hay coincidencias con los filtros aplicados.</p>
                                  </td></tr>"; 
                        } ?>
                        <?php foreach ($pedidosFiltrados as $pf) { 
                            $estadoStr = strtolower($pf['estado_nombre']);
                            if ($estadoStr == 'entregado') {
                                $claseBadge = 'bg-success text-success-emphasis bg-opacity-10 border border-success-subtle';
                                $icono = '✔️';
                            } elseif ($estadoStr == 'cancelado') {
                                $claseBadge = 'bg-danger text-danger-emphasis bg-opacity-10 border border-danger-subtle';
                                $icono = '❌';
                            } elseif ($estadoStr == 'en ruta' || $estadoStr == 'enviado') {
                                $claseBadge = 'bg-primary text-primary-emphasis bg-opacity-10 border border-primary-subtle';
                                $icono = '🚚';
                            } else {
                                $claseBadge = 'bg-warning text-warning-emphasis bg-opacity-25 border border-warning-subtle';
                                $icono = '🕒';
                            }
                        ?>
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="ps-4 fw-bold text-dark font-monospace">#<?= $pf['id'] ?></td>
                                <td><div class="text-secondary small fw-semibold"><?= date('d/m/Y', strtotime($pf['fecha'])) ?></div></td>
                                <td>
                                    <div class="fw-medium text-dark d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-circle text-center d-flex justify-content-center align-items-center" style="width: 28px; height:28px;">👤</div>
                                        <?= htmlspecialchars($pf['cliente']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $claseBadge ?>">
                                        <?= $icono ?> <?= htmlspecialchars($pf['estado_nombre']) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    <?= number_format($pf['importe'], 2) ?> €
                                </td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm btn-imprimir-albaran" data-id="<?= $pf['id'] ?>">
                                        🖨️ PDF
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/logicaAdmin.js"></script>
</body>
</html>