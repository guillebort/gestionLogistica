<?php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['codigo'] ?? null;
    $carrito = $_SESSION['carritoJSON'] ?? null;

    if ($idUsuario == null || $carrito == null) {
        header("Location: ../tienda/productos.php");
        exit;
    }

    $con = AccesoBD::getInstance();

    $tipoTarjeta = $_POST['tarjetaGuardada'] ?? 'NUEVA';
    $guardarTarjeta = $_POST['guardarTarjetaCheck'] ?? '';

    // Solo guardamos si eligió "NUEVA" y marcó el check de "SI"
    if ($tipoTarjeta === 'NUEVA' && $guardarTarjeta === 'SI') {
        $numero = $_POST['numeroTarjeta'] ?? '';
        $titular = $_POST['titularTarjeta'] ?? '';
        $caducidad = $_POST['caducidadTarjeta'] ?? '';
        
        // Hacemos un check rápido para no guardar tarjetas vacías
        if (!empty($numero) && !empty($titular) && !empty($caducidad)) {
            $con->guardarTarjeta($idUsuario, $numero, $titular, $caducidad);
        }
    }

    // Recuperamos datos de ruta guardados en la sesión por guardarRuta.php
    $total = $_SESSION['totalPedido'];
    $dirOrigen = $_SESSION['direccionOrigen'];
    $latOrigen = $_SESSION['latOrigen'];
    $lonOrigen = $_SESSION['lonOrigen'];
    $dirDestino = $_SESSION['direccionDestino'];
    $latDestino = $_SESSION['latDestino'];
    $lonDestino = $_SESSION['lonDestino'];

    
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