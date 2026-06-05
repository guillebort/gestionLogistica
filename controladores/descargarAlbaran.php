<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../servicios/PdfService.php'; // Asegúrate de que la ruta coincida con tu proyecto

if (!isset($_GET['id']) || !isset($_SESSION['codigo'])) {
    die("Acceso no autorizado.");
}

$idPedido = (int)$_GET['id'];
$con = AccesoBD::getInstance();

$pedido = $con->obtenerPedidoPorId($idPedido);
$detalles = $con->obtenerDetallesPedido($idPedido);

// SEGURIDAD: Solo el Administrador (rol 1) o el cliente dueño del pedido pueden descargarlo
if ($_SESSION['rol'] != 1 && $pedido['id_usuario'] != $_SESSION['codigo']) {
    die("Error de seguridad: Este albarán no pertenece a tu cuenta.");
}

// Instanciamos el servicio PDF y generamos el documento
$pdf = new PdfService();
// La función generarTicket procesará los datos y forzará la descarga o visualización del PDF
$pdf->generarTicket($pedido, $detalles);
?>