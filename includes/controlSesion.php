<?php
// includes/controlSesion.php

// 1. Iniciamos sesión solo si no está iniciada ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Control de Inactividad
$tiempo_limite = 900; // 15 minutos en segundos

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
    
    // Si ha pasado el límite, limpiamos todo y a la calle
    if ($tiempo_transcurrido > $tiempo_limite) {
        session_unset();
        session_destroy();
        
        // Lo mandamos al nuevo login unificado (que haremos en el paso 2)
        header("Location: ../tienda/login.php?timeout=1");
        exit;
    }
}

// 3. Refrescamos la marca de tiempo porque el usuario acaba de hacer algo
$_SESSION['ultimo_acceso'] = time();
?>