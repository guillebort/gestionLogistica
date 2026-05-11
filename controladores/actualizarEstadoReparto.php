<?php
// controladores/actualizarEstadoReparto.php
session_start();
require_once '../modelos/AccesoBD.php';

// Verificamos que el usuario sea Repartidor (Rol 2)
$idRepartidor = $_SESSION['codigo'] ?? 0;
$rol = $_SESSION['rol'] ?? 0;

if ($idRepartidor <= 0 || $rol != 2) {
    echo "ERROR_AUTH";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Saneamos y validamos los datos recibidos
    $idPedido = filter_input(INPUT_POST, 'idPedido', FILTER_VALIDATE_INT);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($idPedido && $estado) {
        $con = AccesoBD::getInstance();
        // Llamamos a la función que ya tenías creada en AccesoBD.php
        $exito = $con->actualizarEstadoReparto($idPedido, $idRepartidor, $estado);

        if ($exito) {
            echo "OK";
        } else {
            echo "ERROR_DB";
        }
    } else {
        echo "ERROR_PARAMS";
    }
} else {
    echo "ERROR_METHOD";
}
?>