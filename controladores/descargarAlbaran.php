<?php
// controladores/descargarAlbaran.php
session_start();
require_once '../vendor/autoload.php';
require_once '../modelos/AccesoBD.php';
require_once '../servicios/pdfService.php';

use servicios\PdfService;

// Verificamos permisos
if (!isset($_SESSION['codigo'])) {
    die("Acceso denegado.");
}

$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$idPedido) {
    die("ID de pedido no válido.");
}

$con = AccesoBD::getInstance();
$datosPedido = $con->obtenerAlbaranPedido($idPedido);

if (!$datosPedido) {
    die("No se encontró el pedido.");
}

$pdfService = new PdfService();
$pdfOutput = $pdfService->generarAlbaranPdf($datosPedido);

// Enviar el archivo binario al navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Albaran_Pedido_'.$idPedido.'.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
echo $pdfOutput;
exit;