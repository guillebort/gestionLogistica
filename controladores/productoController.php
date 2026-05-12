<?php
// controladores/ProductoController.php
session_start();
require_once '../modelos/AccesoBD.php';

class ProductoController {
    
    public function mostrarCatalogo() {
        // 1. El Controlador llama al Modelo
        $bd = AccesoBD::getInstance();
        $listaProductos = $bd->obtenerProductosBD();
        
        // 2. El Controlador carga los datos de la sesión necesarios
        $nombreUsuario = $_SESSION['nombreUsuario'] ?? '';

        // 3. El Controlador "inyecta" los datos en la Vista
        require_once '../tienda/productos.php';
    }
}

// Enrutador básico (Front Controller pattern)
$accion = $_GET['accion'] ?? 'catalogo';
$controller = new ProductoController();

if ($accion === 'catalogo') {
    $controller->mostrarCatalogo();
}