<?php
session_start();
require_once '../modelos/AccesoBD.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
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
            
            // Guardamos los datos vitales en la sesión, INCLUYENDO EL ROL
            $_SESSION['codigo'] = $codigo;
            $_SESSION['nombreUsuario'] = $u->getNombre();
            $_SESSION['rol'] = $u->getRol(); // ¡Añadimos esta línea clave!
            
            // Redirección basada en el ROL
            if ($_SESSION['rol'] == 2) {
                // Es un repartidor, lo mandamos a su panel estilo App
                header("Location: ../repartidor/repartidor.php");
            } else {
                // Es un cliente normal (rol 0), lo mandamos a la tienda/mi cuenta
                header("Location: ../tienda/" . ltrim($urlDestino, '/'));
            }
            exit;
        } else {
            $_SESSION['mensaje'] = "⚠️ Usuario o contraseña incorrectos.";
            header("Location: ../tienda/login.php");
            exit;
        }
    }
}
?>