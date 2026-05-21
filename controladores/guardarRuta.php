<?php
// controladores/guardarRuta.php

// 1. PRIMERO cargamos las clases SIEMPRE
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

// 2. LUEGO iniciamos la sesión
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['direccionOrigen'] = $_POST['direccionOrigen'];
    $_SESSION['latOrigen'] = $_POST['latOrigen'];
    $_SESSION['lonOrigen'] = $_POST['lonOrigen'];

    $_SESSION['direccionDestino'] = $_POST['direccionDestino'];
    $_SESSION['latDestino'] = $_POST['latDestino'];
    $_SESSION['lonDestino'] = $_POST['lonDestino'];

    // Redirige a la pasarela de pago
    header("Location: checkoutController.php"); 
    exit;
}
?>