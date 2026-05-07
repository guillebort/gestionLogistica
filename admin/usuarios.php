<?php
session_start();
require_once '../modelos/AccesoBD.php';

// Control de flujo[cite: 9]
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/loginUsuario.php");
    exit;
}

$con = AccesoBD::getInstance();

// Lógica para Activar/Desactivar o Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);
    $idUsu = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if ($accion === 'toggle_activo') {
        $estadoActual = filter_input(INPUT_POST, 'estado_actual', FILTER_VALIDATE_INT);
        $nuevoEstado = ($estadoActual == 1) ? 0 : 1;
        $con->cambiarEstadoUsuario($idUsu, $nuevoEstado); // Modifica el campo 'activo'[cite: 9]
        $_SESSION['mensajeAdmin'] = "✅ Estado del usuario actualizado.";
    } elseif ($accion === 'eliminar') {
        // El modelo debe comprobar que COUNT(pedidos) == 0 antes de hacer el DELETE[cite: 9]
        if ($con->eliminarUsuarioSiSinPedidos($idUsu)) {
            $_SESSION['mensajeAdmin'] = "✅ Usuario eliminado correctamente.";
        } else {
            $_SESSION['mensajeAdmin'] = "❌ No se puede eliminar: el usuario tiene pedidos registrados.";
        }
    }
    header("Location: usuarios.php");
    exit;
}

$usuarios = $con->obtenerTodosLosUsuarios();
?>
<!-- Aquí iría el HTML con Bootstrap listando los usuarios en una tabla -->
<!-- Por cada usuario mostramos un botón que cambia según su estado: -->
<!-- <button type="submit" class="btn <?= $usu['activo'] ? 'btn-danger' : 'btn-success' ?>"> -->
<!-- <?= $usu['activo'] ? 'Desactivar' : 'Activar' ?> </button> -->