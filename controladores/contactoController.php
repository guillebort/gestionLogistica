<?php
session_start();

require_once '../modelos/AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';

    $con = AccesoBD::getInstance();
    $exito = $con->guardarMensajeContacto($nombre, $email, $asunto, $mensaje);

    if ($exito) {
        $_SESSION['mensajeContacto'] = "✅ ¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.";
    } else {
        $_SESSION['mensajeContacto'] = "❌ Hubo un error al enviar tu mensaje. Por favor, inténtalo de nuevo.";
    }
    
    header("Location: ../tienda/contacto.php"); 
    exit;
}
?>