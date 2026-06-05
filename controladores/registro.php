<?php
require_once '../modelos/AccesoBD.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos FILTER_VALIDATE_EMAIL para comprobar que es un correo 100% válido
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_VALIDATE_EMAIL);
    
    $nombre    = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $domicilio = $_POST['domicilio'] ?? '';
    $poblacion = $_POST['poblacion'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $cp        = $_POST['cp'] ?? '';
    $telefono  = $_POST['telefono'] ?? '';
    $clave     = $_POST['clave'] ?? '';
    $clave2    = $_POST['clave2'] ?? '';
    $urlDestino= $_POST['url'] ?? 'usuario.php';

    // 1. VALIDACIÓN DE CORREO VÁLIDO
    if (!$usuario) {
        $_SESSION['mensaje'] = "❌ Error: El formato del correo electrónico no es válido.";
        header("Location: ../tienda/registroUsuario.php?url=" . urlencode($urlDestino));
        exit;
    }

    if (empty($clave) || empty($nombre)) {
        $_SESSION['mensaje'] = "❌ Faltan campos obligatorios.";
        header("Location: ../tienda/registroUsuario.php?url=" . urlencode($urlDestino));
        exit;
    }

    if ($clave !== $clave2) {
        $_SESSION['mensaje'] = "❌ Las contraseñas no coinciden.";
        header("Location: ../tienda/registroUsuario.php?url=" . urlencode($urlDestino));
        exit;
    }

    try {
        $con = AccesoBD::getInstance();
        $exito = $con->registrarUsuarioBD($usuario, $clave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono);

        if ($exito) {
            $idNuevo = $con->comprobarUsuarioBD($usuario, $clave);
            if ($idNuevo > 0) {
                session_regenerate_id(true); 
                $_SESSION['codigo'] = $idNuevo;
                $_SESSION['nombreUsuario'] = $nombre;
                $_SESSION['rol'] = 0; 
            }
            header("Location: ../tienda/" . ltrim($urlDestino, '/'));
            exit;
        } else {
            $_SESSION['mensaje'] = "❌ El correo electrónico ya está registrado en el sistema.";
            header("Location: ../tienda/registroUsuario.php?url=" . urlencode($urlDestino));
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "❌ Error técnico en la plataforma logística.";
        header("Location: ../tienda/registroUsuario.php?url=" . urlencode($urlDestino));
        exit;
    }
}
?>