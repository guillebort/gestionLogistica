<?php
session_start();
require_once '../modelos/AccesoBD.php';

$con = AccesoBD::getInstance();
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

$idUsuario = filter_input(INPUT_GET, 'idUsuario', FILTER_VALIDATE_INT);
$idProducto = filter_input(INPUT_GET, 'idProducto', FILTER_VALIDATE_INT);
$fecha = filter_input(INPUT_GET, 'fecha', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$operadorFecha = $_GET['operadorFecha'] ?? '=';

$usuarios = $con->obtenerTodosLosUsuarios(); 
$productos = $con->obtenerProductosBD();
$pedidosFiltrados = $con->obtenerPedidosFiltrados($idUsuario, $idProducto, $fecha, $operadorFecha);

require_once '../admin/historialPedidos.php';
?>