<?php
// --- Archivo: procesarPedido.php ---
session_start();
require_once '../modelos/AccesoBD.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalPedido = 0.0;
    unset($_SESSION['carritoJSON']); // Limpiar viejo
    $carritoJSON = [];
    $con = AccesoBD::getInstance();

    // Leer el JSON que manda el JS
    $jsonInput = file_get_contents('php://input');
    $productosInput = json_decode($jsonInput, true);

    if (is_array($productosInput)) {
        foreach ($productosInput as $prod) {
            $nuevo = new ProductoCarrito();
            $nuevo->setCodigo((int)$prod['codigo']);
            $nuevo->setDescripcion($prod['descripcion']);
            $nuevo->setPrecio((float)$prod['precio']);
            
            $cantidad = (int)$prod['cantidad'];
            $existencias = $con->obtenerExistencias($nuevo->getCodigo());

            if ($cantidad > $existencias) {
                $cantidad = $existencias;
            }

            if ($cantidad > 0) {
                $nuevo->setCantidad($cantidad);
                $carritoJSON[] = $nuevo;
                $totalPedido += ($nuevo->getPrecio() * $cantidad);
            }
        }
    }

    header('Content-Type: application/json; charset=utf-8');

    if (count($carritoJSON) > 0) {
        $_SESSION['carritoJSON'] = $carritoJSON;
        $_SESSION['totalPedido'] = $totalPedido;

        $codigoUsuario = $_SESSION['codigo'] ?? 0;
        $urlDestino = ($codigoUsuario <= 0) ? "login.php?url=datosEnvio.php" : "datosEnvio.php";

        echo json_encode(["status" => "ok", "redirect" => $urlDestino]);
    } else {
        echo json_encode(["status" => "error", "message" => "Carrito vacío o sin stock."]);
    }
}
?>