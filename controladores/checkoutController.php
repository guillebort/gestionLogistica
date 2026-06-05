<?php
session_start();
require_once '../modelos/AccesoBD.php';

if (!isset($_SESSION['codigo'])) {
    // Si no está logueado, le obligamos a iniciar sesión o registrarse, pasando la url de retorno
    header("Location: ../tienda/login.php?url=checkoutController.php");
    exit;
}

if (empty($_SESSION['carrito'])) {
    header("Location: ../tienda/productos.php");
    exit;
}

$con = AccesoBD::getInstance();
$id_usuario = $_SESSION['codigo'];

// Extraemos las tarjetas asociadas al cliente
$tarjetasGuardadas = $con->obtenerTarjetasUsuario($id_usuario);

require_once '../tienda/checkout.php';
?>