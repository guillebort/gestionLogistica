<?php

session_start();
require_once '../modelos/AccesoBD.php';

$codigo = $_SESSION['codigo'] ?? 0;
if ($codigo <= 0) {
    header("Location: ../tienda/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $domicilio = $_POST['domicilio'] ?? '';
    $poblacion = $_POST['poblacion'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $clave1 = $_POST['clave1'] ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    if (!empty($clave1) && $clave1 !== $clave2) {
        $_SESSION['mensaje'] = "❌ Las contraseñas no coinciden.";
        header("Location: ../tienda/usuario.php");
        exit;
    }

    $con = AccesoBD::getInstance();
    $exito = $con->modificarUsuarioBD($codigo, $clave1, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono);

    $_SESSION['mensaje'] = $exito ? "✅ Perfil actualizado correctamente." : "❌ Hubo un error al actualizar tus datos.";
    header("Location: ../tienda/usuario.php");
    exit;
}
?>