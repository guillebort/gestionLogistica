<?php
// admin/productos.php
session_start();

// Control de seguridad por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../modelos/AccesoBD.php';
$bd = new AccesoBD();
$mensaje = '';
$tipo_mensaje = 'success';

// 1. LÓGICA DE BORRADO (GET)
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    if ($bd->eliminarProducto($id_eliminar)) {
        $mensaje = "Servicio eliminado correctamente del catálogo.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "No se pudo eliminar el servicio. Comprueba si está asociado a pedidos activos.";
        $tipo_mensaje = "danger";
    }
}

// 2. LÓGICA DE GUARDADO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar_producto') {
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = (float)($_POST['precio'] ?? 0);
        
        // Conversión del textarea a formato <li> para la base de datos
        $caracteristicas_brutas = $_POST['caracteristicas'] ?? '';
        $caracteristicas = '';
        if (!empty($caracteristicas_brutas)) {
            $lineas = explode("\n", str_replace("\r", "", $caracteristicas_brutas));
            foreach ($lineas as $linea) {
                if (trim($linea) !== '') {
                    $caracteristicas .= "<li>" . htmlspecialchars(trim($linea)) . "</li>";
                }
            }
        }

        if (!empty($nombre) && $precio > 0) {
            if ($bd->guardarProducto($nombre, $precio, $caracteristicas)) {
                $mensaje = "Nuevo servicio logístico añadido con éxito.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error crítico al guardar en la base de datos.";
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "Por favor, rellena todos los campos con valores válidos.";
            $tipo_mensaje = "warning";
        }
    }
}

// 3. CARGA DE DATOS
$listaProductos = $bd->obtenerProductosBD();

// 4. INYECTAR LA VISTA
require_once '..admin/productos.php';
?>