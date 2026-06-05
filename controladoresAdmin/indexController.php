<?php
session_start();
require_once '../includes/controlSesion.php';
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

$pedidosPendientes = $con->obtenerPedidosPendientesMapa();
$repartidores = $con->obtenerRepartidores();
$stats = $con->obtenerEstadisticas();

$pedidosJson = json_encode($pedidosPendientes);

// Cargar la vista
require_once '../admin/index.php';
?>