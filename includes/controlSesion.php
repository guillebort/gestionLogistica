<?php
// includes/controlSesion.php

// Iniciamos sesión solo si no está iniciada ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de Inactividad
$tiempo_limite = 900; // 5 minutos en segundos

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
    
    // Si ha pasado el límite, limpiamos todo y a la calle
    if ($tiempo_transcurrido > $tiempo_limite) {
        session_unset();
        session_destroy();
        
        // Lo mandamos al login 
        header("Location: ../tienda/login.php?timeout=1");
        exit;
    }
}

// Refrescamos la marca de tiempo porque el usuario acaba de hacer algo
$_SESSION['ultimo_acceso'] = time();
?>