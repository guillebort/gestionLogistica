<?php

// --- INICIO DE DEPURACIÓN ---
echo "Datos recibidos por el servidor:<br>";
echo "<pre>";
var_dump($_POST);
echo "</pre>";
// Si ves 'carrito' en la lista, el servidor lo recibe. Si no, no llega.
die("--- Fin de la depuración ---"); 
// --- FIN DE DEPURACIÓN ---
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';



// LEEMOS EL CARRITO DE FORMA "A PRUEBA DE BOMBAS"
$carritoRaw = null;

// Intentamos leer el JSON enviado por fetch (lo más probable)
$jsonInput = file_get_contents('php://input');
if (!empty($jsonInput)) {
    $carritoRaw = json_decode($jsonInput, true);
}

// Si no, probamos con $_POST
if (empty($carritoRaw) && isset($_POST['carrito'])) {
    $carritoRaw = json_decode($_POST['carrito'], true);
}

// SI LLEGA AQUÍ VACÍO, ES QUE EL JS NO HA ENVIADO NADA
if (empty($carritoRaw)) {
    http_response_code(400);
    die("ERROR: El carrito ha llegado vacío al servidor.");
}

// Convertimos a objetos ProductoCarrito (lo que pide tu función guardarPedido)
$carritoObjetos = [];
foreach ($carritoRaw as $item) {
    $prod = new ProductoCarrito();
    // Normalizamos nombres para evitar fallos (codigo o id)
    $prod->setCodigo((int)($item['codigo'] ?? $item['id'] ?? 0));
    $prod->setCantidad((int)($item['cantidad'] ?? 1));
    $prod->setPrecio((float)($item['precio'] ?? 0));
    $carritoObjetos[] = $prod;
}

// ... AQUÍ LLAMAS A TU GUARDARPEDIDO ...

    $idPedido = $con->guardarPedido(
        $idUsuario, 
        $_POST['importeTotal'] ?? 0, 
        $carritoObjetos, 
        $_POST['direccionOrigen'], 
        $_POST['latOrigen'], 
        $_POST['lonOrigen'], 
        $_POST['direccionDestino'], 
        $_POST['latDestino'], 
        $_POST['lonDestino']
    );

    if ($idPedido > 0) {
        header("Location: ../tienda/pedidoCompletado.php?id=" . $idPedido);
    } else {
        echo "Error al guardar en BD. Revisa las FK.";
    }

?>