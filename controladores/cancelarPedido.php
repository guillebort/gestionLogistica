<?php
// --- Archivo: cancelarPedido.php ---
session_start();
require_once 'AccesoBD.php';

$idUsuario = $_SESSION['codigo'] ?? null;
if ($idUsuario == null) {
    header("Location: loginUsuario.php");
    exit;
}

try {
    $idPedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $con = AccesoBD::getInstance();
    $exito = $con->cancelarPedido($idPedido, $idUsuario);

    if ($exito) {
        $_SESSION['mensaje'] = "✅ Pedido #" . $idPedido . " cancelado. El stock ha sido devuelto.";
    } else {
        $_SESSION['mensaje'] = "❌ No se pudo cancelar el pedido. (Quizás ya está enviado).";
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "❌ Error técnico al intentar cancelar.";
}

header("Location: usuario.php");
exit;
?>