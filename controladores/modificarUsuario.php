<?php
// controladores/modificarUsuario.php
session_start();
require_once '../modelos/AccesoBD.php';

$codigo = $_SESSION['codigo'] ?? 0;
if ($codigo <= 0) {
    header("Location: ../tienda/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificación del Token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Error de seguridad: Token CSRF inválido o solicitud caducada.");
    }
    
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $domicilio = $_POST['domicilio'] ?? '';
    $poblacion = $_POST['poblacion'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $clave1 = $_POST['clave1'] ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    // Validación de contraseñas
    if (!empty($clave1) && $clave1 !== $clave2) {
        $_SESSION['mensaje'] = "❌ Las contraseñas no coinciden.";
        header("Location: ../tienda/usuario.php");
        exit;
    }

    $con = AccesoBD::getInstance();
    $exito = $con->modificarUsuarioBD($codigo, $clave1, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono);

    if ($exito) {
        // Actualizamos el nombre en sesión por si lo ha cambiado
        $_SESSION['nombreUsuario'] = $nombre;
        $_SESSION['mensaje'] = "✅ Perfil actualizado correctamente.";
    } else {
        $_SESSION['mensaje'] = "❌ Hubo un error al actualizar tus datos.";
    }
    
    header("Location: ../tienda/usuario.php");
    exit;
}
?>