<?php
// controladores/procesarPedido.php
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalPedido = 0.0;
    unset($_SESSION['carritoJSON']); // Limpiamos por si había algo antiguo
    $carritoJSON = [];
    
    $con = AccesoBD::getInstance();

    // Leer el JSON que manda el fetch de JS
    $jsonInput = file_get_contents('php://input');
    $productosInput = json_decode($jsonInput, true);

    if (is_array($productosInput)) {
        foreach ($productosInput as $prod) {
            $nuevo = new ProductoCarrito();
            
            // Soportamos 'codigo' o 'id' para evitar fallos del frontend
            $nuevo->setCodigo((int)($prod['codigo'] ?? $prod['id'] ?? 0));
            $nuevo->setDescripcion($prod['descripcion'] ?? $prod['nombre'] ?? 'Servicio Logístico');
            $nuevo->setPrecio((float)($prod['precio'] ?? 0));
            
            $cantidad = (int)($prod['cantidad'] ?? 1);
            
            // Comprobamos el stock real en la base de datos
            $existencias = $con->obtenerExistencias($nuevo->getCodigo());

            // Si el cliente pide más de lo que hay, lo ajustamos al máximo
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
        // Guardamos el array de OBJETOS de la clase ProductoCarrito y el total
        $_SESSION['carritoJSON'] = $carritoJSON;
        $_SESSION['totalPedido'] = $totalPedido;

        $codigoUsuario = $_SESSION['codigo'] ?? 0;
        
        // Si no está logueado, lo mandamos al login. Si lo está, a datos de envío.
        $urlDestino = ($codigoUsuario <= 0) 
            ? "../tienda/login.php?origen=carrito&url=../controladores/datosEnvioController.php" 
            : "../controladores/datosEnvioController.php";

        echo json_encode([
            "status" => "success", 
            "message" => "Carrito validado correctamente",
            "redirect" => $urlDestino
        ]);
    } else {
        http_response_code(400); 
        echo json_encode([
            "status" => "error", 
            "message" => "El carrito está vacío o los servicios seleccionados ya no están disponibles."
        ]);
    }
}
?>