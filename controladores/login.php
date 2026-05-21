<?php
// controladores/login.php
session_start();
require_once '../modelos/AccesoBD.php';

// Generamos el token CSRF si no existe
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
            
            // Guardamos los datos vitales en la sesión
            $_SESSION['codigo'] = $codigo;
            $_SESSION['nombreUsuario'] = $u->getNombre();
            $_SESSION['rol'] = $u->getRol(); 
            
            // Redirección directa basada en el ROL del usuario
            if ($_SESSION['rol'] == 1) {
                // Es Administrador
                $_SESSION['nombreAdmin'] = $u->getNombre();
                header("Location: ../admin/index.php");
            } elseif ($_SESSION['rol'] == 2) {
                // Es Repartidor
                header("Location: ../repartidor/repartidor.php");
            } else {
                // Es Cliente normal (Rol 0)
                header("Location: ../tienda/" . ltrim($urlDestino, '/'));
            }
            exit;
            
        } else {
            // Fallo de autenticación
            $_SESSION['mensaje'] = "⚠️ Usuario o contraseña incorrectos.";
            header("Location: ../tienda/login.php");
            exit;
        }
    }
}
?>