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
</head>
<body class="bg-light">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        <?php if (isset($_SESSION['mensajeAdmin'])) { ?>
            <div class="alert alert-info text-center fw-bold shadow-sm">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>

        <h3 class="mb-4 text-primary">👥 Listado de Usuarios y Personal</h3>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Email / Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Estado</th>
                            <th>Rol en el Sistema</th>
                            <th class="text-center">Acciones de Cuenta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usu) { ?>
                            <tr>
                                <td><strong><?= $usu['id'] ?></strong></td>
                                <td><?= htmlspecialchars($usu['usuario']) ?></td>
                                <td><?= htmlspecialchars($usu['nombre'] . ' ' . $usu['apellidos']) ?></td>
                                
                                <!-- Columna Estado -->
                                <td>
                                    <span class="badge <?= $usu['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $usu['activo'] ? 'Activo' : 'Baja/Inactivo' ?>
                                    </span>
                                </td>

                                <!-- Columna Cambio de Rol -->
                                <td>
                                    <form action="usuarios.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="accion" value="cambiar_rol">
                                        <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                        <select name="nuevo_rol" class="form-select form-select-sm">
                                            <option value="0" <?= $usu['rol'] == 0 ? 'selected' : '' ?>>Cliente</option>
                                            <option value="2" <?= $usu['rol'] == 2 ? 'selected' : '' ?>>Repartidor</option>
                                            <option value="1" <?= $usu['rol'] == 1 ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Aplicar</button>
                                    </form>
                                </td>

                                <!-- Columna Botones Activar/Borrar -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Botón Activar/Desactivar -->
                                        <form action="usuarios.php" method="POST">
                                            <input type="hidden" name="accion" value="toggle_activo">
                                            <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                            <input type="hidden" name="estado_actual" value="<?= $usu['activo'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $usu['activo'] ? 'btn-warning' : 'btn-success' ?>" <?= ($usu['id'] == $codigoLogueado) ? 'disabled' : '' ?>>
                                                <?= $usu['activo'] ? 'Suspender' : 'Activar' ?>
                                            </button>
                                        </form>

                                        <!-- Botón Eliminar -->
                                        <form action="usuarios.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario de la base de datos de forma permanente?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" <?= ($usu['id'] == $codigoLogueado) ? 'disabled' : '' ?>>
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