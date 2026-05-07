<?php
session_start();
require_once '../modelos/AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_EMAIL);
    $clave = $_POST['clave'] ?? '';

    $con = AccesoBD::getInstance();
    $codigo = $con->comprobarUsuarioBD($usuario, $clave);

    if ($codigo > 0) {
        $u = $con->obtenerUsuarioBD($codigo);
        // El enunciado pide un campo que indique que el usuario es administrador (rol 1)
        if ($u != null && $u->getRol() == 1) {
            $_SESSION['codigo'] = $codigo;
            $_SESSION['rol'] = 1;
            $_SESSION['nombreAdmin'] = $u->getNombre();
            header("Location: ../admin/index.php");
            exit;
        } else {
            $_SESSION['error_admin'] = "Acceso denegado: No tienes permisos de administrador.";
        }
    } else {
        $_SESSION['error_admin'] = "Credenciales incorrectas.";
    }
    header("Location: ../admin/loginAdmin.php");
    exit;
}