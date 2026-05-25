<?php
// controladores/registro.php

require_once '../modelos/AccesoBD.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// Leer payload JSON o FormData estándar
$jsonInput = json_decode(file_get_contents('php://input'), true);

// Función auxiliar para extraer datos venga como venga
function getParam($key, $jsonInput) {
    return $jsonInput[$key] ?? $_POST[$key] ?? '';
}

$usuario   = filter_var(getParam('usuario', $jsonInput), FILTER_SANITIZE_EMAIL);
$nombre    = getParam('nombre', $jsonInput);
$apellidos = getParam('apellidos', $jsonInput);
$domicilio = getParam('domicilio', $jsonInput);
$poblacion = getParam('poblacion', $jsonInput);
$provincia = getParam('provincia', $jsonInput);
$cp        = getParam('cp', $jsonInput);
$telefono  = getParam('telefono', $jsonInput);
$clave     = getParam('clave', $jsonInput);
$clave2    = getParam('clave2', $jsonInput);
$urlDestino= getParam('url', $jsonInput) ?: 'usuario.php';

// Validación de Backend 
if (empty($usuario) || empty($clave) || empty($nombre)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios."]);
    exit;
}

if ($clave !== $clave2) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Las contraseñas no coinciden. Intento de evasión de frontend detectado."]);
    exit;
}

try {
    $con = AccesoBD::getInstance();
    
    // Intentamos registrar al usuario
    $exito = $con->registrarUsuarioBD($usuario, $clave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono);

    if ($exito) {
        // Autologin inmediato tras el registro
        $idNuevo = $con->comprobarUsuarioBD($usuario, $clave);
        if ($idNuevo != -1) {
            session_regenerate_id(true); // Prevenir Session Fixation
            $_SESSION['codigo'] = $idNuevo;
            $_SESSION['nombreUsuario'] = $nombre;
            $_SESSION['rol'] = 0; // Rol cliente 
        }
        
        $rutaRedirect = "../tienda/" . ltrim($urlDestino, '/');
        
        http_response_code(201); // 201 Created
        echo json_encode([
            "status" => "success", 
            "message" => "¡Cuenta creada! Bienvenido a LogisTFG.",
            "data" => ["redirect" => $rutaRedirect]
        ]);
    } else {
        // Falló la inserción (generalmente porque el usuario/email ya tiene el constraint UNIQUE en MySQL)
        http_response_code(409); // 409 Conflict
        echo json_encode(["status" => "error", "message" => "El correo electrónico introducido ya está registrado en el sistema."]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Error técnico en la plataforma logística."]);
}
exit;
?>