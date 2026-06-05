<?php
session_start();
require_once '../modelos/AccesoBD.php';

if (!isset($_SESSION['codigo']) || empty($_SESSION['carrito'])) {
    header("Location: ../tienda/index.php");
    exit;
}

$con = AccesoBD::getInstance();
$id_usuario = $_SESSION['codigo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opcion_tarjeta = $_POST['opcion_tarjeta'] ?? 'nueva';

    // 1. VALIDACIÓN DE FECHA SI ES TARJETA NUEVA
    if ($opcion_tarjeta === 'nueva') {
        $titular = trim($_POST['titular'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $caducidad = trim($_POST['caducidad'] ?? '');
        $cvc = trim($_POST['cvc'] ?? '');

        if (empty($titular) || empty($numero) || empty($caducidad) || empty($cvc)) {
            $_SESSION['error_pago'] = "Rellena todos los datos de la tarjeta.";
            header("Location: checkoutController.php");
            exit;
        }

        // VALIDACIÓN ESTRICTA: Fecha mayor a la actual
        $partesFecha = explode('/', $caducidad);
        if (count($partesFecha) == 2) {
            $mesCad = (int)$partesFecha[0];
            $anoCad = (int)$partesFecha[1] + 2000;

            $fechaCaducidad = new DateTime();
            $fechaCaducidad->setDate($anoCad, $mesCad, 1);
            $fechaCaducidad->modify('last day of this month');
            $fechaCaducidad->setTime(23, 59, 59);

            $fechaActual = new DateTime();

            if ($fechaCaducidad < $fechaActual) {
                $_SESSION['error_pago'] = "❌ Error: La tarjeta introducida está caducada.";
                header("Location: checkoutController.php");
                exit;
            }
        } else {
            $_SESSION['error_pago'] = "Formato de fecha inválido (usa MM/AA).";
            header("Location: checkoutController.php");
            exit;
        }

        // GUARDAR TARJETA EN BD (Si lo ha marcado)
        if (isset($_POST['guardar_tarjeta']) && $_POST['guardar_tarjeta'] == '1') {
            $con->guardarTarjetaBD($id_usuario, $numero, $titular, $caducidad);
        }
    }

    // 2. CREACIÓN DEL PEDIDO
    $dirOrigen = $_SESSION['direccionOrigen'] ?? 'Sede Central';
    $dirDestino = $_SESSION['direccionDestino'] ?? 'Destino Cliente';
    
    $importeTotal = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $importeTotal += ($item['precio'] * $item['cantidad']);
    }

    $id_pedido_generado = $con->insertarPedido($id_usuario, $importeTotal, $dirOrigen, $dirDestino);

    if ($id_pedido_generado) {
        foreach ($_SESSION['carrito'] as $id_prod => $item) {
            $con->insertarDetallePedido($id_pedido_generado, $id_prod, $item['cantidad'], $item['precio']);
        }

        // 3. VACIAR EL CARRITO (Obligatorio tras pagar)
        unset($_SESSION['carrito']);
        unset($_SESSION['direccionOrigen']);
        unset($_SESSION['direccionDestino']);

        // 4. REDIRIGIR A TU VISTA REAL: pedidoCompletado.php
        header("Location: ../tienda/pedidoCompletado.php?id=" . $id_pedido_generado);
        exit;
    } else {
        $_SESSION['error_pago'] = "Error interno al generar el pedido.";
        header("Location: checkoutController.php");
        exit;
    }
}
?>