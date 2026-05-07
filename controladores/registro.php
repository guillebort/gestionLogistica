<?php
// --- Archivo: registro.php ---
session_start();
require_once '../modelos/AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $domicilio = $_POST['domicilio'] ?? '';
    $poblacion = $_POST['poblacion'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    if ($clave !== $clave2) {
        header("Location: registroUsuario.php?error=Las contraseñas no coinciden");
        exit;
    }

    $con = AccesoBD::getInstance();
    $exito = $con->registrarUsuarioBD($usuario, $clave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono);

    if ($exito) {
            $idNuevo = $con->comprobarUsuarioBD($usuario, $clave);
            if ($idNuevo != -1) {
                $_SESSION['codigo'] = $idNuevo;
                $_SESSION['nombreUsuario'] = $nombre;
            }
            $rutaDestino = $_POST['url'] ?? 'usuario.php';
            $_SESSION['mensaje'] = "✅ ¡Cuenta creada! Bienvenido a la tienda.";
            
            // SOLUCIÓN: Añadir '../tienda/' para volver a la carpeta correcta
            header("Location: ../tienda/" . ltrim($rutaDestino, '/'));
        } else {
            $_SESSION['mensaje'] = "❌ Error al registrar. Ese correo ya existe.";
            
            // SOLUCIÓN PARA EL ERROR: Mejor usar el archivo de registro como fallback
            $rutaOrigen = $_SERVER['HTTP_REFERER'] ?? '../tienda/registroUsuario.php';
            header("Location: " . $rutaOrigen);
        }
        exit;
    }
?>