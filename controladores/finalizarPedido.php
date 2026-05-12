<?php


require_once '../vendor/autoload.php';
require_once '../modelos/AccesoBD.php';
session_start();
use servicios\pdfService;
use servicios\mailService;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['codigo'] ?? null;
    $carrito = $_SESSION['carritoJSON'] ?? null;

   if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['mensaje'] = "Error de seguridad: La solicitud caducó o es inválida.";
        header("Location: ../tienda/procesarPedido.php");
        exit;
    }

    if (!$idUsuario || !$carrito) {
        header("Location: ../tienda/productos.php");
        exit;
    }

    $con = AccesoBD::getInstance();
    
    // ... [Tu lógica actual de tarjetas se mantiene igual] ...

    $total = $_SESSION['totalPedido'];
    $dirOrigen = $_SESSION['direccionOrigen'];
    $latOrigen = $_SESSION['latOrigen'] ?? 0.0;
    $lonOrigen = $_SESSION['lonOrigen'] ?? 0.0;
    $dirDestino = $_SESSION['direccionDestino'] ?? '';
    $latDestino = $_SESSION['latDestino'] ?? 0.0;
    $lonDestino = $_SESSION['lonDestino'] ?? 0.0;

    // 1. Guardar en Base de Datos
    $idNuevoPedido = $con->guardarPedido($idUsuario, $total, $carrito, $dirOrigen, $latOrigen, $lonOrigen, $dirDestino, $latDestino, $lonDestino);

    if ($idNuevoPedido > 0) {
        $usuarioDatos = $con->obtenerUsuarioBD($idUsuario);
        
        if ($usuarioDatos != null) {
            // 2. Generar PDF (Delegado al servicio)
            $pdfService = new PdfService();
            $pdfOutput = $pdfService->generarTicketReserva($idNuevoPedido, $usuarioDatos, $dirOrigen, $dirDestino, $total);
            
            // 3. Enviar Mail (Delegado al servicio)
            $mailService = new MailService();
            $mailService->enviarConfirmacionConTicket($usuarioDatos, $idNuevoPedido, $pdfOutput);
        }

        // 4. Limpiar sesión y Redirigir
        unset($_SESSION['carritoJSON'], $_SESSION['totalPedido'], $_SESSION['direccionOrigen'], $_SESSION['direccionDestino']);
        $_SESSION['mensaje'] = "REF-LOGIS-" . $idNuevoPedido;
        header("Location: ../tienda/pedidoCompletado.php");
    } else {
        $_SESSION['mensaje'] = "Error técnico al procesar el envío.";
        header("Location: ../tienda/procesarPedido.php");
    }
    exit;
}