<?php
// controladores/guardarRuta.php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

session_start();

// 1. Declarar que la salida del script será de tipo JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guardar las variables en la sesión como estabas haciendo
    $_SESSION['direccionOrigen'] = $_POST['direccionOrigen'];
    $_SESSION['latOrigen'] = $_POST['latOrigen'];
    $_SESSION['lonOrigen'] = $_POST['lonOrigen'];

    $_SESSION['direccionDestino'] = $_POST['direccionDestino'];
    $_SESSION['latDestino'] = $_POST['latDestino'];
    $_SESSION['lonDestino'] = $_POST['lonDestino'];

    // 2. Devolver una respuesta JSON con la instrucción de redirección
    echo json_encode([
        "status" => "success",
        "data" => [
            "redirect" => "checkoutController.php"
        ]
    ]);
    exit;
}
?>