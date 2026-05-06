<?php
session_start();
require_once '../modelos/AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['codigo'] ?? null;
    $carrito = $_SESSION['carritoJSON'] ?? null;

    if ($idUsuario == null || $carrito == null) {
        header("Location: ../tienda/productos.php");
        exit;
    }

    // Recuperamos datos de ruta guardados en la sesión por guardarRuta.php
    $total = $_SESSION['totalPedido'];
    $dirOrigen = $_SESSION['direccionOrigen'];
    $latOrigen = $_SESSION['latOrigen'];
    $lonOrigen = $_SESSION['lonOrigen'];
    $dirDestino = $_SESSION['direccionDestino'];
    $latDestino = $_SESSION['latDestino'];
    $lonDestino = $_SESSION['lonDestino'];

    $con = AccesoBD::getInstance();
    $idNuevoPedido = $con->guardarPedido($idUsuario, $total, $carrito, $dirOrigen, $latOrigen, $lonOrigen, $dirDestino, $latDestino, $lonDestino);

    if ($idNuevoPedido > 0) {
        // Limpieza de sesión tras éxito
        unset($_SESSION['carritoJSON'], $_SESSION['totalPedido'], $_SESSION['direccionOrigen'], $_SESSION['direccionDestino']);
        $_SESSION['mensaje'] = "REF-LOGIS-" . $idNuevoPedido;
        header("Location: ../tienda/pedidoCompletado.php");
    } else {
        $_SESSION['mensaje'] = "Error técnico al procesar el envío.";
        header("Location: ../tienda/procesarPedido.php");
    }
    exit;
}
?>