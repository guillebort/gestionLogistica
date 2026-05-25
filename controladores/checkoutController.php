<?php
// controladores/checkoutController.php

require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

session_start();

class CheckoutController {
    
    public function prepararPasarela() {
        // 1. Generar token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // 2. Recuperar carrito de la sesión
        $listaCarrito = $_SESSION["carritoJSON"] ?? [];
        $total = $_SESSION["totalPedido"] ?? 0.0;

        //Si el carrito está vacío, no se puede hacer checkout
        if (empty($listaCarrito)) {
            header("Location: ../tienda/carrito.php");
            exit;
        }

        // 3. Obtener métodos de pago del usuario (Lógica de BD)
        $codigoLogueado = $_SESSION["codigo"] ?? 0;
        $misTarjetas = [];
        
        if ($codigoLogueado > 0) {
            $con = AccesoBD::getInstance();
            $misTarjetas = $con->obtenerTarjetasUsuario($codigoLogueado);
        } else {
            // Regla de negocio: Si no está logueado, lo mandamos al login
            header("Location: ../tienda/login.php?origen=carrito&url=../controladores/datosEnvioController.php");
            exit;
        }

        // 4. Inyectar datos en la vista
        require_once '../tienda/procesarPedido.php';
    }
}

$controller = new CheckoutController();
$controller->prepararPasarela();
?>