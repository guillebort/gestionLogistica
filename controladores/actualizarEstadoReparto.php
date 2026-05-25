<?php
// controladores/actualizarEstadoReparto.php
session_start();
require_once '../modelos/AccesoBD.php';

// 1. Cabecera estandarizada para respuestas JSON
header('Content-Type: application/json; charset=utf-8');

// 2. Control de Acceso (Autorización - HTTP 403)
$idRepartidor = $_SESSION['codigo'] ?? 0;
$rol = $_SESSION['rol'] ?? 0;

if ($idRepartidor <= 0 || $rol != 2) {
    http_response_code(403); // 403 Forbidden
    echo json_encode([
        "status" => "error", 
        "message" => "Acceso denegado. Se requieren permisos de repartidor."
    ]);
    exit;
}

// 3. Control de Método (HTTP 405)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 Method Not Allowed
    echo json_encode([
        "status" => "error", 
        "message" => "Método no permitido. Use POST."
    ]);
    exit;
}

// 4. Saneamiento y validación de datos
$idPedido = filter_input(INPUT_POST, 'idPedido', FILTER_VALIDATE_INT);
$estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);
$motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$firma = $_POST['firma'] ?? null;

// 5. Control de Parámetros (HTTP 400)
if (!$idPedido || !$estado) {
    http_response_code(400); // 400 Bad Request
    echo json_encode([
        "status" => "error", 
        "message" => "Faltan parámetros obligatorios o son inválidos."
    ]);
    exit;
}

// 6. Lógica de negocio y respuesta (HTTP 200 o HTTP 500)
$con = AccesoBD::getInstance();
$exito = $con->actualizarEstadoReparto($idPedido, $idRepartidor, $estado, $firma);

if ($exito) {
    http_response_code(200); // 200 OK
    echo json_encode([
        "status" => "success", 
        "message" => "Estado actualizado correctamente a " . $estado,
        "data" => ["idPedido" => $idPedido, "nuevoEstado" => $estado]
    ]);
} else {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode([
        "status" => "error", 
        "message" => "Error interno al actualizar la base de datos."
    ]);
}
exit;
?>