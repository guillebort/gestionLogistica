<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/login.php");
    exit();
}

require_once '../modelos/AccesoBD.php';
$bd = AccesoBD::getInstance();
$mensaje = '';
$tipo_mensaje = 'success';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar_producto') {
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = (float)($_POST['precio'] ?? 0);
        
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
            if ($bd->agregarProductoBD($nombre, $precio, 999, '', $caracteristicas, '')) {
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
    } elseif ($_POST['accion'] === 'actualizar_producto') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = (float)($_POST['precio'] ?? 0);
        
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
        
        if ($id && !empty($nombre) && $precio > 0) {
            if ($bd->modificarProductoBD($id, $nombre, $precio, 999, $caracteristicas, '')) {
                $mensaje = "Servicio actualizado correctamente en el catálogo.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error interno al intentar actualizar el servicio.";
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "Por favor, rellena todos los campos con valores válidos.";
            $tipo_mensaje = "warning";
        }
    }
}

$listaProductos = $bd->obtenerProductosBD();

require_once '../admin/productos.php';
?>