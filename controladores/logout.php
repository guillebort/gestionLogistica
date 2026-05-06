<?php
// --- Archivo: logout.php ---
session_start();
session_unset();
session_destroy();

// El equivalente en PHP a redirigir al "Puente Destructor" (limpiarCarrito) que tenías
header("Location: limpiarCarrito.php");
exit;
?>