<?php
// controladores/RepartidorController.php
session_start();
require_once '../modelos/AccesoBD.php';

class RepartidorController {
    
    public function mostrarPanel() {
        // 1. Validación de seguridad y rol
        $rolUsuario = $_SESSION['rol'] ?? 0; 
        $idRepartidor = $_SESSION['codigo'] ?? 0;

        // Si no está logueado o no es repartidor (rol 2), lo echamos
        if ($idRepartidor <= 0 || $rolUsuario != 2) {
            header("Location: ../tienda/login.php");
            exit;
        }

        // 2. Conexión al Modelo para obtener los datos
        $con = AccesoBD::getInstance();
        $nombreRepartidor = $_SESSION['nombreUsuario'] ?? 'Repartidor';
        
        // Obtenemos las rutas/pedidos asignados a este repartidor en concreto
        $paradas = $con->obtenerRutasRepartidor($idRepartidor);

        // 3. Inyectamos los datos en la Vista "tonta"
        require_once '../repartidor/repartidor.php';
    }
}

// Enrutador frontal básico
$controller = new RepartidorController();
$controller->mostrarPanel();

?>