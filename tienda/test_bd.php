<?php
// Mostramos todos los errores por pantalla para ver qué falla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../modelos/AccesoBD.php';

echo "<h3>Probando conexión a la base de datos...</h3>";

// Al instanciar la clase, el constructor ejecuta automáticamente abrirConexionBD()
$con = AccesoBD::getInstance();

echo "<p style='color:green;'><b>¡Conexión a la base de datos PERFECTA! 🚀</b></p>";
?>