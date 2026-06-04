<?php
require_once '../modelos/AccesoBD.php';
session_start();
$con = AccesoBD::getInstance();
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

// Recogemos parámetros GET para el filtro
$idUsuario = filter_input(INPUT_GET, 'idUsuario', FILTER_VALIDATE_INT);
$idProducto = filter_input(INPUT_GET, 'idProducto', FILTER_VALIDATE_INT);
$fecha = filter_input(INPUT_GET, 'fecha', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$operadorFecha = $_GET['operadorFecha'] ?? '=';

// Listas para rellenar los desplegables (selects)
$usuarios = $con->obtenerTodosLosUsuarios(); 
$productos = $con->obtenerProductosBD();

// Obtener la tabla filtrada (ya no pasamos la variable lógica $logica)
$pedidosFiltrados = $con->obtenerPedidosFiltrados($idUsuario, $idProducto, $fecha, $operadorFecha);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Envíos - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    
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

        <!-- FORMULARIO DE FILTRADO OPTIMIZADO -->
     <div class="card shadow-sm border-0 mb-4 bg-white">
    <div class="card-body p-4">
        <form action="historialPedidos.php" method="GET" class="row g-3 align-items-end">
            
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">👤 Cliente</label>
                <select name="idUsuario" class="form-select">
                    <option value="">Todos los clientes...</option>
                    <?php foreach($usuarios as $u) { 
                        $columnaRol = isset($u['id_rol']) ? $u['id_rol'] : (isset($u['rol']) ? $u['rol'] : null);
                        if ($columnaRol != 0 && strtolower((string)$columnaRol) != 'cliente') {
                            continue; 
                        }
                    ?>
                        <option value="<?= $u['id'] ?>" <?= (isset($_GET['idUsuario']) && $_GET['idUsuario'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">🏷️ Servicio</label>
                <select name="idProducto" class="form-select">
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
                    <select name="operadorFecha" class="form-select" style="max-width: 130px;">
                        <option value="=" <?= (isset($_GET['operadorFecha']) && $_GET['operadorFecha'] == '=') ? 'selected' : '' ?>>Exacto</option>
                        <option value=">=" <?= (isset($_GET['operadorFecha']) && $_GET['operadorFecha'] == '>=') ? 'selected' : '' ?>>Desde</option>
                        <option value="<=" <?= (isset($_GET['operadorFecha']) && $_GET['operadorFecha'] == '<=') ? 'selected' : '' ?>>Hasta</option>
                    </select>
                    <input type="date" name="fecha" class="form-control" value="<?= isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '' ?>">
                </div>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">🔍 Filtrar</button>
                <a href="historialPedidos.php" class="btn btn-light border" title="Limpiar todo">✖️</a>
            </div>

        </form>
    </div>
</div>
        <!-- TABLA DE RESULTADOS -->
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead>
                        <tr>
                            <th># REF</th>
                            <th>Fecha Emisión</th>
                            <th>Cliente Origen</th>
                            <th>Estado Operativo</th>
                            <th>Importe Facturado</th>
                            <th class="text-end">Acciones</th>
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
                            // Lógica visual del badge según el estado logístico
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
                            <tr>
                                <td class="ps-4 fw-bold text-dark font-monospace">#<?= $pf['id'] ?></td>
                                <td>
                                    <div class="text-secondary small fw-semibold"><?= date('d/m/Y', strtotime($pf['fecha'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-circle text-center" style="width: 28px; height:28px; line-height:28px;">👤</div>
                                        <?= htmlspecialchars($pf['cliente']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2 fw-semibold <?= $claseBadge ?>">
                                        <?= $icono ?> <?= htmlspecialchars($pf['estado_nombre']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    <?= number_format($pf['importe'], 2) ?> €
                                </td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" onclick="imprimirAlbaran(<?= $pf['id'] ?>)">
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
    <script src="../js/logica.js"></script>
</body>
</html>