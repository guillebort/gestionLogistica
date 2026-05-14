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
$logica = $_GET['logica'] ?? 'AND';

// Listas para rellenar los desplegables (selects)
$usuarios = $con->obtenerTodosLosUsuarios(); 
$productos = $con->obtenerProductosBD();

// Obtener la tabla filtrada
$pedidosFiltrados = $con->obtenerPedidosFiltrados($idUsuario, $idProducto, $fecha, $operadorFecha, $logica);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial y Filtros - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        
        <div class="mb-4">
            <h3 class="fw-bold text-dark">Historial de Envíos</h3>
            <p class="text-muted">Busca, filtra e imprime albaranes de cualquier pedido del sistema.</p>
        </div>

        <!-- FORMULARIO DE FILTRADO -->
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h6 class="fw-bold text-primary mb-0">🔍 Filtros Avanzados</h6>
            </div>
            <div class="card-body p-4">
                <form action="historialPedidos.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-medium">Usuario / Cliente</label>
                        <select name="idUsuario" class="form-select bg-light border-0 rounded-3 shadow-none">
                            <option value="">Todos los clientes</option>
                            <?php foreach($usuarios as $u) { ?>
                                <option value="<?= $u['id'] ?>" <?= ($idUsuario == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-medium">Servicio</label>
                        <select name="idProducto" class="form-select bg-light border-0 rounded-3 shadow-none">
                            <option value="">Todos</option>
                            <?php foreach($productos as $p) { ?>
                                <option value="<?= $p->getId() ?>" <?= ($idProducto == $p->getId()) ? 'selected' : '' ?>><?= htmlspecialchars($p->getDescripcion()) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-medium">Fecha</label>
                        <input type="date" name="fecha" class="form-control bg-light border-0 rounded-3 shadow-none" value="<?= htmlspecialchars($fecha) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-medium">Op. Fecha</label>
                        <select name="operadorFecha" class="form-select bg-light border-0 rounded-3 shadow-none">
                            <option value="=" <?= ($operadorFecha == '=') ? 'selected' : '' ?>>Igual a</option>
                            <option value="<=" <?= ($operadorFecha == '<=') ? 'selected' : '' ?>>Menor o igual a</option>
                            <option value=">=" <?= ($operadorFecha == '>=') ? 'selected' : '' ?>>Mayor o igual a</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-medium">Lógica</label>
                        <select name="logica" class="form-select bg-light border-0 rounded-3 shadow-none">
                            <option value="AND" <?= ($logica == 'AND') ? 'selected' : '' ?>>Y (AND)</option>
                            <option value="OR" <?= ($logica == 'OR') ? 'selected' : '' ?>>O (OR)</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary rounded-3 fw-bold shadow-sm">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RESULTADOS -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0"># Ref</th>
                            <th class="py-3 border-0">Fecha</th>
                            <th class="py-3 border-0">Cliente</th>
                            <th class="py-3 border-0">Estado</th>
                            <th class="py-3 border-0">Importe</th>
                            <th class="pe-4 py-3 border-0 text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidosFiltrados)) { echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No hay resultados para estos filtros.</td></tr>"; } ?>
                        <?php foreach ($pedidosFiltrados as $pf) { 
                            // Color dinámico para el estado
                            $estadoStr = strtolower($pf['estado_nombre']);
                            if ($estadoStr == 'entregado') $claseBadge = 'bg-success text-success-emphasis bg-opacity-10 border border-success-subtle';
                            elseif ($estadoStr == 'cancelado') $claseBadge = 'bg-danger text-danger-emphasis bg-opacity-10 border border-danger-subtle';
                            else $claseBadge = 'bg-primary text-primary-emphasis bg-opacity-10 border border-primary-subtle';
                        ?>
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="ps-4 fw-bold text-dark">#<?= $pf['id'] ?></td>
                                <td class="text-secondary small fw-medium"><?= date('d/m/Y', strtotime($pf['fecha'])) ?></td>
                                <td>
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($pf['cliente']) ?></div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2 fw-medium <?= $claseBadge ?>">
                                        <?= htmlspecialchars($pf['estado_nombre']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark"><?= number_format($pf['importe'], 2) ?> €</td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-medium" onclick="imprimirAlbaran(<?= $pf['id'] ?>, '<?= htmlspecialchars($pf['cliente']) ?>')">
                                        🖨️ Albarán
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="../js/logica.js"></script>
</body>
</html>