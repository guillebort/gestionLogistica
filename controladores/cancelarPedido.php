<?php
session_start();
require_once '../modelos/AccesoBD.php';

$idUsuario = $_SESSION['codigo'] ?? null;
if ($idUsuario == null) {
    header("Location: login.php");
    exit;
}

// 1. Verificamos que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// 2. Verificamos el Token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Error de seguridad: Token CSRF inválido.");
}

try {
    // 3. Recogemos el ID por POST en lugar de GET
    $idPedido = isset($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : 0;
    
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

header("Location: ../tienda/usuario.php");
exit;
?>