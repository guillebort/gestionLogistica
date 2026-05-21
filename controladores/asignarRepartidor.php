<?php
// controladores/asignarRepartidor.php
session_start();
require_once '../modelos/AccesoBD.php';

header('Content-Type: application/json; charset=utf-8');

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$rol = $_SESSION['rol'] ?? 0;

// Verificamos rol de admin (rol 1)
if ($codigoLogueado <= 0 || $rol != 1) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Permisos insuficientes."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

$idPedido = filter_input(INPUT_POST, 'idPedido', FILTER_VALIDATE_INT);
$idRepartidor = filter_input(INPUT_POST, 'idRepartidor', FILTER_VALIDATE_INT);
$nuevoEstado = 2; // "Enviado / En ruta"

if ($idPedido > 0 && $idRepartidor > 0) {
    $con = AccesoBD::getInstance();
    $exito = $con->asignarRepartidor($idPedido, $idRepartidor, $nuevoEstado);
    
    if ($exito) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Pedido #$idPedido asignado correctamente al repartidor."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error al actualizar la base de datos."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos de asignación inválidos."]);
}
exit;
?>