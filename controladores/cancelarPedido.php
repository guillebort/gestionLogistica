<?php
// controladores/cancelarPedido.php
session_start();
require_once '../modelos/AccesoBD.php';

header('Content-Type: application/json; charset=utf-8');

$idUsuario = $_SESSION['codigo'] ?? null;
if (!$idUsuario) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "No autorizado. Inicia sesión."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// Para APIs JSON, solemos leer del body
$jsonInput = json_decode(file_get_contents('php://input'), true);
$idPedido = $jsonInput['id_pedido'] ?? filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
$tokenCsrf = $jsonInput['csrf_token'] ?? filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);

if (!$tokenCsrf || !hash_equals($_SESSION['csrf_token'], $tokenCsrf)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Error de seguridad (CSRF)."]);
    exit;
}

if (!$idPedido) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID de pedido no proporcionado."]);
    exit;
}

try {
    $con = AccesoBD::getInstance();
    $exito = $con->cancelarPedido($idPedido, $idUsuario);

    if ($exito) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Pedido #$idPedido cancelado correctamente. Stock restaurado."]);
    } else {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "No se pudo cancelar el pedido. Es posible que ya esté en ruta."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error técnico en el servidor."]);
}
exit;
?>