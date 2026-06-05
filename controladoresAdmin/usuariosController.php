<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
    $idUsu = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if ($accion === 'toggle_activo') {
        $estadoActual = filter_input(INPUT_POST, 'estado_actual', FILTER_VALIDATE_INT);
        $con->cambiarEstadoUsuario($idUsu, $estadoActual == 1 ? 0 : 1);
        $_SESSION['mensajeAdmin'] = "✅ Estado actualizado.";
        
    } elseif ($accion === 'eliminar') {
        if ($con->eliminarUsuarioSiSinPedidos($idUsu)) {
            $_SESSION['mensajeAdmin'] = "✅ Usuario eliminado.";
        } else {
            $_SESSION['mensajeAdmin'] = "❌ No se puede eliminar: el usuario tiene pedidos.";
        }
        
    } elseif ($accion === 'cambiar_rol') {
        $nuevoRol = filter_input(INPUT_POST, 'nuevo_rol', FILTER_VALIDATE_INT);
        if ($idUsu == $codigoLogueado && $nuevoRol != 1) {
            $_SESSION['mensajeAdmin'] = "❌ No puedes quitarte el rol de admin a ti mismo.";
        } else {
            $con->cambiarRolUsuario($idUsu, $nuevoRol);
            $_SESSION['mensajeAdmin'] = "✅ Rol actualizado.";
        }
        
    } elseif ($accion === 'crear_personal') {
        $n_email = filter_input(INPUT_POST, 'nuevo_email', FILTER_SANITIZE_EMAIL);
        $n_clave = $_POST['nueva_clave'] ?? '';
        $n_nombre = filter_input(INPUT_POST, 'nuevo_nombre', FILTER_SANITIZE_STRING);
        $n_apellidos = filter_input(INPUT_POST, 'nuevo_apellidos', FILTER_SANITIZE_STRING);
        $n_telefono = filter_input(INPUT_POST, 'nuevo_telefono', FILTER_SANITIZE_STRING);
        $n_domicilio = filter_input(INPUT_POST, 'nuevo_domicilio', FILTER_SANITIZE_STRING);
        $n_poblacion = filter_input(INPUT_POST, 'nuevo_poblacion', FILTER_SANITIZE_STRING);
        $n_provincia = filter_input(INPUT_POST, 'nuevo_provincia', FILTER_SANITIZE_STRING);
        $n_cp = filter_input(INPUT_POST, 'nuevo_cp', FILTER_SANITIZE_STRING);
        $n_rol = filter_input(INPUT_POST, 'nuevo_rol', FILTER_VALIDATE_INT);

        if (!empty($n_email) && !empty($n_clave) && !empty($n_nombre) && isset($n_rol)) {
            if ($con->registrarUsuarioBD($n_email, $n_clave, $n_nombre, $n_apellidos, $n_domicilio, $n_poblacion, $n_provincia, $n_cp, $n_telefono, $n_rol)) {
                $_SESSION['mensajeAdmin'] = "✅ Personal registrado correctamente.";
            } else {
                $_SESSION['mensajeAdmin'] = "❌ Error: Ese correo ya existe o los datos son inválidos.";
            }
        } else {
            $_SESSION['mensajeAdmin'] = "❌ Error: Rellena los campos obligatorios.";
        }
    }
    header("Location: usuariosController.php");
    exit;
}

$usuarios = $con->obtenerTodosLosUsuarios();
require_once '../admin/usuarios.php';
?>