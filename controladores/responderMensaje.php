<?php
session_start();

require_once __DIR__ . '/../servicios/MailService.php';

if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email_cliente = filter_input(INPUT_POST, 'email_cliente', FILTER_SANITIZE_EMAIL);
    $asunto_original = filter_input(INPUT_POST, 'asunto_original', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $respuesta = filter_input(INPUT_POST, 'respuesta', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


    $mailService = new \servicios\MailService();

    // Llamar a la función
    $enviado = $mailService->enviarRespuestaMensaje($email_cliente, $asunto_original, $respuesta);

    // Redirigir según el resultado
    if ($enviado) {
        header("Location: ../admin/mensajes.php?estado=enviado");
    } else {
        header("Location: ../admin/mensajes.php?estado=error_correo");
    }
    exit;
} else {
    header("Location: ../admin/mensajes.php");
    exit;
}