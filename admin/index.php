<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

// 1. SEGURIDAD: Comprobamos que está logueado y que su ROL es Administrador (Ej: rol = 1)
// Si no es admin, lo echamos al login del admin
if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    header("Location: login.html");
    exit;
}

// 2. OBTENER DATOS: Sacamos los pedidos pendientes y los repartidores disponibles
$pedidosPendientes = $con->obtenerPedidosPendientes();
$repartidores = $con->obtenerRepartidores();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">⚙️ LogisTFG - Panel de Administración</span>
            <span class="navbar-text text-white">
                Admin: <?= htmlspecialchars($usuarioActual->getNombre()) ?> | <a href="../controladores/logout.php" class="text-danger">Cerrar Sesión</a>
            </span>
        </div>
    </nav>

    <main class="container my-5">
        <?php if (isset($_SESSION['mensajeAdmin'])) { ?>
            <div class="alert alert-success text-center">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>

        <h3 class="mb-4 text-primary">📦 Pedidos Pendientes de Asignar</h3>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($pedidosPendientes)) { ?>
                    <div class="alert alert-info">No hay pedidos pendientes en el almacén.</div>
                <?php } else { ?>
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th># Pedido</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Dirección Destino</th>
                                <th>Asignar a Repartidor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidosPendientes as $ped) { ?>
                                <tr>
                                    <td><strong><?= $ped['id'] ?></strong></td>
                                    <td><?= $ped['fecha'] ?></td>
                                    <td><?= htmlspecialchars($ped['cliente']) ?></td>
                                    <td>📍 <?= htmlspecialchars($ped['destino']) ?></td>
                                    <td>
                                        <!-- Formulario para asignar el repartidor -->
                                        <form action="../controladores/asignarRepartidor.php" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="idPedido" value="<?= $ped['id'] ?>">
                                            <select name="idRepartidor" class="form-select form-select-sm" required>
                                                <option value="" disabled selected>Selecciona repartidor...</option>
                                                <?php foreach ($repartidores as $rep) { ?>
                                                    <option value="<?= $rep['id'] ?>"><?= htmlspecialchars($rep['nombre'] . ' ' . $rep['apellidos']) ?></option>
                                                <?php } ?>
                                            </select>
                                            <button type="submit" class="btn btn-success btn-sm">Asignar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
    </main>
</body>
</html>