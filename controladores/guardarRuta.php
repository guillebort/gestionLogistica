<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Guardar direcciones de la ruta
    $_SESSION['direccionOrigen'] = $_POST['direccionOrigen'] ?? '';
    $_SESSION['latOrigen'] = $_POST['latOrigen'] ?? '';
    $_SESSION['lonOrigen'] = $_POST['lonOrigen'] ?? '';

    $_SESSION['direccionDestino'] = $_POST['direccionDestino'] ?? '';
    $_SESSION['latDestino'] = $_POST['latDestino'] ?? '';
    $_SESSION['lonDestino'] = $_POST['lonDestino'] ?? '';

    // 2. TRASPASAR EL CARRITO DE JAVASCRIPT A PHP
    if (isset($_POST['carrito_datos'])) {
        $carritoJS = json_decode($_POST['carrito_datos'], true);
        $_SESSION['carrito'] = []; // Limpiamos por si había algo viejo
        
        if (is_array($carritoJS) && !empty($carritoJS)) {
            foreach ($carritoJS as $item) {
                // Mapeamos el ID del producto como clave del array para finalizarPedido
                $id_prod = $item['id'];
                $_SESSION['carrito'][$id_prod] = [
                    'nombre' => $item['nombre'] ?? $item['descripcion'] ?? 'Servicio Logístico',
                    'cantidad' => $item['cantidad'] ?? 1,
                    'precio' => $item['precio'] ?? 0
                ];
            }
        }
    }

    // 3. Redirección nativa y limpia al Checkout
    header('Location: checkoutController.php');
    exit;
}
?>