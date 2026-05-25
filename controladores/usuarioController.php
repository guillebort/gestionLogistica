<?php
// controladores/usuarioController.php
session_start();
require_once '../includes/controlSesion.php';
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

class UsuarioController {
    public function mostrarPerfil() {

        $codigoLogueado = $_SESSION["codigo"] ?? 0;
        if ($codigoLogueado <= 0) {
            header("Location: ../tienda/login.php");
            exit;
        }

        try {
            $con = AccesoBD::getInstance();
            $u = $con->obtenerUsuarioBD($codigoLogueado);
            
            if ($u != null) {
                $historial = $con->obtenerHistorialDetallado($codigoLogueado);
            } else {
                $historial = [];
            }

            require_once '../tienda/usuario.php';

        } catch (Exception $e) {
            $_SESSION["mensaje"] = "Error al cargar el perfil.";
            header("Location: ../tienda/index.php");
            exit;
        }
    }
}

$controller = new UsuarioController();
$controller->mostrarPerfil();
?>