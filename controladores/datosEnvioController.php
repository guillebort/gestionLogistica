<?php
// controladores/datosEnvioController.php

// 1. PRIMERO las clases
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

// 2. LUEGO la sesión
session_start();

class DatosEnvioController {
    public function cargarFormularioRuta() {
        // 1. Seguridad: Verificar sesión y carrito
        $idUsuario = $_SESSION['codigo'] ?? null;
        if ($idUsuario == null || !isset($_SESSION['carritoJSON'])) {
            header("Location: ../controladores/productoController.php");
            exit;
        }

        // 2. Obtener datos del cliente para el autocompletado
        $con = AccesoBD::getInstance();
        $u = $con->obtenerUsuarioBD($idUsuario);

        // 3. Cargar la vista
        require_once '../tienda/datosEnvio.php';
    }
}

$controller = new DatosEnvioController();
$controller->cargarFormularioRuta();
?>