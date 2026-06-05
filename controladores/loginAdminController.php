<?php
// controladores/loginAdminController.php
session_start();
require_once '../modelos/AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_EMAIL);
    $clave = $_POST['clave'] ?? '';

    if (empty($usuario) || empty($clave)) {
        $_SESSION['error_admin'] = "El usuario y la contraseña son obligatorios.";
        header("Location: ../admin/loginAdmin.php");
        exit;
    }

    try {
        $con = AccesoBD::getInstance();
        $codigo = $con->comprobarUsuarioBD($usuario, $clave);

        if ($codigo > 0) {
            $u = $con->obtenerUsuarioBD($codigo);
            if ($u != null && $u->getRol() == 1) {
                session_regenerate_id(true);
                $_SESSION['codigo'] = $codigo;
                $_SESSION['rol'] = 1;
                $_SESSION['nombreAdmin'] = $u->getNombre();
                
                header("Location: ../admin/index.php");
                exit;
            } else {
                $_SESSION['error_admin'] = "Acceso denegado: Careces de privilegios de Administrador.";
                header("Location: ../admin/loginAdmin.php");
                exit;
            }
        } else {
            $_SESSION['error_admin'] = "Credenciales incorrectas. Inténtalo de nuevo.";
            header("Location: ../admin/loginAdmin.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error_admin'] = "Error técnico en la plataforma logística.";
        header("Location: ../admin/loginAdmin.php");
        exit;
    }
}
?>