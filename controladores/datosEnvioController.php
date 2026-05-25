<?php
// controladores/datosEnvioController.php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

session_start();

class DatosEnvioController {
    public function cargarFormularioRuta() {
        // Verificamos sesión y carrito
        $idUsuario = $_SESSION['codigo'] ?? null;
        if ($idUsuario == null || !isset($_SESSION['carritoJSON'])) {
            header("Location: ../controladores/productoController.php");
            exit;
        }

        // Obtener datos del cliente para el autocompletado
        $con = AccesoBD::getInstance();
        $u = $con->obtenerUsuarioBD($idUsuario);

        // Cargar la vista
        require_once '../tienda/datosEnvio.php';
    }
}

$controller = new DatosEnvioController();
$controller->cargarFormularioRuta();
?>