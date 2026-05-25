<?php
// controladores/RepartidorController.php
require_once '../modelos/AccesoBD.php';
session_start();

class RepartidorController {
    
    public function mostrarPanel() {
        // Validación de seguridad y rol
        $rolUsuario = $_SESSION['rol'] ?? 0; 
        $idRepartidor = $_SESSION['codigo'] ?? 0;

        // Si no está logueado o no es repartidor (rol 2), lo echamos
        if ($idRepartidor <= 0 || $rolUsuario != 2) {
            header("Location: ../tienda/login.php");
            exit;
        }

        // obtenemos los datos
        $con = AccesoBD::getInstance();
        $nombreRepartidor = $_SESSION['nombreUsuario'] ?? 'Repartidor';
        
        // Obtenemos las rutas/pedidos asignados a este repartidor en concreto
        $paradas = $con->obtenerRutasRepartidor($idRepartidor);

        // inyectamos los datos en la vista
        require_once '../repartidor/repartidor.php';
    }
}

$controller = new RepartidorController();
$controller->mostrarPanel();

?>