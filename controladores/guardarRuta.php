<?php
// --- Archivo: guardarRuta.php ---
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['direccionOrigen'] = $_POST['direccionOrigen'];
    $_SESSION['latOrigen'] = $_POST['latOrigen'];
    $_SESSION['lonOrigen'] = $_POST['lonOrigen'];

    $_SESSION['direccionDestino'] = $_POST['direccionDestino'];
    $_SESSION['latDestino'] = $_POST['latDestino'];
    $_SESSION['lonDestino'] = $_POST['lonDestino'];

    header("Location: finalizarPedido.php"); // Tu vista de cobro
    exit;
}
?>