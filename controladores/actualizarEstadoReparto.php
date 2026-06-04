<?php
session_start();
require_once '../modelos/AccesoBD.php';

header('Content-Type: application/json; charset=utf-8');

// Control de Acceso
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

// Control de Método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 Method Not Allowed
    echo json_encode([
        "status" => "error", 
        "message" => "Método no permitido. Use POST."
    ]);
    exit;
}

// Saneamiento y validación de datos
$idPedido = filter_input(INPUT_POST, 'idPedido', FILTER_VALIDATE_INT);
$estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);
$motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$firma = $_POST['firma'] ?? null;

// Control de Parámetros (HTTP 400)
if (!$idPedido || !$estado) {
    http_response_code(400); // 400 Bad Request
    echo json_encode([
        "status" => "error", 
        "message" => "Faltan parámetros obligatorios o son inválidos."
    ]);
    exit;
}

// Lógica de negocio y respuesta (HTTP 200 o HTTP 500)
$con = AccesoBD::getInstance();
$exito = $con->actualizarEstadoReparto($idPedido, $idRepartidor, $estado, $firma);

if ($exito) {
    
    // --- INICIO MÓDULO DE CORREO ELECTRÓNICO ---
    // Extraemos el email y nombre cruzando con la tabla usuarios
    $cliente = $con->obtenerDatosEmailCliente($idPedido);
    
    if ($cliente && !empty($cliente['email'])) {
        $emailDestino = $cliente['email'];
        $nombreCliente = $cliente['nombre'];
        $enviarCorreo = true;
        
        if ($estado == 2) { 
            $asunto = "🚚 Tu pedido #$idPedido está EN CAMINO";
            $mensaje = "Hola $nombreCliente,\n\nEl repartidor acaba de recoger tu paquete en origen. ¡Ya está en camino hacia la dirección de entrega!\n\nPronto lo recibirás.";
        } elseif ($estado == 3) { 
            $asunto = "✅ Tu pedido #$idPedido ha sido ENTREGADO";
            $mensaje = "Hola $nombreCliente,\n\nTe confirmamos que tu pedido #$idPedido ha sido entregado con éxito en su destino.\n\nGracias por confiar en nuestra empresa de logística.";
        } elseif ($estado == 4) { 
            $asunto = "⚠️ Incidencia con tu pedido #$idPedido";
            $mensaje = "Hola $nombreCliente,\n\nHa ocurrido una incidencia intentando entregar tu pedido #$idPedido.\nMotivo reportado: $motivo\n\nNos pondremos en contacto contigo pronto para solucionarlo.";
        } else {
            $enviarCorreo = false; // Si es otro estado raro, no mandamos nada
        }

        if ($enviarCorreo) {
            $cabeceras = "From: envios@tudominio.com\r\n"; // Cambia tudominio.com por tu web real
            $cabeceras .= "Reply-To: soporte@tudominio.com\r\n";
            $cabeceras .= "X-Mailer: PHP/" . phpversion();
            
            // Enviamos el correo (el @ evita que salgan warnings feos en el JSON si falla el servidor SMTP local)
            @mail($emailDestino, $asunto, $mensaje, $cabeceras);
        }
    }
    // --- FIN MÓDULO DE CORREO ELECTRÓNICO ---

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