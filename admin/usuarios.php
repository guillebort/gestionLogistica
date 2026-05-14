<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

// Control de flujo: Solo Administradores (rol = 1)
$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

// Lógica para Activar/Desactivar, Eliminar o Cambiar Rol
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
    $idUsu = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if ($accion === 'toggle_activo') {
        $estadoActual = filter_input(INPUT_POST, 'estado_actual', FILTER_VALIDATE_INT);
        $nuevoEstado = ($estadoActual == 1) ? 0 : 1;
        $con->cambiarEstadoUsuario($idUsu, $nuevoEstado);
        $_SESSION['mensajeAdmin'] = "✅ Estado del usuario actualizado.";
        
    } elseif ($accion === 'eliminar') {
        if ($con->eliminarUsuarioSiSinPedidos($idUsu)) {
            $_SESSION['mensajeAdmin'] = "✅ Usuario eliminado correctamente.";
        } else {
            $_SESSION['mensajeAdmin'] = "❌ No se puede eliminar: el usuario tiene pedidos registrados en el sistema.";
        }
        
    } elseif ($accion === 'cambiar_rol') {
        $nuevoRol = filter_input(INPUT_POST, 'nuevo_rol', FILTER_VALIDATE_INT);
        // Evitar que el admin se quite el rol a sí mismo por accidente
        if ($idUsu == $codigoLogueado && $nuevoRol != 1) {
            $_SESSION['mensajeAdmin'] = "❌ No puedes quitarte el rol de administrador a ti mismo.";
        } else {
            if ($con->cambiarRolUsuario($idUsu, $nuevoRol)) {
                $_SESSION['mensajeAdmin'] = "✅ Rol de usuario actualizado correctamente.";
            } else {
                $_SESSION['mensajeAdmin'] = "❌ Error al actualizar el rol.";
            }
        }
    }
    
    header("Location: usuarios.php");
    exit;
}

// Obtenemos todos los usuarios para llenar la tabla
$usuarios = $con->obtenerTodosLosUsuarios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin: Usuarios - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">👥 Gestión del Personal y Clientes</h3>
                <p class="text-muted small mt-1 mb-0">Administra los accesos y roles de toda la plataforma.</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fs-6"><?= count($usuarios) ?> Registros</span>
        </div>

        <?php if (isset($_SESSION['mensajeAdmin'])) { 
            // Color dinámico según si es éxito o error
            $esError = strpos($_SESSION['mensajeAdmin'], '❌') !== false;
            $claseAlerta = $esError ? 'alert-danger text-danger-emphasis bg-danger-subtle' : 'alert-success text-success-emphasis bg-success-subtle';
        ?>
            <div class="alert <?= $claseAlerta ?> text-center rounded-4 shadow-sm border-0 mb-4 fw-medium">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>
        
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0">ID</th>
                            <th class="py-3 border-0">Email / Usuario</th>
                            <th class="py-3 border-0">Nombre Completo</th>
                            <th class="py-3 border-0 text-center">Estado</th>
                            <th class="py-3 border-0">Rol del Sistema</th>
                            <th class="pe-4 py-3 border-0 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usu) { 
                            $esAdminLogueado = ($usu['id'] == $codigoLogueado);
                        ?>
                            <tr class="border-bottom border-secondary border-opacity-10 <?= $esAdminLogueado ? 'bg-primary bg-opacity-10' : '' ?>">
                                <td class="ps-4 fw-bold text-dark">#<?= $usu['id'] ?></td>
                                <td>
                                    <div class="text-primary fw-medium"><?= htmlspecialchars($usu['usuario']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center text-secondary" style="width: 32px; height: 32px;">👤</div>
                                        <?= htmlspecialchars($usu['nombre'] . ' ' . $usu['apellidos']) ?>
                                    </div>
                                </td>
                                
                                <!-- Columna Estado -->
                                <td class="text-center">
                                    <?php if($usu['activo']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success-emphasis border border-success-subtle rounded-pill px-3 py-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger-emphasis border border-danger-subtle rounded-pill px-3 py-2">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Columna Cambio de Rol -->
                                <td>
                                    <form action="usuarios.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="accion" value="cambiar_rol">
                                        <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                        <select name="nuevo_rol" class="form-select form-select-sm rounded-pill shadow-none bg-light border-0 px-3 fw-medium">
                                            <option value="0" <?= $usu['rol'] == 0 ? 'selected' : '' ?>>Cliente</option>
                                            <option value="2" <?= $usu['rol'] == 2 ? 'selected' : '' ?>>Repartidor</option>
                                            <option value="1" <?= $usu['rol'] == 1 ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold shadow-sm">Aplicar</button>
                                    </form>
                                </td>

                                <!-- Columna Botones Activar/Borrar -->
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Botón Activar/Desactivar -->
                                        <form action="usuarios.php" method="POST">
                                            <input type="hidden" name="accion" value="toggle_activo">
                                            <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                            <input type="hidden" name="estado_actual" value="<?= $usu['activo'] ?>">
                                            <?php if($usu['activo']): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" <?= $esAdminLogueado ? 'disabled' : '' ?>>
                                                    Suspender
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" <?= $esAdminLogueado ? 'disabled' : '' ?>>
                                                    Reactivar
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Botón Eliminar -->
                                        <form action="usuarios.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario de la base de datos de forma permanente?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" <?= $esAdminLogueado ? 'disabled' : '' ?>>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>