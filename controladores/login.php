<?php
// --- Archivo: login.php ---
session_start();
require_once 'AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $urlDestino = $_POST['url'] ?? 'usuario.php'; 
    $tipoAcceso = $_POST['tipoAcceso'] ?? 'Acceso';

    $con = AccesoBD::getInstance();

    if ($tipoAcceso === "Acceso") {
        $codigo = $con->comprobarUsuarioBD($usuario, $clave);
        if ($codigo > 0) {
            $u = $con->obtenerUsuarioBD($codigo);
            $_SESSION['codigo'] = $codigo;
            $_SESSION['nombreUsuario'] = $u->getNombre();
            header("Location: " . $urlDestino);
            exit;
        } else {
            $_SESSION['mensaje'] = "⚠️ Usuario o contraseña incorrectos.";
            header("Location: loginUsuario.php");
            exit;
        }
    }
}
?>