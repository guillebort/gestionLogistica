<?php
// controladores/productoController.php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';
session_start();
class ProductoController {
    
    public function mostrarCatalogo() {
        try {
            // Instanciamos el modelo
            $con = AccesoBD::getInstance();
            
            // Obtenemos el catálogo
            $listaProductos = $con->obtenerProductosBD();
            
            //inicializamos un array vacío para que la vista no falle
            if (!$listaProductos) {
                $listaProductos = [];
            }

            // renderizamos la vista de la tienda
            require_once '../tienda/productos.php';

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error interno al cargar el catálogo de servicios.";
            header("Location: ../tienda/index.php");
            exit;
        }
    }
}

$controller = new ProductoController();
$controller->mostrarCatalogo();
?>