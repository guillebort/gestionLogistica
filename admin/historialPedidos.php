<?php

require_once '../modelos/AccesoBD.php';
session_start();
$con = AccesoBD::getInstance();
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/loginUsuario.php");
    exit;
}

// Recogemos parámetros GET para el filtro
$idUsuario = filter_input(INPUT_GET, 'idUsuario', FILTER_VALIDATE_INT);
$idProducto = filter_input(INPUT_GET, 'idProducto', FILTER_VALIDATE_INT);
$fecha = filter_input(INPUT_GET, 'fecha', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$operadorFecha = $_GET['operadorFecha'] ?? '=';
$logica = $_GET['logica'] ?? 'AND';

// Listas para rellenar los desplegables (selects)
$usuarios = $con->obtenerTodosLosUsuarios(); // Usa la función que tienes en AccesoBD
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
</head>
<body class="bg-light">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        <!-- FORMULARIO DE FILTRADO -->
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-header bg-primary text-white">🔍 Filtros de Búsqueda Avanzada</div>
            <div class="card-body">
                <form action="historial_pedidos.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Usuario/Cliente</label>
                        <select name="idUsuario" class="form-select">
                            <option value="">Todos los clientes</option>
                            <?php foreach($usuarios as $u) { ?>
                                <option value="<?= $u['id'] ?>" <?= ($idUsuario == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Servicio/Producto</label>
                        <select name="idProducto" class="form-select">
                            <option value="">Todos los servicios</option>
                            <?php foreach($productos as $p) { ?>
                                <option value="<?= $p->getId() ?>" <?= ($idProducto == $p->getId()) ? 'selected' : '' ?>><?= htmlspecialchars($p->getDescripcion()) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Operador Fecha</label>
                        <select name="operadorFecha" class="form-select">
                            <option value="=" <?= ($operadorFecha == '=') ? 'selected' : '' ?>>Igual a</option>
                            <option value="<=" <?= ($operadorFecha == '<=') ? 'selected' : '' ?>>Menor o igual a</option>
                            <option value=">=" <?= ($operadorFecha == '>=') ? 'selected' : '' ?>>Mayor o igual a</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Condición Lógica</label>
                        <select name="logica" class="form-select">
                            <option value="AND" <?= ($logica == 'AND') ? 'selected' : '' ?>>Y (AND)</option>
                            <option value="OR" <?= ($logica == 'OR') ? 'selected' : '' ?>>O (OR)</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RESULTADOS -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th>Importe</th><th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidosFiltrados)) { echo "<tr><td colspan='6' class='text-center'>No hay resultados para estos filtros.</td></tr>"; } ?>
                        <?php foreach ($pedidosFiltrados as $pf) { ?>
                            <tr>
                                <td><strong><?= $pf['id'] ?></strong></td>
                                <td><?= $pf['fecha'] ?></td>
                                <td><?= htmlspecialchars($pf['cliente']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($pf['estado_nombre']) ?></span></td>
                                <td><?= $pf['importe'] ?> €</td>
                                <!-- ¡El botón debe ir dentro de un <td> para no romper la tabla! -->
                                <td>
                                    <button class="btn btn-outline-dark btn-sm" onclick="imprimirAlbaran(<?= $pf['id'] ?>, '<?= htmlspecialchars($pf['cliente']) ?>')">🖨️ Albarán</button>
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