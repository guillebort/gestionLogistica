<?php
// --- Archivo: controladores/asignarRepartidor.php ---
session_start();
require_once '../modelos/AccesoBD.php';

// Bloqueo de seguridad extra: si un cliente averigua esta URL y hace POST, le denegamos la acción
$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    die("Acceso denegado. No tienes permisos de administrador.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = $_POST['idPedido'] ?? 0;
    $idRepartidor = $_POST['idRepartidor'] ?? 0;
    
    // Estado 2 = "Enviado" / "Asignado a Ruta"
    $nuevoEstado = 2; 

    if ($idPedido > 0 && $idRepartidor > 0) {
        $exito = $con->asignarRepartidor($idPedido, $idRepartidor, $nuevoEstado);
        
        if ($exito) {
            $_SESSION['mensajeAdmin'] = "✅ Pedido #$idPedido asignado correctamente a ruta.";
        } else {
            $_SESSION['mensajeAdmin'] = "❌ Hubo un error al asignar el pedido #$idPedido.";
        }
    }
}

// Devolvemos al admin a su panel
header("Location: ../admin/index.php");
exit;
?>