<?php
// controladores/productoController.php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

class ProductoController {
    
    public function mostrarCatalogo() {
        try {
            // 1. Instanciamos el modelo
            $con = AccesoBD::getInstance();
            
            // 2. Lógica de negocio: Obtener el catálogo
            $listaProductos = $con->obtenerProductosBD();
            
            // Si por algún motivo la BD no devuelve nada, inicializamos un array vacío para que la vista no falle
            if (!$listaProductos) {
                $listaProductos = [];
            }

            // 3. Renderizamos la vista de la tienda
            require_once '../tienda/productos.php';

        } catch (Exception $e) {
            // En un entorno profesional, registraríamos el error en un log (error_log)
            $_SESSION['mensaje'] = "Error interno al cargar el catálogo de servicios.";
            header("Location: ../tienda/index.php");
            exit;
        }
    }
}

// Ejecutamos el Front Controller
$controller = new ProductoController();
$controller->mostrarCatalogo();
?>