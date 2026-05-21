<?php
// controladores/procesarPedido.php

// 1. PRIMERO cargamos las clases SIEMPRE
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

// 2. LUEGO iniciamos la sesión
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalPedido = 0.0;
    unset($_SESSION['carritoJSON']); // Limpiar carrito viejo en sesión
    $carritoJSON = [];
    $con = AccesoBD::getInstance();

    // Leer el JSON que manda el fetch de JS
    $jsonInput = file_get_contents('php://input');
    $productosInput = json_decode($jsonInput, true);

    if (is_array($productosInput)) {
        foreach ($productosInput as $prod) {
            $nuevo = new ProductoCarrito();
            $nuevo->setCodigo((int)$prod['codigo']);
            $nuevo->setDescripcion($prod['descripcion']);
            $nuevo->setPrecio((float)$prod['precio']);
            
            $cantidad = (int)$prod['cantidad'];
            
            // Validación vital de logística: Comprobar stock real en el momento de procesar
            $existencias = $con->obtenerExistencias($nuevo->getCodigo());

            if ($cantidad > $existencias) {
                $cantidad = $existencias; // Ajustamos al máximo disponible
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
        
        if ($codigoUsuario <= 0) {
            $urlDestino = "../tienda/login.php?origen=carrito&url=../controladores/datosEnvioController.php";
        } else {
            $urlDestino = "../controladores/datosEnvioController.php";
        }

        echo json_encode([
            "status" => "success", 
            "message" => "Carrito procesado correctamente",
            "redirect" => $urlDestino
        ]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode([
            "status" => "error", 
            "message" => "El carrito está vacío o los servicios ya no tienen cupos disponibles."
        ]);
    }
}
?>