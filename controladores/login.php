<?php
session_start();
// CORREGIDO: Ruta relativa al modelo
require_once '../modelos/AccesoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $urlDestino = $_POST['url'] ?? 'usuario.php'; 
    $tipoAcceso = $_POST['tipoAcceso'] ?? 'Acceso';

    $con = AccesoBD::getInstance();

    if ($tipoAcceso === "Acceso") {
        $codigo = $con->comprobarUsuarioBD($usuario, $clave);
        if ($codigo > 0) {
            $u = $con->obtenerUsuarioBD($codigo);
            $_SESSION['codigo'] = $codigo;
            $_SESSION['nombreUsuario'] = $u->getNombre();
            
            // CORREGIDO: Volvemos a la carpeta tienda
            header("Location: ../tienda/" . ltrim($urlDestino, '/'));
            exit;
        } else {
            $_SESSION['mensaje'] = "⚠️ Usuario o contraseña incorrectos.";
            
            // CORREGIDO: Volvemos a la carpeta tienda
            header("Location: ../tienda/loginUsuario.php");
            exit;
        }
    }
}
?>