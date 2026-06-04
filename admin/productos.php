<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

// LÓGICA PARA ELIMINAR PRODUCTO EN EL MISMO ARCHIVO
if (isset($_GET['eliminar'])) {
    $idEliminar = filter_input(INPUT_GET, 'eliminar', FILTER_VALIDATE_INT);
    if ($idEliminar) {
        // Asegúrate de que esta función existe en tu AccesoBD.php
        $con->eliminarProducto($idEliminar); 
        header("Location: productos.php?msg=borrado");
        exit;
    }
}

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

// OBTENER LISTA PARA MOSTRAR (AHORA PAGINADA)
$limite = 5; // Productos que quieres mostrar por página
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) {
    $paginaActual = 1;
}

$totalProductos = $con->contarProductos();
$totalPaginas = ceil($totalProductos / $limite);
$offset = ($paginaActual - 1) * $limite;

$listaProductos = $con->obtenerProductosPaginados($limite, $offset);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin: Servicios - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        /* Modernización de la paginación */
        .pagination .page-link { border: none; color: #2c3e50; font-weight: 500; border-radius: 8px; margin: 0 4px; }
        .pagination .page-item.active .page-link { background-color: #0d6efd; color: white; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2); }
    </style>
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-0">🏷️ Catálogo de Servicios</h3>
            <p class="text-muted small mt-1">Añade y edita las tarifas logísticas disponibles para los clientes.</p>
        </div>

        <?php if (isset($_SESSION['mensajeAdmin'])) { 
            $esError = strpos($_SESSION['mensajeAdmin'], '❌') !== false;
            $claseAlerta = $esError ? 'alert-danger text-danger-emphasis bg-danger-subtle' : 'alert-success text-success-emphasis bg-success-subtle';
        ?>
            <div class="alert <?= $claseAlerta ?> text-center rounded-4 shadow-sm border-0 mb-4 fw-medium">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>

        <!-- Formulario Alta Nuevo Producto -->
        <div class="card shadow-sm mb-5 border-0 rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                <h6 class="fw-bold text-primary text-uppercase mb-0" style="letter-spacing: 0.5px;">➕ Crear Nueva Tarifa</h6>
            </div>
            <div class="card-body p-4">
                <form action="productos.php" method="POST" class="row g-3">
                    <input type="hidden" name="accion" value="nuevo">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">Nombre del Servicio</label>
                        <input type="text" class="form-control bg-light border-0 rounded-3 shadow-none" name="descripcion" placeholder="Ej: Envío Express" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Precio (€)</label>
                        <input type="number" step="0.01" class="form-control bg-light border-0 rounded-3 shadow-none" name="precio" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Stock/Cupos</label>
                        <input type="number" class="form-control bg-light border-0 rounded-3 shadow-none" name="existencias" placeholder="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">Color Destacado</label>
                        <select class="form-select bg-light border-0 rounded-3 shadow-none" name="colorCss" required>
                            <option value="dark">Oscuro (Estándar)</option>
                            <option value="primary">Azul (Recomendado)</option>
                            <option value="success">Verde (Ecológico)</option>
                            <option value="warning">Amarillo (Urgente)</option>
                        </select>
                    </div>
                    <div class="col-md-10">
                        <label class="form-label text-muted small fw-bold">Características (Puntos clave permitiendo &lt;li&gt;)</label>
                        <input type="text" class="form-control bg-light border-0 rounded-3 shadow-none" name="caracteristicas" placeholder="<li>Entrega 24h</li><li>Seguro incluido</li>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold shadow-sm">Añadir</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado y Edición Editable -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0">Ref</th>
                            <th class="py-3 border-0">Descripción del Servicio</th>
                            <th class="py-3 border-0" style="width: 120px;">Precio (€)</th>
                            <th class="py-3 border-0" style="width: 120px;">Cupos</th>
                            <th class="py-3 border-0" style="width: 150px;">Color Visual</th>
                            <th class="pe-4 py-3 border-0 text-end">Guardar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaProductos as $p) { ?>
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <form action="productos.php" method="POST">
                                    <input type="hidden" name="accion" value="editar">
                                    <input type="hidden" name="idProducto" value="<?= $p->getId() ?>">
                                    <input type="hidden" name="caracteristicas" value="<?= htmlspecialchars($p->getCaracteristicas()) ?>">
                                    
                                    <td class="ps-4 fw-bold text-dark">#<?= $p->getId() ?></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm bg-light border-0 rounded-3 shadow-none fw-medium text-dark" name="descripcion" value="<?= htmlspecialchars($p->getDescripcion()) ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control form-control-sm bg-light border-0 rounded-3 shadow-none fw-bold text-success" name="precio" value="<?= $p->getPrecio() ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm bg-light border-0 rounded-3 shadow-none fw-medium text-secondary" name="existencias" value="<?= $p->getExistencias() ?>">
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm bg-light border-0 rounded-3 shadow-none text-muted" name="colorCss">
                                            <option value="dark" <?= $p->getColorCss() == 'dark' ? 'selected' : '' ?>>Oscuro</option>
                                            <option value="primary" <?= $p->getColorCss() == 'primary' ? 'selected' : '' ?>>Azul</option>
                                            <option value="success" <?= $p->getColorCss() == 'success' ? 'selected' : '' ?>>Verde</option>
                                            <option value="warning" <?= $p->getColorCss() == 'warning' ? 'selected' : '' ?>>Amarillo</option>
                                        </select>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm">Actualizar</button>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button href="productos.php?eliminar=<?= $producto->getId() ?>"
                                            class="btn btn-outline-danger btn-sm rounded-pill shadow-sm fw-bold" 
                                            onclick="return confirm('¿Seguro que quieres eliminar este servicio?');"> eliminar</button>
                                    </td>
                                </form>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación Bootstrap (Si hay más de una página) -->
            <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white border-0 py-4 d-flex justify-content-center">
                <nav aria-label="Navegación del catálogo">
                    <ul class="pagination mb-0">
                        <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link shadow-sm" href="?pagina=<?= $paginaActual - 1 ?>">← Ant</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= ($paginaActual == $i) ? 'active' : '' ?>">
                                <a class="page-link shadow-sm" href="?pagina=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link shadow-sm" href="?pagina=<?= $paginaActual + 1 ?>">Sig →</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>