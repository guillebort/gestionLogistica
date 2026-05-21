<?php
// controladores/loginAdminController.php
session_start();
require_once '../modelos/AccesoBD.php';

// Estándar REST: Cabecera JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método HTTP no permitido."]);
    exit;
}

// Soporte para FormData o JSON Raw
$jsonInput = json_decode(file_get_contents('php://input'), true);
$usuario = filter_var($jsonInput['usuario'] ?? $_POST['usuario'] ?? '', FILTER_SANITIZE_EMAIL);
$clave = $jsonInput['clave'] ?? $_POST['clave'] ?? '';

if (empty($usuario) || empty($clave)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "El usuario y la contraseña son obligatorios."]);
    exit;
}

try {
    $con = AccesoBD::getInstance();
    $codigo = $con->comprobarUsuarioBD($usuario, $clave);

    if ($codigo > 0) {
        $u = $con->obtenerUsuarioBD($codigo);
        
        // Autorización estricta: Solo Rol 1 (Admin)
        if ($u != null && $u->getRol() == 1) {
            
            // Ciberseguridad: Prevenir secuestro de sesión (Session Fixation)
            session_regenerate_id(true);
            
            $_SESSION['codigo'] = $codigo;
            $_SESSION['rol'] = 1;
            $_SESSION['nombreAdmin'] = $u->getNombre();

            http_response_code(200);
            echo json_encode([
                "status" => "success", 
                "message" => "Acceso autorizado. Cargando panel de control...",
                "data" => ["redirect" => "../admin/index.php"]
            ]);
        } else {
            http_response_code(403); // 403 Forbidden
            echo json_encode(["status" => "error", "message" => "Acceso denegado: Tus credenciales son válidas pero careces de privilegios de Administrador."]);
        }
    } else {
        http_response_code(401); // 401 Unauthorized
        echo json_encode(["status" => "error", "message" => "Credenciales incorrectas. Inténtalo de nuevo."]);
    }
} catch (Exception $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["status" => "error", "message" => "Error técnico en la plataforma logística."]);
}
exit;
?>