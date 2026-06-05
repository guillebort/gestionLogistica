<?php
session_start();
require_once '../modelos/AccesoBD.php';

$con = AccesoBD::getInstance();
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

$mensajes = $con->obtenerMensajes();
require_once '../admin/mensajes.php';
?>