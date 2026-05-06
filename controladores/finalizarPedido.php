<?php
// --- Archivo: finalizarPedido.php ---
session_start();
require_once 'AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['codigo'] ?? null;
    $carrito = $_SESSION['carritoJSON'] ?? null;

    if ($idUsuario == null || $carrito == null) {
        header("Location: productos.php");
        exit;
    }

    $total = $_SESSION['totalPedido'];
    $dirOrigen = $_SESSION['direccionOrigen'];
    $latOrigen = (float)$_SESSION['latOrigen'];
    $lonOrigen = (float)$_SESSION['lonOrigen'];

    $dirDestino = $_SESSION['direccionDestino'];
    $latDestino = (float)$_SESSION['latDestino'];
    $lonDestino = (float)$_SESSION['lonDestino'];

    $con = AccesoBD::getInstance();
    $idNuevoPedido = $con->guardarPedido($idUsuario, $total, $carrito, $dirOrigen, $latOrigen, $lonOrigen, $dirDestino, $latDestino, $lonDestino);

    if ($idNuevoPedido > 0) {
        $quiereGuardar = $_POST['guardarTarjetaCheck'] ?? '';
        $tarjetaElegida = $_POST['tarjetaGuardada'] ?? '';

        if ($quiereGuardar === "SI" && ($tarjetaElegida == "" || $tarjetaElegida === "NUEVA")) {
            $num = $_POST['numeroTarjeta'] ?? '';
            $tit = $_POST['titularTarjeta'] ?? '';
            $cad = $_POST['caducidadTarjeta'] ?? '';
            if (!empty(trim($num))) {
                $con->guardarTarjeta($idUsuario, $num, $tit, $cad);
            }
        }

        // Limpiamos la mochila
        unset($_SESSION['carritoJSON'], $_SESSION['totalPedido'], $_SESSION['direccionOrigen'], $_SESSION['latOrigen'], $_SESSION['lonOrigen'], $_SESSION['direccionDestino'], $_SESSION['latDestino'], $_SESSION['lonDestino']);

        $_SESSION['mensaje'] = "REF-LOGIS-" . $idNuevoPedido;
        header("Location: pedidoCompletado.php");
        exit;
    } else {
        $_SESSION['mensaje'] = "❌ Error al tramitar el pedido. Revisa el stock.";
        header("Location: procesarPedido.php");
        exit;
    }
}
?>