<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

// SEGURIDAD: Comprobamos que está logueado y que su ROL es Administrador
if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    header("Location: ../tienda/loginTienda.php");
    exit;
}

// PROCESAR FORMULARIOS (Alta o Modificación)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $existencias = filter_input(INPUT_POST, 'existencias', FILTER_VALIDATE_INT);
    $caracteristicas = $_POST['caracteristicas'] ?? ''; // Permite HTML básico como <li>
    $colorCss = filter_input(INPUT_POST, 'colorCss', FILTER_SANITIZE_STRING);
    
    if ($accion === 'nuevo') {
        $imagen = "default.jpg"; // Por defecto
        $exito = $con->agregarProductoBD($descripcion, $precio, $existencias, $imagen, $caracteristicas, $colorCss);
        $_SESSION['mensajeAdmin'] = $exito ? "✅ Servicio añadido correctamente." : "❌ Error al añadir servicio.";
    } elseif ($accion === 'editar') {
        $idProd = filter_input(INPUT_POST, 'idProducto', FILTER_VALIDATE_INT);
        $exito = $con->modificarProductoBD($idProd, $descripcion, $precio, $existencias, $caracteristicas, $colorCss);
        $_SESSION['mensajeAdmin'] = $exito ? "✅ Servicio actualizado (Recuerda: Existencias 0 = Oculto/Borrado)." : "❌ Error al actualizar.";
    }
    
    header("Location: productos.php");
    exit;
}

// OBTENER LISTA PARA MOSTRAR
$listaProductos = $con->obtenerProductosBD();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin: Servicios - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">⚙️ LogisTFG - Gestión de Catálogo</span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Volver a Pedidos</a>
                <span class="text-white me-3">Admin: <?= htmlspecialchars($usuarioActual->getNombre()) ?></span>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <?php if (isset($_SESSION['mensajeAdmin'])) { ?>
            <div class="alert alert-info text-center fw-bold">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>

        <!-- Formulario Alta Nuevo Producto -->
        <div class="card shadow-sm mb-5 border-primary">
            <div class="card-header bg-primary text-white">Añadir Nuevo Servicio/Tarifa</div>
            <div class="card-body">
                <form action="productos.php" method="POST" class="row g-3">
                    <input type="hidden" name="accion" value="nuevo">
                    <div class="col-md-4">
                        <label class="form-label">Nombre del Servicio</label>
                        <input type="text" class="form-control" name="descripcion" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio (€)</label>
                        <input type="number" step="0.01" class="form-control" name="precio" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Stock/Cupos</label>
                        <input type="number" class="form-control" name="existencias" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color CSS (primary, dark, success...)</label>
                        <input type="text" class="form-control" name="colorCss" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Características (HTML permitido, ej: &lt;li&gt;Rápido&lt;/li&gt;)</label>
                        <textarea class="form-control" name="caracteristicas" rows="2"></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Añadir Servicio</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado y Edición -->
        <h3 class="mb-3">Catálogo Actual</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Existencias</th>
                        <th>Color CSS</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listaProductos as $p) { ?>
                        <tr>
                            <form action="productos.php" method="POST">
                                <input type="hidden" name="accion" value="editar">
                                <input type="hidden" name="idProducto" value="<?= $p->getId() ?>">
                                
                                <td><?= $p->getId() ?></td>
                                <td><input type="text" class="form-control form-control-sm" name="descripcion" value="<?= htmlspecialchars($p->getDescripcion()) ?>"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" name="precio" value="<?= $p->getPrecio() ?>"></td>
                                <td><input type="number" class="form-control form-control-sm" name="existencias" value="<?= $p->getExistencias() ?>"></td>
                                <td><input type="text" class="form-control form-control-sm" name="colorCss" value="<?= htmlspecialchars($p->getColorCss()) ?>"></td>
                                <!-- Ocultamos las características en la tabla para no saturar, pero se envían -->
                                <input type="hidden" name="caracteristicas" value="<?= htmlspecialchars($p->getCaracteristicas()) ?>">
                                <td>
                                    <button type="submit" class="btn btn-warning btn-sm">Guardar</button>
                                </td>
                            </form>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>