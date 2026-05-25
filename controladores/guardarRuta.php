<?php
// controladores/guardarRuta.php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['direccionOrigen'] = $_POST['direccionOrigen'];
    $_SESSION['latOrigen'] = $_POST['latOrigen'];
    $_SESSION['lonOrigen'] = $_POST['lonOrigen'];

    $_SESSION['direccionDestino'] = $_POST['direccionDestino'];
    $_SESSION['latDestino'] = $_POST['latDestino'];
    $_SESSION['lonDestino'] = $_POST['lonDestino'];

    // Redirigimos a la pasarela de pago
    header("Location: checkoutController.php"); 
    exit;
}
?>